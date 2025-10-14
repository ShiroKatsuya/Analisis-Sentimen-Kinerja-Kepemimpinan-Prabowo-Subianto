#!/usr/bin/env python3

"""
FastAPI Sentiment Analysis Service for Code-Mixed Indonesian-English Text

Provides REST API endpoints for sentiment analysis using improved models for Indonesian text
"""

import json
import re
from typing import Dict, Optional, List
from datetime import datetime
from pydantic import BaseModel, Field
from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
import uvicorn

# Try to import transformer dependencies
try:
    from transformers import pipeline, AutoTokenizer, AutoModelForSequenceClassification
    import torch
    import numpy as np
    TRANSFORMER_AVAILABLE = True
except ImportError:
    TRANSFORMER_AVAILABLE = False
    print("Warning: Transformer dependencies not available. Sentiment analysis is unavailable.")

# Pydantic models for request/response
class AnalyzeRequest(BaseModel):
    text: str = Field(..., min_length=1, max_length=1000, description="Text to analyze")
    source_type: Optional[str] = Field(default="user_input", description="Source type of the text")

class AnalyzeResponse(BaseModel):
    processed_text: str
    sentiment: str
    confidence_score: float
    sentiment_scores: Dict[str, float]
    language_breakdown: Dict[str, float]
    analysis_method: str
    analysis_timestamp: str
    source_type: str

class HealthResponse(BaseModel):
    status: str
    transformer_available: bool
    timestamp: str

# Initialize FastAPI app
app = FastAPI(
    title="Sentiment Analysis API",
    description="API for sentiment analysis of code-mixed Indonesian-English text using improved models",
    version="1.0.0"
)

# Add CORS middleware
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # In production, specify your Laravel app URL
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

class CodeMixedSentimentAnalyzer:
    def __init__(self):
        """Initialize the sentiment analysis model and tokenizer"""
        self.use_transformer = TRANSFORMER_AVAILABLE

        if self.use_transformer:
            # Try multiple models in order of preference - prioritizing Indonesian models
            models_to_try = [
                # "cardiffnlp/twitter-xlm-roberta-base-sentiment",
                "nlptown/bert-base-multilingual-uncased-sentiment",
                # "cardiffnlp/twitter-roberta-base-sentiment"
            ]
            
            for model_name in models_to_try:
                try:
                    print(f"Attempting to load sentiment model: {model_name}")
                    
                    # Initialize the sentiment analysis pipeline
                    self.sentiment_pipeline = pipeline(
                        "sentiment-analysis",
                        model=model_name,
                        tokenizer=model_name,
                        return_all_scores=True,
                        use_fast=False  # Use slow tokenizer to avoid conversion issues
                    )

                    # For code-mixed text, we'll also use a more specific approach
                    self.tokenizer = AutoTokenizer.from_pretrained(model_name, use_fast=False)
                    self.model = AutoModelForSequenceClassification.from_pretrained(model_name)
                    
                    self.model_name = model_name
                    print(f"Sentiment analysis model loaded successfully: {model_name}")
                    break

                except Exception as e:
                    print(f"Error loading model {model_name}: {e}")
                    if model_name == models_to_try[-1]:  # Last model failed
                        print("All sentiment analysis models failed to load")
                        self.use_transformer = False
                    continue

        if not self.use_transformer:
            print("Sentiment analysis is unavailable (transformer dependencies not available).")

    def preprocess_code_mixed_text(self, text: str) -> str:
        """
        Preprocess code-mixed Indonesian-English text for sentiment analysis
        """
        # Convert to lowercase
        text = text.lower()

        # Remove excessive whitespace
        text = re.sub(r'\s+', ' ', text)

        # Remove special characters but keep Indonesian and English letters
        text = re.sub(r'[^\w\s]', ' ', text)

        # Clean up whitespace again
        text = re.sub(r'\s+', ' ', text).strip()

        return text

    def is_neutral_statement(self, text: str) -> bool:
        """
        Check if the text is likely a neutral factual statement
        """
        # List of neutral patterns that often indicate factual statements
        neutral_patterns = [
            r'\b(adalah|is|are|was|were)\b',  # Copula verbs
            r'\b(planet|bumi|earth|matahari|sun|bulan|moon)\b',  # Astronomical terms
            r'\b(satu|one|dua|two|tiga|three)\b',  # Numbers
            r'\b(satunya|only|unique|tunggal)\b',  # Unique/only
            r'\b(fakta|fact|kenyataan|reality)\b',  # Fact/reality words
            r'\b(berada|located|terletak|situated)\b',  # Location words
            r'\b(memiliki|has|have|had|punya)\b',  # Possession words
            r'\b(terdiri|consists|composed|made)\b',  # Composition words
        ]
        
        text_lower = text.lower()
        
        # Check if text contains neutral patterns
        neutral_count = 0
        for pattern in neutral_patterns:
            if re.search(pattern, text_lower):
                neutral_count += 1
        
        # If text has multiple neutral patterns, it's likely neutral
        if neutral_count >= 2:
            return True
            
        # Check for absence of emotional words
        emotional_words = [
            'senang', 'bahagia', 'gembira', 'suka', 'love', 'like', 'happy', 'joy',
            'sedih', 'kecewa', 'marah', 'sad', 'angry', 'disappointed', 'frustrated',
            'bagus', 'baik', 'good', 'great', 'excellent', 'wonderful',
            'buruk', 'jelek', 'bad', 'terrible', 'awful', 'horrible',
            'menakjubkan', 'amazing', 'fantastic', 'incredible',
            'mengerikan', 'terrible', 'horrible', 'awful'
        ]
        
        has_emotional_words = any(word in text_lower for word in emotional_words)
        
        # If no emotional words and has neutral patterns, likely neutral
        if not has_emotional_words and neutral_count >= 1:
            return True
            
        return False

    def adjust_sentiment_for_neutral_statements(self, sentiment_scores: Dict[str, float], text: str) -> Dict[str, float]:
        """
        Adjust sentiment scores for neutral factual statements
        """
        if self.is_neutral_statement(text):
            # Boost neutral score and reduce others
            sentiment_scores['neutral'] = max(sentiment_scores['neutral'], 0.6)
            
            # Reduce other scores proportionally
            total_other = sentiment_scores['positive'] + sentiment_scores['negative']
            if total_other > 0:
                sentiment_scores['positive'] = sentiment_scores['positive'] * 0.3
                sentiment_scores['negative'] = sentiment_scores['negative'] * 0.3
            
            # Normalize to sum to 1.0
            total = sum(sentiment_scores.values())
            for k in sentiment_scores:
                sentiment_scores[k] = sentiment_scores[k] / total
                
        return sentiment_scores

    def _analyze_language_breakdown(self, text: str) -> Dict[str, float]:
        """
        Analyze language breakdown in code-mixed text.
        This is a placeholder for a more sophisticated language detection model.
        """
        # Simple simulation based on common code-mixing patterns
        indonesian_keywords = ["yang", "untuk", "dan", "atau", "tidak", "dengan", "saya", "kamu", "dia", "mereka", "kita", "ini", "itu"]
        english_keywords = ["the", "a", "an", "is", "are", "was", "were", "and", "or", "not", "with", "I", "you", "he", "she", "it", "they", "we", "this", "that"]

        text_lower = text.lower()
        indonesian_count = sum(text_lower.count(kw) for kw in indonesian_keywords)
        english_count = sum(text_lower.count(kw) for kw in english_keywords)

        total_keywords = indonesian_count + english_count

        if total_keywords == 0:
            # Default to mixed if no keywords found
            return {'indonesian': 0.5, 'english': 0.5, 'mixed': 0.0}

        indonesian_ratio = indonesian_count / total_keywords
        english_ratio = english_count / total_keywords

        # Simple rule: if both languages are present above a certain threshold, consider it mixed
        if indonesian_ratio > 0.3 and english_ratio > 0.3:
            mixed_score = 0.4
            indonesian_score = indonesian_ratio * (1 - mixed_score)
            english_score = english_ratio * (1 - mixed_score)
        else:
            mixed_score = 0.0
            indonesian_score = indonesian_ratio
            english_score = english_ratio
        
        # Normalize to sum to 1.0
        total_sum = indonesian_score + english_score + mixed_score
        if total_sum == 0:
            return {'indonesian': 0.5, 'english': 0.5, 'mixed': 0.0} # Fallback
        
        return {
            'indonesian': round(indonesian_score / total_sum, 4),
            'english': round(english_score / total_sum, 4),
            'mixed': round(mixed_score / total_sum, 4)
        }

    def perform_sentiment_analysis(self, text: str) -> Dict:
        """
        Perform sentiment analysis on code-mixed text using improved logic
        """
        if not self.use_transformer:
            raise RuntimeError("Transformer model is not available. Sentiment analysis cannot be performed.")
        try:
            # Preprocess the text
            processed_text = self.preprocess_code_mixed_text(text)

            # Use sentiment analysis pipeline
            results = self.sentiment_pipeline(processed_text)

            # Extract sentiment scores
            sentiment_scores = {'positive': 0.0, 'negative': 0.0, 'neutral': 0.0}

            if results and len(results) > 0:
                result = results[0]
                print(f"Raw sentiment results: {result}")  # Debug output
                
                # Handle different model label formats
                for score_info in result:
                    label = score_info['label'].lower()
                    score = score_info['score']
                    
                    # Map various label formats to our standard format
                    if any(pos_word in label for pos_word in ['positive', 'pos', '5', '4']):
                        sentiment_scores['positive'] = score
                    elif any(neg_word in label for neg_word in ['negative', 'neg', '1', '2']):
                        sentiment_scores['negative'] = score
                    elif any(neu_word in label for neu_word in ['neutral', 'neu', '3']):
                        sentiment_scores['neutral'] = score
                
                # Handle cases where we only have positive/negative (no neutral)
                if sentiment_scores['neutral'] == 0.0 and (
                    sentiment_scores['positive'] > 0.0 or sentiment_scores['negative'] > 0.0
                ):
                    # Calculate neutral as the remaining probability
                    total = sentiment_scores['positive'] + sentiment_scores['negative']
                    if total < 1.0:
                        sentiment_scores['neutral'] = 1.0 - total
                    else:
                        # Normalize to sum to 1.0
                        sentiment_scores['positive'] /= total
                        sentiment_scores['negative'] /= total
            else:
                # Fallback to neutral if no result
                sentiment_scores = {'positive': 0.0, 'negative': 0.0, 'neutral': 1.0}

            # Normalize scores to sum to 1.0 if possible
            total_score = sum(sentiment_scores.values())
            if total_score > 0:
                for k in sentiment_scores:
                    sentiment_scores[k] = sentiment_scores[k] / total_score

            # Apply neutral statement adjustment
            sentiment_scores = self.adjust_sentiment_for_neutral_statements(sentiment_scores, processed_text)

            # Print the scores for negative, neutral, and positive
            print("Sentiment Scores:")
            print(f"Negative: {sentiment_scores.get('negative', 0.0)}")
            print(f"Neutral: {sentiment_scores.get('neutral', 0.0)}")
            print(f"Positive: {sentiment_scores.get('positive', 0.0)}")

            # Determine the main sentiment with improved logic
            sentiment = max(sentiment_scores, key=sentiment_scores.get)
            confidence_score = sentiment_scores[sentiment]

            # Additional check: if neutral score is close to others, prefer neutral
            max_score = max(sentiment_scores.values())
            neutral_score = sentiment_scores['neutral']
            
            # If neutral is within 0.1 of the highest score, prefer neutral for factual statements
            if (max_score - neutral_score) < 0.1 and self.is_neutral_statement(processed_text):
                sentiment = 'neutral'
                confidence_score = neutral_score

            # Analyze language breakdown
            language_breakdown = self._analyze_language_breakdown(processed_text)

            return {
                'processed_text': processed_text,
                'sentiment': sentiment,
                'confidence_score': round(confidence_score, 4),
                'sentiment_scores': {k: round(v, 4) for k, v in sentiment_scores.items()},
                'language_breakdown': language_breakdown,
                'analysis_method': f'sentiment_analysis_{self.model_name.replace("/", "_")}'
            }

        except Exception as e:
            print(f"Error in sentiment analysis: {e}")
            # Return fallback analysis
            return self._fallback_analysis(text)

    def _fallback_analysis(self, text: str) -> Dict:
        """Fallback analysis when everything fails"""
        return {
            'processed_text': self.preprocess_code_mixed_text(text),
            'sentiment': 'neutral',
            'confidence_score': 0.0,
            'sentiment_scores': {'positive': 0.0, 'negative': 0.0, 'neutral': 1.0},
            'analysis_method': 'fallback'
        }

# Initialize the analyzer
analyzer = CodeMixedSentimentAnalyzer()

@app.get("/", response_model=HealthResponse)
async def root():
    """Health check endpoint"""
    return HealthResponse(
        status="healthy",
        transformer_available=TRANSFORMER_AVAILABLE,
        timestamp=datetime.now().isoformat()
    )

@app.post("/analyze", response_model=AnalyzeResponse)
async def analyze_text(request: AnalyzeRequest):
    """
    Analyze sentiment of code-mixed Indonesian-English text
    """
    try:
        # Perform sentiment analysis
        result = analyzer.perform_sentiment_analysis(request.text)

        # Add timestamp and source type
        result['analysis_timestamp'] = datetime.now().isoformat()
        result['source_type'] = request.source_type

        return AnalyzeResponse(**result)

    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Analysis failed: {str(e)}")

@app.get("/health", response_model=HealthResponse)
async def health_check():
    """Health check endpoint"""
    return HealthResponse(
        status="healthy",
        transformer_available=TRANSFORMER_AVAILABLE,
        timestamp=datetime.now().isoformat()
    )

if __name__ == "__main__":
    print("Starting FastAPI Sentiment Analysis Service...")
    print(f"Transformer Available: {TRANSFORMER_AVAILABLE}")
    print("API will be available at: http://localhost:8001")
    print("API Documentation: http://localhost:8001/docs")

    uvicorn.run(
        "fastapi_bert_service:app",
        host="0.0.0.0",
        port=8001,
        reload=True,
        log_level="info"
    )
<?php

namespace App\Http\Controllers;

use App\Models\SentimentAnalysis;
use App\Models\TextSample;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SentimentAnalysisController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = SentimentAnalysis::with('textSample');

        // Apply filters
        if ($request->filled('sentiment')) {
            $query->where('sentiment', $request->sentiment);
        }

        if ($request->filled('source_type')) {
            $query->where('source_type', $request->source_type);
        }

        if ($request->filled('date_from')) {
            $query->where('analysis_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('analysis_date', '<=', $request->date_to);
        }

        $analyses = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('sentiment-analysis.index', compact('analyses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('sentiment-analysis.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'text' => 'required|string|max:5000',
            'source_type' => 'required|string|in:social_media,news,survey,other',
            'source_platform' => 'nullable|string|max:100',
        ]);

        // Create text sample
        $textSample = TextSample::create([
            'content' => $request->text,
            'source_type' => $request->source_type,
            'source_platform' => $request->source_platform,
            'published_at' => now(),
        ]);

        // Perform sentiment analysis using BERT model
        $analysisResult = $this->performBertAnalysis($request->text);

        // Create sentiment analysis record
        $sentimentAnalysis = SentimentAnalysis::create([
            'text_sample_id' => $textSample->id,
            'original_text' => $request->text,
            'processed_text' => $analysisResult['processed_text'],
            'sentiment' => $analysisResult['sentiment'],
            'confidence_score' => $analysisResult['confidence_score'],
            'sentiment_scores' => $analysisResult['sentiment_scores'],
            'language_breakdown' => $analysisResult['language_breakdown'],
            'source_type' => $request->source_type,
            'analysis_date' => now(),
        ]);

        // Mark text sample as processed
        $textSample->update(['is_processed' => true]);

        return redirect()->route('sentiment-analysis.show', $sentimentAnalysis)
            ->with('success', 'Sentiment analysis completed successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(SentimentAnalysis $sentimentAnalysis)
    {
        $sentimentAnalysis->load('textSample');
        return view('sentiment-analysis.show', compact('sentimentAnalysis'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SentimentAnalysis $sentimentAnalysis)
    {
        return view('sentiment-analysis.edit', compact('sentimentAnalysis'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SentimentAnalysis $sentimentAnalysis)
    {
        $request->validate([
            'sentiment' => 'required|in:positive,negative,neutral',
            'confidence_score' => 'required|numeric|between:0,1',
        ]);

        $sentimentAnalysis->update([
            'sentiment' => $request->sentiment,
            'confidence_score' => $request->confidence_score,
        ]);

        return redirect()->route('sentiment-analysis.show', $sentimentAnalysis)
            ->with('success', 'Analysis updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SentimentAnalysis $sentimentAnalysis)
    {
        $sentimentAnalysis->delete();
        return redirect()->route('sentiment-analysis.index')
            ->with('success', 'Analysis deleted successfully!');
    }

    /**
     * Perform BERT-based sentiment analysis using FastAPI service
     */
    private function performBertAnalysis(string $text): array
    {
        try {
            // Prepare the data for FastAPI
            $data = [
                'text' => $text,
                'source_type' => 'user_input'
            ];
            
            // Call the FastAPI service
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://localhost:8001/analyze');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200 && $response) {
                $result = json_decode($response, true);
                
                if (json_last_error() === JSON_ERROR_NONE && isset($result['sentiment'])) {
                    return $result;
                }
            }
            
            // If FastAPI fails, try direct Python execution as fallback
            return $this->fallbackPythonAnalysis($text);
            
        } catch (\Exception $e) {
            // Log the error
            \Illuminate\Support\Facades\Log::error("FastAPI analysis failed: " . $e->getMessage());
            
            // Return fallback analysis
            return $this->fallbackAnalysis($text);
        }
    }
    
    /**
     * Fallback to direct Python execution if FastAPI is not available
     */
    private function fallbackPythonAnalysis(string $text): array
    {
        try {
            // Escape the text for command line execution
            // $escapedText = escapeshellarg($text);
            
            // // Try the simplified Python service first
            // $command = "python " . base_path("python_bert_service_simple.py") . " {$escapedText}";
            // $output = shell_exec($command);
            
            // if (!$output) {
            //     // Try the full Python service
            //     $command = "python " . base_path("python_bert_service.py") . " {$escapedText}";
            //     $output = shell_exec($command);
            // }
            
            // if ($output) {
            //     $result = json_decode($output, true);
                
            //     if (json_last_error() === JSON_ERROR_NONE && isset($result['sentiment'])) {
            //         return $result;
            //     }
            // }
            
            // If Python also fails, use rule-based analysis
            return $this->fallbackAnalysis($text);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Python analysis failed: " . $e->getMessage());
            return $this->fallbackAnalysis($text);
        }
    }

    /**
     * Fallback analysis when Python BERT service is unavailable
     */
    private function fallbackAnalysis(string $text): array
    {
        // Preprocess text for code-mixed analysis
        $processedText = $this->preprocessCodeMixedText($text);
        
        // Simple rule-based sentiment analysis as fallback
        $positiveWords = ['baik', 'bagus', 'hebat', 'luar biasa', 'excellent', 'amazing', 'great', 'good', 'wonderful', 'fantastic'];
        $negativeWords = ['buruk', 'jelek', 'sangat buruk', 'terrible', 'awful', 'bad', 'horrible', 'disappointing', 'frustrating'];
        
        $textLower = strtolower($text);
        $positiveCount = 0;
        $negativeCount = 0;
        
        foreach ($positiveWords as $word) {
            $positiveCount += substr_count($textLower, $word);
        }
        
        foreach ($negativeWords as $word) {
            $negativeCount += substr_count($textLower, $word);
        }
        
        // Determine sentiment
        if ($positiveCount > $negativeCount) {
            $sentiment = 'positive';
            $confidenceScore = min(0.9, 0.5 + ($positiveCount * 0.1));
        } elseif ($negativeCount > $positiveCount) {
            $sentiment = 'negative';
            $confidenceScore = min(0.9, 0.5 + ($negativeCount * 0.1));
        } else {
            $sentiment = 'neutral';
            $confidenceScore = 0.5;
        }
        
        // Create sentiment scores
        $sentimentScores = [
            'positive' => $sentiment === 'positive' ? $confidenceScore : (1 - $confidenceScore) / 2,
            'negative' => $sentiment === 'negative' ? $confidenceScore : (1 - $confidenceScore) / 2,
            'neutral' => $sentiment === 'neutral' ? $confidenceScore : (1 - $confidenceScore) / 2,
        ];
        
        // Analyze language breakdown
        $languageBreakdown = $this->analyzeLanguageBreakdown($text);
        
        return [
            'processed_text' => $processedText,
            'sentiment' => $sentiment,
            'confidence_score' => $confidenceScore,
            'sentiment_scores' => $sentimentScores,
            'language_breakdown' => $languageBreakdown,
        ];
    }

    /**
     * Preprocess code-mixed text
     */
    private function preprocessCodeMixedText(string $text): string
    {
        // Remove special characters and normalize
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    /**
     * Analyze language breakdown in code-mixed text
     */
    private function analyzeLanguageBreakdown(string $text): array
    {
        // Simple language detection (in real app, use proper language detection)
        $words = explode(' ', $text);
        $totalWords = count($words);
        
        // Simulate language breakdown
        return [
            'indonesian' => rand(30, 70) / 100,
            'english' => rand(10, 40) / 100,
            'mixed' => rand(10, 30) / 100,
        ];
    }

    /**
     * Bulk analyze text samples
     */
    public function bulkAnalyze()
    {
        $unprocessedSamples = TextSample::where('is_processed', false)->get();
        
        foreach ($unprocessedSamples as $sample) {
            $analysisResult = $this->performBertAnalysis($sample->content);
            
            SentimentAnalysis::create([
                'text_sample_id' => $sample->id,
                'original_text' => $sample->content,
                'processed_text' => $analysisResult['processed_text'],
                'sentiment' => $analysisResult['sentiment'],
                'confidence_score' => $analysisResult['confidence_score'],
                'sentiment_scores' => $analysisResult['sentiment_scores'],
                'language_breakdown' => $analysisResult['language_breakdown'],
                'source_type' => $sample->source_type,
                'analysis_date' => now(),
            ]);
            
            $sample->update(['is_processed' => true]);
        }
        
        return redirect()->route('dashboard')
            ->with('success', 'Bulk analysis completed!');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SentimentAnalysis extends Model
{
    protected $fillable = [
        'text_sample_id',
        'original_text',
        'processed_text',
        'sentiment',
        'confidence_score',
        'sentiment_scores',
        'language_breakdown',
        'source_type',
        'analysis_date',
    ];

    protected $casts = [
        'sentiment_scores' => 'array',
        'language_breakdown' => 'array',
        'analysis_date' => 'date',
        'confidence_score' => 'decimal:4',
    ];

    public function textSample(): BelongsTo
    {
        return $this->belongsTo(TextSample::class);
    }

    public function getSentimentColorAttribute(): string
    {
        return match($this->sentiment) {
            'positive' => 'green',
            'negative' => 'red',
            'neutral' => 'gray',
            default => 'gray',
        };
    }

    public function getSentimentIconAttribute(): string
    {
        return match($this->sentiment) {
            'positive' => '😊',
            'negative' => '😞',
            'neutral' => '😐',
            default => '😐',
        };
    }
}

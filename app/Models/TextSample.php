<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TextSample extends Model
{
    protected $fillable = [
        'content',
        'source_type',
        'source_platform',
        'author_id',
        'author_name',
        'published_at',
        'location',
        'metadata',
        'is_processed',
    ];

    protected $casts = [
        'metadata' => 'array',
        'published_at' => 'datetime',
        'is_processed' => 'boolean',
    ];

    public function sentimentAnalysis(): HasOne
    {
        return $this->hasOne(SentimentAnalysis::class);
    }

    public function getShortContentAttribute(): string
    {
        return strlen($this->content) > 100 
            ? substr($this->content, 0, 100) . '...' 
            : $this->content;
    }
}

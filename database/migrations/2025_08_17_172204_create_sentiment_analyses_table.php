<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sentiment_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('text_sample_id');
            $table->text('original_text');
            $table->text('processed_text');
            $table->enum('sentiment', ['positive', 'negative', 'neutral']);
            $table->decimal('confidence_score', 5, 4);
            $table->json('sentiment_scores')->nullable(); // Store detailed scores
            $table->json('language_breakdown')->nullable(); // Store language mix analysis
            $table->string('source_type')->nullable(); // social_media, news, survey, etc.
            $table->date('analysis_date');
            $table->timestamps();
            
            $table->index(['sentiment', 'analysis_date']);
            $table->index(['source_type', 'analysis_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sentiment_analyses');
    }
};

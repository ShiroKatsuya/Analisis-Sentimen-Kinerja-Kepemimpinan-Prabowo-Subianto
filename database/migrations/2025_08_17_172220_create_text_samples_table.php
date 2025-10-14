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
        Schema::create('text_samples', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->string('source_type'); // social_media, news, survey, etc.
            $table->string('source_platform')->nullable(); // twitter, facebook, instagram, etc.
            $table->string('author_id')->nullable();
            $table->string('author_name')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->string('location')->nullable();
            $table->json('metadata')->nullable(); // Additional metadata
            $table->boolean('is_processed')->default(false);
            $table->timestamps();
            
            $table->index(['source_type', 'published_at']);
            $table->index(['is_processed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('text_samples');
    }
};

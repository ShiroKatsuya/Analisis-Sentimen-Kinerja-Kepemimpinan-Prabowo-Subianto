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
        Schema::table('sentiment_analyses', function (Blueprint $table) {
            // Ensure the column exists and is the correct type (unsigned big integer)
            // Then add the foreign key constraint now that `text_samples` table exists
            $table->foreign('text_sample_id', 'sentiment_analyses_text_sample_id_foreign')
                ->references('id')
                ->on('text_samples')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sentiment_analyses', function (Blueprint $table) {
            $table->dropForeign('sentiment_analyses_text_sample_id_foreign');
        });
    }
};



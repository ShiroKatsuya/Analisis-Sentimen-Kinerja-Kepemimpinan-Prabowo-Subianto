<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SentimentAnalysisController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/analytics', [DashboardController::class, 'analytics'])->name('analytics');

Route::resource('sentiment-analysis', SentimentAnalysisController::class)->parameters([
    'sentiment-analysis' => 'sentimentAnalysis'
]);
Route::post('sentiment-analysis/bulk-analyze', [SentimentAnalysisController::class, 'bulkAnalyze'])->name('sentiment-analysis.bulk-analyze');

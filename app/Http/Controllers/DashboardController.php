<?php

namespace App\Http\Controllers;

use App\Models\SentimentAnalysis;
use App\Models\TextSample;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Get overall statistics
        $totalSamples = TextSample::count();
        $processedSamples = TextSample::where('is_processed', true)->count();
        $totalAnalyses = SentimentAnalysis::count();

        // Get sentiment distribution
        $sentimentDistribution = SentimentAnalysis::select('sentiment', DB::raw('count(*) as count'))
            ->groupBy('sentiment')
            ->get()
            ->pluck('count', 'sentiment')
            ->toArray();

        // Get sentiment over time (last 30 days)
        $sentimentOverTime = SentimentAnalysis::select(
                'analysis_date',
                'sentiment',
                DB::raw('count(*) as count')
            )
            ->where('analysis_date', '>=', now()->subDays(30))
            ->groupBy('analysis_date', 'sentiment')
            ->orderBy('analysis_date')
            ->get();

        // Get source type distribution
        $sourceDistribution = SentimentAnalysis::select('source_type', DB::raw('count(*) as count'))
            ->groupBy('source_type')
            ->get()
            ->pluck('count', 'source_type')
            ->toArray();

        // Get average confidence scores by sentiment
        $confidenceBySentiment = SentimentAnalysis::select(
                'sentiment',
                DB::raw('AVG(confidence_score) as avg_confidence')
            )
            ->groupBy('sentiment')
            ->get()
            ->pluck('avg_confidence', 'sentiment')
            ->toArray();

        // Get recent analyses
        $recentAnalyses = SentimentAnalysis::with('textSample')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard', compact(
            'totalSamples',
            'processedSamples',
            'totalAnalyses',
            'sentimentDistribution',
            'sentimentOverTime',
            'sourceDistribution',
            'confidenceBySentiment',
            'recentAnalyses'
        ));
    }

    public function analytics()
    {
        // Get detailed analytics data for charts
        $monthlySentiment = SentimentAnalysis::select(
                DB::raw('DATE_FORMAT(analysis_date, "%Y-%m") as month'),
                'sentiment',
                DB::raw('count(*) as count')
            )
            ->groupBy('month', 'sentiment')
            ->orderBy('month')
            ->get();

        $sourceSentiment = SentimentAnalysis::select(
                'source_type',
                'sentiment',
                DB::raw('count(*) as count')
            )
            ->groupBy('source_type', 'sentiment')
            ->get();

        $confidenceDistribution = SentimentAnalysis::select(
                DB::raw('ROUND(confidence_score, 1) as confidence_range'),
                DB::raw('count(*) as count')
            )
            ->groupBy('confidence_range')
            ->orderBy('confidence_range')
            ->get();

        return response()->json([
            'monthlySentiment' => $monthlySentiment,
            'sourceSentiment' => $sourceSentiment,
            'confidenceDistribution' => $confidenceDistribution,
        ]);
    }
}

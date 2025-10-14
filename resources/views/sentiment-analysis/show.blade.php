@extends('layouts.app')

@section('title', 'Analysis Details')

@section('content')
<div x-data="analysisDetails()">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Analysis Details</h1>
                <p class="mt-2 text-gray-600">Detailed sentiment analysis results</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('sentiment-analysis.edit', $sentimentAnalysis) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                    </svg>
                    Edit
                </a>
                <a href="{{ route('sentiment-analysis.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Back to List
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column - Analysis Results -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Sentiment Result Card -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-900">Sentiment Analysis Result</h2>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        {{ $sentimentAnalysis->sentiment === 'positive' ? 'bg-green-100 text-green-800' : 
                           ($sentimentAnalysis->sentiment === 'negative' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                        {{ $sentimentAnalysis->sentiment_icon }} {{ ucfirst($sentimentAnalysis->sentiment) }}
                    </span>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Confidence Score -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-sm font-medium text-gray-900 mb-2">Confidence Score</h3>
                        <div class="flex items-center">
                            <div class="flex-1 bg-gray-200 rounded-full h-2 mr-3">
                                <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $sentimentAnalysis->confidence_score * 100 }}%"></div>
                            </div>
                            <span class="text-lg font-semibold text-gray-900">{{ round($sentimentAnalysis->confidence_score * 100, 1) }}%</span>
                        </div>
                    </div>

                    <!-- Analysis Date -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-sm font-medium text-gray-900 mb-2">Analysis Date</h3>
                        <p class="text-lg font-semibold text-gray-900">{{ $sentimentAnalysis->analysis_date->format('M j, Y') }}</p>
                        <p class="text-sm text-gray-500">{{ $sentimentAnalysis->analysis_date->format('g:i A') }}</p>
                    </div>
                </div>
            </div>

            <!-- Sentiment Scores Chart -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Sentiment Scores Breakdown</h3>
                <div class="h-64">
                    <canvas id="sentimentScoresChart"></canvas>
                </div>
            </div>

            <!-- Language Breakdown -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Language Breakdown</h3>
                <div class="h-64">
                    <canvas id="languageChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Right Column - Text Details -->
        <div class="space-y-6">
            <!-- Original Text -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Original Text</h3>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-gray-900 whitespace-pre-wrap">{{ $sentimentAnalysis->original_text }}</p>
                </div>
            </div>

            <!-- Processed Text -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Processed Text</h3>
                <div class="bg-blue-50 rounded-lg p-4">
                    <p class="text-gray-900 whitespace-pre-wrap">{{ $sentimentAnalysis->processed_text }}</p>
                </div>
                <p class="mt-2 text-sm text-gray-500">Text after preprocessing for BERT analysis</p>
            </div>

            <!-- Source Information -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Source Information</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Source Type</dt>
                        <dd class="text-sm text-gray-900 capitalize">{{ str_replace('_', ' ', $sentimentAnalysis->source_type) }}</dd>
                    </div>
                    @if($sentimentAnalysis->textSample->source_platform)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Platform</dt>
                        <dd class="text-sm text-gray-900 capitalize">{{ str_replace('_', ' ', $sentimentAnalysis->textSample->source_platform) }}</dd>
                    </div>
                    @endif
                    @if($sentimentAnalysis->textSample->author_name)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Author</dt>
                        <dd class="text-sm text-gray-900">{{ $sentimentAnalysis->textSample->author_name }}</dd>
                    </div>
                    @endif
                    @if($sentimentAnalysis->textSample->location)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Location</dt>
                        <dd class="text-sm text-gray-900">{{ $sentimentAnalysis->textSample->location }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Published</dt>
                        <dd class="text-sm text-gray-900">{{ $sentimentAnalysis->textSample->published_at ? $sentimentAnalysis->textSample->published_at->format('M j, Y g:i A') : 'Not specified' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Analysis Metadata -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Analysis Metadata</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Analysis ID</dt>
                        <dd class="text-sm text-gray-900">{{ $sentimentAnalysis->id }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Created</dt>
                        <dd class="text-sm text-gray-900">{{ $sentimentAnalysis->created_at->format('M j, Y g:i A') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                        <dd class="text-sm text-gray-900">{{ $sentimentAnalysis->updated_at->format('M j, Y g:i A') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <!-- Detailed Sentiment Scores -->
    @if($sentimentAnalysis->sentiment_scores)
    <div class="mt-8 bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Detailed Sentiment Scores</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($sentimentAnalysis->sentiment_scores as $sentiment => $score)
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-900 capitalize">{{ $sentiment }}</span>
                    <span class="text-sm font-semibold text-gray-900">{{ round($score * 100, 1) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="h-2 rounded-full {{ $sentiment === 'positive' ? 'bg-green-500' : ($sentiment === 'negative' ? 'bg-red-500' : 'bg-gray-500') }}" 
                         style="width: {{ $score * 100 }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Language Breakdown Details -->
    @if($sentimentAnalysis->language_breakdown)
    <div class="mt-8 bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Language Breakdown Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($sentimentAnalysis->language_breakdown as $language => $percentage)
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-900 capitalize">{{ $language }}</span>
                    <span class="text-sm font-semibold text-gray-900">{{ round($percentage * 100, 1) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="h-2 rounded-full bg-blue-500" style="width: {{ $percentage * 100 }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
function analysisDetails() {
    return {
        init() {
            this.initCharts();
        },

        initCharts() {
            // Sentiment Scores Chart
            const sentimentCtx = document.getElementById('sentimentScoresChart').getContext('2d');
            new Chart(sentimentCtx, {
                type: 'bar',
                data: {
                    labels: ['Positive', 'Negative', 'Neutral'],
                    datasets: [{
                        label: 'Confidence Score',
                        data: [
                            {{ ($sentimentAnalysis->sentiment_scores['positive'] ?? 0) * 100 }},
                            {{ ($sentimentAnalysis->sentiment_scores['negative'] ?? 0) * 100 }},
                            {{ ($sentimentAnalysis->sentiment_scores['neutral'] ?? 0) * 100 }}
                        ],
                        backgroundColor: ['#10B981', '#EF4444', '#6B7280'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            });

            // Language Breakdown Chart
            const languageCtx = document.getElementById('languageChart').getContext('2d');
            new Chart(languageCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode(array_keys($sentimentAnalysis->language_breakdown ?? [])) !!},
                    datasets: [{
                        data: {!! json_encode(array_values($sentimentAnalysis->language_breakdown ?? [])) !!},
                        backgroundColor: ['#3B82F6', '#8B5CF6', '#F59E0B', '#10B981'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    }
}
</script>
@endpush
@endsection

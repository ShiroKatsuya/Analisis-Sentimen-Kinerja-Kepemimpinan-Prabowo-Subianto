@extends('layouts.app')

@section('title', 'Edit Analysis')

@section('content')
<div x-data="editAnalysis()">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Edit Analysis</h1>
                <p class="mt-2 text-gray-600">Modify sentiment analysis results</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('sentiment-analysis.show', $sentimentAnalysis) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.639 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.639 0-8.573-3.007-9.963-7.178z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    View Details
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

    <!-- Form -->
    <div class="bg-white shadow rounded-lg">
        <form method="POST" action="{{ route('sentiment-analysis.update', $sentimentAnalysis) }}" class="space-y-6 p-6">
            @csrf
            @method('PUT')
            
            <!-- Original Text (Read-only) -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Original Text</label>
                <div class="mt-1 bg-gray-50 rounded-md p-3">
                    <p class="text-gray-900">{{ $sentimentAnalysis->original_text }}</p>
                </div>
                <p class="mt-1 text-sm text-gray-500">Original text cannot be modified</p>
            </div>

            <!-- Sentiment -->
            <div>
                <label for="sentiment" class="block text-sm font-medium text-gray-700">
                    Sentiment
                    <span class="text-red-500">*</span>
                </label>
                <select
                    id="sentiment"
                    name="sentiment"
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                    required
                    x-model="formData.sentiment"
                >
                    <option value="positive" {{ $sentimentAnalysis->sentiment === 'positive' ? 'selected' : '' }}>Positive</option>
                    <option value="negative" {{ $sentimentAnalysis->sentiment === 'negative' ? 'selected' : '' }}>Negative</option>
                    <option value="neutral" {{ $sentimentAnalysis->sentiment === 'neutral' ? 'selected' : '' }}>Neutral</option>
                </select>
                @error('sentiment')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confidence Score -->
            <div>
                <label for="confidence_score" class="block text-sm font-medium text-gray-700">
                    Confidence Score
                    <span class="text-red-500">*</span>
                </label>
                <div class="mt-1 relative">
                    <input
                        type="range"
                        id="confidence_score"
                        name="confidence_score"
                        min="0"
                        max="1"
                        step="0.01"
                        value="{{ $sentimentAnalysis->confidence_score }}"
                        class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                        x-model="formData.confidence_score"
                        @input="updateConfidenceDisplay()"
                    >
                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-sm text-gray-500">0%</span>
                        <span class="text-lg font-semibold text-gray-900" x-text="`${Math.round(formData.confidence_score * 100)}%`"></span>
                        <span class="text-sm text-gray-500">100%</span>
                    </div>
                </div>
                @error('confidence_score')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Current Analysis Info -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Current Analysis Information</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p><strong>Analysis Date:</strong> {{ $sentimentAnalysis->analysis_date->format('M j, Y g:i A') }}</p>
                            <p><strong>Source Type:</strong> {{ ucfirst(str_replace('_', ' ', $sentimentAnalysis->source_type)) }}</p>
                            <p><strong>BERT Model Version:</strong> Code-Mixed Indonesian-English BERT</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sentiment Preview -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="text-sm font-medium text-gray-900 mb-3">Sentiment Preview</h3>
                <div class="flex items-center space-x-4">
                    <div class="flex items-center">
                        <span class="text-2xl mr-2" x-text="getSentimentIcon(formData.sentiment)"></span>
                        <span class="text-lg font-medium text-gray-900" x-text="getSentimentLabel(formData.sentiment)"></span>
                    </div>
                    <div class="flex items-center">
                        <span class="text-sm text-gray-500 mr-2">Confidence:</span>
                        <span class="text-lg font-semibold text-gray-900" x-text="`${Math.round(formData.confidence_score * 100)}%`"></span>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('sentiment-analysis.show', $sentimentAnalysis) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button
                    type="submit"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    Update Analysis
                </button>
            </div>
        </form>
    </div>

    <!-- Analysis History -->
    <div class="mt-8 bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Analysis History</h3>
            <p class="mt-1 text-sm text-gray-600">Track changes to this analysis</p>
        </div>
        <div class="p-6">
            <div class="flow-root">
                <ul role="list" class="-mb-8">
                    <li>
                        <div class="relative pb-8">
                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                            <div class="relative flex space-x-3">
                                <div>
                                    <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.008v.008H12V8.25z" />
                                        </svg>
                                    </span>
                                </div>
                                <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                    <div>
                                        <p class="text-sm text-gray-500">
                                            Analysis created with 
                                            <span class="font-medium text-gray-900">{{ ucfirst($sentimentAnalysis->sentiment) }}</span> 
                                            sentiment ({{ round($sentimentAnalysis->confidence_score * 100, 1) }}% confidence)
                                        </p>
                                    </div>
                                    <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                        <time datetime="{{ $sentimentAnalysis->created_at->format('Y-m-d H:i:s') }}">
                                            {{ $sentimentAnalysis->created_at->format('M j, Y') }}
                                        </time>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    @if($sentimentAnalysis->updated_at->ne($sentimentAnalysis->created_at))
                    <li>
                        <div class="relative pb-8">
                            <div class="relative flex space-x-3">
                                <div>
                                    <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                        </svg>
                                    </span>
                                </div>
                                <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                    <div>
                                        <p class="text-sm text-gray-500">
                                            Analysis last updated
                                        </p>
                                    </div>
                                    <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                        <time datetime="{{ $sentimentAnalysis->updated_at->format('Y-m-d H:i:s') }}">
                                            {{ $sentimentAnalysis->updated_at->format('M j, Y') }}
                                        </time>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function editAnalysis() {
    return {
        formData: {
            sentiment: '{{ $sentimentAnalysis->sentiment }}',
            confidence_score: {{ $sentimentAnalysis->confidence_score }}
        },

        init() {
            this.updateConfidenceDisplay();
        },

        updateConfidenceDisplay() {
            // This function is called when the range input changes
            // The display is automatically updated via x-text binding
        },

        getSentimentIcon(sentiment) {
            const icons = {
                'positive': '😊',
                'negative': '😞',
                'neutral': '😐'
            };
            return icons[sentiment] || '😐';
        },

        getSentimentLabel(sentiment) {
            return sentiment.charAt(0).toUpperCase() + sentiment.slice(1);
        }
    }
}
</script>
@endpush
@endsection

@extends('layouts.app')

@section('title', 'New Sentiment Analysis')

@section('content')
<div x-data="createAnalysis()">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">New Sentiment Analysis</h1>
                <p class="mt-2 text-gray-600">Analyze code-mixed text using BERT model</p>
            </div>
            <a href="{{ route('sentiment-analysis.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Back to List
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white shadow rounded-lg">
        <form method="POST" action="{{ route('sentiment-analysis.store') }}" class="space-y-6 p-6">
            @csrf
            
            <!-- Text Input -->
            <div>
                <label for="text" class="block text-sm font-medium text-gray-700">
                    Text to Analyze
                    <span class="text-red-500">*</span>
                </label>
                <div class="mt-1">
                    <textarea
                        id="text"
                        name="text"
                        rows="6"
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                        placeholder="Enter the code-mixed text you want to analyze..."
                        required
                        x-model="formData.text"
                        @input="updateCharacterCount()"
                    >{{ old('text') }}</textarea>
                </div>
                <div class="mt-2 flex justify-between text-sm text-gray-500">
                    <span x-text="`${characterCount} characters`"></span>
                    <span x-text="`${wordCount} words`"></span>
                </div>
                @error('text')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Source Type -->
            <div>
                <label for="source_type" class="block text-sm font-medium text-gray-700">
                    Source Type
                    <span class="text-red-500">*</span>
                </label>
                <select
                    id="source_type"
                    name="source_type"
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                    required
                    x-model="formData.source_type"
                >
                    <option value="">Select source type</option>
                    <option value="social_media" {{ old('source_type') === 'social_media' ? 'selected' : '' }}>Social Media</option>
                    <option value="news" {{ old('source_type') === 'news' ? 'selected' : '' }}>News</option>
                    <option value="survey" {{ old('source_type') === 'survey' ? 'selected' : '' }}>Survey</option>
                    <option value="other" {{ old('source_type') === 'other' ? 'selected' : '' }}>Other</option>
                </select>
                @error('source_type')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Source Platform (conditional) -->
            <div x-show="formData.source_type === 'social_media'">
                <label for="source_platform" class="block text-sm font-medium text-gray-700">
                    Platform
                </label>
                <select
                    id="source_platform"
                    name="source_platform"
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                    x-model="formData.source_platform"
                >
                    <option value="">Select platform</option>
                    <option value="twitter" {{ old('source_platform') === 'twitter' ? 'selected' : '' }}>Twitter/X</option>
                    <option value="facebook" {{ old('source_platform') === 'facebook' ? 'selected' : '' }}>Facebook</option>
                    <option value="instagram" {{ old('source_platform') === 'instagram' ? 'selected' : '' }}>Instagram</option>
                    <option value="tiktok" {{ old('source_platform') === 'tiktok' ? 'selected' : '' }}>TikTok</option>
                    <option value="youtube" {{ old('source_platform') === 'youtube' ? 'selected' : '' }}>YouTube</option>
                    <option value="other" {{ old('source_platform') === 'other' ? 'selected' : '' }}>Other</option>
                </select>
                @error('source_platform')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Analysis Preview -->
            <div x-show="formData.text.length > 0" class="bg-gray-50 rounded-lg p-4">
                <h3 class="text-sm font-medium text-gray-900 mb-3">Analysis Preview</h3>
                <div class="space-y-3">
                    <div>
                        <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Original Text</label>
                        <p class="mt-1 text-sm text-gray-900" x-text="formData.text"></p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Source</label>
                        <p class="mt-1 text-sm text-gray-900">
                            <span x-text="getSourceTypeLabel(formData.source_type)"></span>
                            <span x-show="formData.source_type === 'social_media' && formData.source_platform" x-text="` - ${getPlatformLabel(formData.source_platform)}`"></span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- BERT Model Info -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423L16.5 15.75l.394 1.183a2.25 2.25 0 001.423 1.423L19.5 18.75l-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">BERT Model Analysis</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p>This text will be analyzed using a BERT-based model specifically trained for code-mixed Indonesian-English text. The analysis will provide:</p>
                            <ul class="mt-1 list-disc list-inside space-y-1">
                                <li>Sentiment classification (Positive/Negative/Neutral)</li>
                                <li>Confidence scores for each sentiment</li>
                                <li>Language breakdown analysis</li>
                                <li>Text preprocessing results</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('sentiment-analysis.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button
                    type="submit"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    :disabled="!formData.text || !formData.source_type"
                    :class="{ 'opacity-50 cursor-not-allowed': !formData.text || !formData.source_type }"
                >
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423L16.5 15.75l.394 1.183a2.25 2.25 0 001.423 1.423L19.5 18.75l-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                    </svg>
                    Analyze Text
                </button>
            </div>
        </form>
    </div>

    <!-- Sample Texts -->
    <div class="mt-8 bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Sample Code-Mixed Texts</h3>
            <p class="mt-1 text-sm text-gray-600">Try analyzing these sample texts to see how the BERT model works</p>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="border border-gray-200 rounded-lg p-4 hover:border-indigo-300 transition-colors cursor-pointer" @click="loadSampleText('Prabowo dan Gibran sudah satu tahun memimpin Indonesia. Overall, mereka sudah melakukan yang terbaik untuk negara ini. Semoga ke depannya bisa lebih baik lagi!')">
                    <h4 class="font-medium text-gray-900 mb-2">Positive Sample</h4>
                    <p class="text-sm text-gray-600 line-clamp-3">Prabowo dan Gibran sudah satu tahun memimpin Indonesia. Overall, mereka sudah melakukan yang terbaik untuk negara ini...</p>
                </div>
                <div class="border border-gray-200 rounded-lg p-4 hover:border-indigo-300 transition-colors cursor-pointer" @click="loadSampleText('Setahun sudah tapi masih belum ada perubahan yang signifikan. Economy masih struggling dan banyak masalah yang belum terselesaikan. Very disappointing!')">
                    <h4 class="font-medium text-gray-900 mb-2">Negative Sample</h4>
                    <p class="text-sm text-gray-600 line-clamp-3">Setahun sudah tapi masih belum ada perubahan yang signifikan. Economy masih struggling dan banyak masalah...</p>
                </div>
                <div class="border border-gray-200 rounded-lg p-4 hover:border-indigo-300 transition-colors cursor-pointer" @click="loadSampleText('Prabowo-Gibran administration sudah berjalan satu tahun. Ada beberapa progress yang bagus, tapi masih ada juga challenges yang perlu diatasi. Let\'s see how they perform in the next year.')">
                    <h4 class="font-medium text-gray-900 mb-2">Neutral Sample</h4>
                    <p class="text-sm text-gray-600 line-clamp-3">Prabowo-Gibran administration sudah berjalan satu tahun. Ada beberapa progress yang bagus, tapi masih ada juga challenges...</p>
                </div>
                <div class="border border-gray-200 rounded-lg p-4 hover:border-indigo-300 transition-colors cursor-pointer" @click="loadSampleText('Wow! Prabowo dan Gibran benar-benar amazing! Mereka sudah berhasil membuat Indonesia lebih baik dalam satu tahun. Semua program mereka sangat helpful dan beneficial untuk masyarakat. I\'m so proud of them!')">
                    <h4 class="font-medium text-gray-900 mb-2">Very Positive Sample</h4>
                    <p class="text-sm text-gray-600 line-clamp-3">Wow! Prabowo dan Gibran benar-benar amazing! Mereka sudah berhasil membuat Indonesia lebih baik dalam satu tahun...</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function createAnalysis() {
    return {
        formData: {
            text: '{{ old("text") }}',
            source_type: '{{ old("source_type") }}',
            source_platform: '{{ old("source_platform") }}'
        },
        characterCount: 0,
        wordCount: 0,

        init() {
            this.updateCharacterCount();
        },

        updateCharacterCount() {
            this.characterCount = this.formData.text.length;
            this.wordCount = this.formData.text.trim() ? this.formData.text.trim().split(/\s+/).length : 0;
        },

        loadSampleText(text) {
            this.formData.text = text;
            this.formData.source_type = 'social_media';
            this.formData.source_platform = 'twitter';
            this.updateCharacterCount();
            
            // Scroll to form
            document.getElementById('text').scrollIntoView({ behavior: 'smooth', block: 'center' });
        },

        getSourceTypeLabel(type) {
            const labels = {
                'social_media': 'Social Media',
                'news': 'News',
                'survey': 'Survey',
                'other': 'Other'
            };
            return labels[type] || type;
        },

        getPlatformLabel(platform) {
            const labels = {
                'twitter': 'Twitter/X',
                'facebook': 'Facebook',
                'instagram': 'Instagram',
                'tiktok': 'TikTok',
                'youtube': 'YouTube',
                'other': 'Other'
            };
            return labels[platform] || platform;
        }
    }
}
</script>
@endpush
@endsection

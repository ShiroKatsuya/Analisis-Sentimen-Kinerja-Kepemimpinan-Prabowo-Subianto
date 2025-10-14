<?php

namespace Database\Seeders;

use App\Models\SentimentAnalysis;
use App\Models\TextSample;
use Illuminate\Database\Seeder;

class SentimentAnalysisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sampleTexts = [
            [
                'content' => 'Prabowo dan Gibran sudah satu tahun memimpin Indonesia. Overall, mereka sudah melakukan yang terbaik untuk negara ini. Semoga ke depannya bisa lebih baik lagi!',
                'source_type' => 'social_media',
                'source_platform' => 'twitter',
                'sentiment' => 'positive',
                'confidence_score' => 0.85,
                'sentiment_scores' => ['positive' => 0.85, 'negative' => 0.10, 'neutral' => 0.05],
                'language_breakdown' => ['indonesian' => 0.70, 'english' => 0.25, 'mixed' => 0.05]
            ],
            [
                'content' => 'Setahun sudah tapi masih belum ada perubahan yang signifikan. Economy masih struggling dan banyak masalah yang belum terselesaikan. Very disappointing!',
                'source_type' => 'social_media',
                'source_platform' => 'facebook',
                'sentiment' => 'negative',
                'confidence_score' => 0.78,
                'sentiment_scores' => ['positive' => 0.08, 'negative' => 0.78, 'neutral' => 0.14],
                'language_breakdown' => ['indonesian' => 0.60, 'english' => 0.35, 'mixed' => 0.05]
            ],
            [
                'content' => 'Prabowo-Gibran administration sudah berjalan satu tahun. Ada beberapa progress yang bagus, tapi masih ada juga challenges yang perlu diatasi. Let\'s see how they perform in the next year.',
                'source_type' => 'news',
                'source_platform' => null,
                'sentiment' => 'neutral',
                'confidence_score' => 0.72,
                'sentiment_scores' => ['positive' => 0.25, 'negative' => 0.18, 'neutral' => 0.72],
                'language_breakdown' => ['indonesian' => 0.55, 'english' => 0.40, 'mixed' => 0.05]
            ],
            [
                'content' => 'Wow! Prabowo dan Gibran benar-benar amazing! Mereka sudah berhasil membuat Indonesia lebih baik dalam satu tahun. Semua program mereka sangat helpful dan beneficial untuk masyarakat. I\'m so proud of them!',
                'source_type' => 'social_media',
                'source_platform' => 'instagram',
                'sentiment' => 'positive',
                'confidence_score' => 0.92,
                'sentiment_scores' => ['positive' => 0.92, 'negative' => 0.05, 'neutral' => 0.03],
                'language_breakdown' => ['indonesian' => 0.65, 'english' => 0.30, 'mixed' => 0.05]
            ],
            [
                'content' => 'The current administration has failed to address key economic issues. Inflation is still high, unemployment rates are concerning, and the overall economic growth is not meeting expectations. This is very concerning for the future of Indonesia.',
                'source_type' => 'news',
                'source_platform' => null,
                'sentiment' => 'negative',
                'confidence_score' => 0.81,
                'sentiment_scores' => ['positive' => 0.06, 'negative' => 0.81, 'neutral' => 0.13],
                'language_breakdown' => ['indonesian' => 0.20, 'english' => 0.75, 'mixed' => 0.05]
            ],
            [
                'content' => 'Setelah satu tahun, saya melihat ada kemajuan dalam beberapa aspek. Namun, masih banyak yang perlu diperbaiki. Overall, saya memberikan rating 7/10 untuk kinerja mereka.',
                'source_type' => 'survey',
                'source_platform' => null,
                'sentiment' => 'neutral',
                'confidence_score' => 0.68,
                'sentiment_scores' => ['positive' => 0.30, 'negative' => 0.22, 'neutral' => 0.68],
                'language_breakdown' => ['indonesian' => 0.85, 'english' => 0.10, 'mixed' => 0.05]
            ],
            [
                'content' => 'Prabowo dan Gibran telah menunjukkan leadership yang excellent dalam mengatasi berbagai challenges. Their policies have been very effective and the people are starting to see positive results.',
                'source_type' => 'social_media',
                'source_platform' => 'twitter',
                'sentiment' => 'positive',
                'confidence_score' => 0.88,
                'sentiment_scores' => ['positive' => 0.88, 'negative' => 0.08, 'neutral' => 0.04],
                'language_breakdown' => ['indonesian' => 0.45, 'english' => 0.50, 'mixed' => 0.05]
            ],
            [
                'content' => 'Tidak ada perubahan berarti dalam setahun ini. Semua janji-janji kampanye belum terwujud. Very frustrating untuk melihat kondisi negara yang masih sama saja.',
                'source_type' => 'social_media',
                'source_platform' => 'facebook',
                'sentiment' => 'negative',
                'confidence_score' => 0.76,
                'sentiment_scores' => ['positive' => 0.12, 'negative' => 0.76, 'neutral' => 0.12],
                'language_breakdown' => ['indonesian' => 0.75, 'english' => 0.20, 'mixed' => 0.05]
            ],
            [
                'content' => 'The administration\'s first year has been a mixed bag. Some initiatives have been successful, while others need more time to show results. We should give them more time to implement their vision.',
                'source_type' => 'news',
                'source_platform' => null,
                'sentiment' => 'neutral',
                'confidence_score' => 0.74,
                'sentiment_scores' => ['positive' => 0.28, 'negative' => 0.20, 'neutral' => 0.74],
                'language_breakdown' => ['indonesian' => 0.15, 'english' => 0.80, 'mixed' => 0.05]
            ],
            [
                'content' => 'Saya sangat impressed dengan kinerja Prabowo-Gibran dalam setahun ini. Mereka berhasil membuat beberapa breakthrough yang significant. Indonesia is definitely heading in the right direction!',
                'source_type' => 'social_media',
                'source_platform' => 'instagram',
                'sentiment' => 'positive',
                'confidence_score' => 0.90,
                'sentiment_scores' => ['positive' => 0.90, 'negative' => 0.06, 'neutral' => 0.04],
                'language_breakdown' => ['indonesian' => 0.60, 'english' => 0.35, 'mixed' => 0.05]
            ]
        ];

        foreach ($sampleTexts as $sample) {
            // Create text sample
            $textSample = TextSample::create([
                'content' => $sample['content'],
                'source_type' => $sample['source_type'],
                'source_platform' => $sample['source_platform'],
                'author_name' => 'Sample User',
                'published_at' => now()->subDays(rand(1, 30)),
                'is_processed' => true,
            ]);

            // Create sentiment analysis
            SentimentAnalysis::create([
                'text_sample_id' => $textSample->id,
                'original_text' => $sample['content'],
                'processed_text' => $this->preprocessText($sample['content']),
                'sentiment' => $sample['sentiment'],
                'confidence_score' => $sample['confidence_score'],
                'sentiment_scores' => $sample['sentiment_scores'],
                'language_breakdown' => $sample['language_breakdown'],
                'source_type' => $sample['source_type'],
                'analysis_date' => now()->subDays(rand(1, 30)),
            ]);
        }
    }

    private function preprocessText(string $text): string
    {
        // Simple preprocessing
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }
}

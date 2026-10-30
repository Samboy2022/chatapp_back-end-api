<?php

use App\Models\CbtSubject;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Starter content for CBT practice, so the screen isn't empty on first
 * deploy: three slides and a small bank of original practice questions
 * (database/data/cbt_sample_questions.php). Real past questions are added
 * from the admin (CBT Questions → Import).
 *
 * A migration rather than a seeder because deploys only run `migrate`.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('cbt_slides')->doesntExist()) {
            $now = now();
            DB::table('cbt_slides')->insert([
                [
                    'badge' => 'RECOMMENDED', 'title' => 'Start Your Practice',
                    'subtitle' => 'Master your upcoming exams with real-time CBT simulations and expert guides.',
                    'cta_label' => 'Begin Now', 'cta_target' => 'jamb',
                    'color_from' => '#0B1F7A', 'color_to' => '#111827', 'sort_order' => 1,
                    'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
                ],
                [
                    'badge' => 'NEW', 'title' => 'Learn as You Go',
                    'subtitle' => 'Practice Mode shows the right answer and why, the moment you answer.',
                    'cta_label' => 'Try WAEC Prep', 'cta_target' => 'waec',
                    'color_from' => '#065F46', 'color_to' => '#0F172A', 'sort_order' => 2,
                    'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
                ],
                [
                    'badge' => 'SPEED TEST', 'title' => 'Beat the Clock',
                    'subtitle' => 'A 20-minute Mock CBT to sharpen your pace before exam day.',
                    'cta_label' => 'Start Mock CBT', 'cta_target' => 'mock',
                    'color_from' => '#7C2D12', 'color_to' => '#111827', 'sort_order' => 3,
                    'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
                ],
            ]);
        }

        if (DB::table('cbt_questions')->exists()) {
            return;
        }

        $bank = require database_path('data/cbt_sample_questions.php');
        $order = 0;
        foreach ($bank as $subjectName => $topics) {
            $now = now();
            $subjectId = DB::table('cbt_subjects')->insertGetId([
                'name' => $subjectName,
                'slug' => CbtSubject::slugFor($subjectName),
                'sort_order' => ++$order,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($topics as $topicName => $questions) {
                $topicId = DB::table('cbt_topics')->insertGetId([
                    'subject_id' => $subjectId, 'name' => $topicName,
                    'created_at' => $now, 'updated_at' => $now,
                ]);

                foreach ($questions as [$text, $options, $correct, $explanation]) {
                    DB::table('cbt_questions')->insert([
                        'subject_id' => $subjectId,
                        'topic_id' => $topicId,
                        'exam_type' => null,
                        'year' => null,
                        'question' => $text,
                        'options' => json_encode(array_combine(['A', 'B', 'C', 'D'], $options), JSON_UNESCAPED_UNICODE),
                        'correct_option' => $correct,
                        'explanation' => $explanation,
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // Content only; the tables' own migration drops everything.
    }
};

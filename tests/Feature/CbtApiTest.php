<?php

namespace Tests\Feature;

use App\Models\CbtAttempt;
use App\Models\CbtQuestion;
use App\Models\CbtSubject;
use App\Models\CbtTopic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CbtApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    /** The starter-content migration gives every subject a few questions. */
    private function subject(string $slug): CbtSubject
    {
        return CbtSubject::where('slug', $slug)->firstOrFail();
    }

    private function startJamb(array $overrides = []): \Illuminate\Testing\TestResponse
    {
        return $this->postJson('/api/cbt/attempts', array_merge([
            'exam' => 'jamb',
            'mode' => 'cbt',
            'duration_minutes' => 45,
            'subjects' => [
                ['id' => $this->subject('english')->id],
                ['id' => $this->subject('mathematics')->id],
                ['id' => $this->subject('physics')->id],
                ['id' => $this->subject('chemistry')->id],
            ],
        ], $overrides));
    }

    public function test_home_lists_slides_exams_and_an_empty_summary()
    {
        $this->getJson('/api/cbt/home')
            ->assertOk()
            ->assertJsonCount(3, 'data.slides')
            ->assertJsonPath('data.slides.0.cta_exam', 'jamb')
            ->assertJsonPath('data.exams.0.slug', 'jamb')
            ->assertJsonCount(4, 'data.exams')
            ->assertJsonPath('data.summary.tests_taken', 0)
            ->assertJsonPath('data.summary.last', null);
    }

    public function test_exam_setup_lists_subjects_with_counts_and_english_first()
    {
        $response = $this->getJson('/api/cbt/exams/jamb')->assertOk();

        $response->assertJsonPath('data.exam.max_subjects', 4)
            ->assertJsonPath('data.subjects.0.slug', 'english')
            ->assertJsonPath('data.subjects.0.compulsory', true)
            ->assertJsonCount(3, 'data.modes');

        $english = $response->json('data.subjects.0');
        $this->assertGreaterThan(0, $english['question_count']);
        $this->assertSame(count($english['topics']), $english['topic_count']);

        $this->getJson('/api/cbt/exams/nope')->assertNotFound();
    }

    public function test_setup_offers_years_from_tagged_past_questions()
    {
        $maths = $this->subject('mathematics');
        CbtQuestion::create([
            'subject_id' => $maths->id, 'exam_type' => 'jamb', 'year' => 2021,
            'question' => '2 + 2 =', 'options' => ['A' => '3', 'B' => '4'], 'correct_option' => 'B',
        ]);
        CbtQuestion::create([
            'subject_id' => $maths->id, 'exam_type' => 'waec', 'year' => 2019,
            'question' => '3 + 3 =', 'options' => ['A' => '6', 'B' => '7'], 'correct_option' => 'A',
        ]);

        $subjects = collect($this->getJson('/api/cbt/exams/jamb')->json('data.subjects'));
        // WAEC's 2019 paper isn't offered for JAMB.
        $this->assertSame([2021], $subjects->firstWhere('slug', 'mathematics')['years']);
    }

    public function test_starting_hides_answers_except_in_practice_mode()
    {
        $paper = $this->startJamb()->assertCreated()->json('data');

        $this->assertCount(4, $paper['subjects']);
        $this->assertSame('english', CbtSubject::find($paper['subjects'][0]['id'])->slug);
        $question = $paper['subjects'][0]['questions'][0];
        $this->assertArrayNotHasKey('correct_option', $question);
        $this->assertArrayNotHasKey('explanation', $question);
        $this->assertSame('A', $question['options'][0]['key']);

        $practice = $this->startJamb(['mode' => 'practice'])->json('data');
        $this->assertArrayHasKey('correct_option', $practice['subjects'][0]['questions'][0]);
    }

    public function test_starting_enforces_compulsory_subjects_and_subject_count()
    {
        // No English.
        $this->startJamb(['subjects' => [
            ['id' => $this->subject('mathematics')->id],
            ['id' => $this->subject('physics')->id],
            ['id' => $this->subject('chemistry')->id],
            ['id' => $this->subject('biology')->id],
        ]])->assertStatus(422)->assertJsonFragment(['English Language is compulsory.']);

        // JAMB needs exactly four.
        $this->startJamb(['subjects' => [['id' => $this->subject('english')->id]]])
            ->assertStatus(422);

        $this->startJamb(['duration_minutes' => 1])->assertStatus(422);
        $this->startJamb(['mode' => 'exam'])->assertStatus(422);
    }

    public function test_topic_filter_only_draws_from_chosen_topics()
    {
        $english = $this->subject('english');
        $grammar = CbtTopic::where('subject_id', $english->id)->where('name', 'Grammar')->first();

        $paper = $this->startJamb(['subjects' => [
            ['id' => $english->id, 'topic_ids' => [$grammar->id]],
            ['id' => $this->subject('mathematics')->id],
            ['id' => $this->subject('physics')->id],
            ['id' => $this->subject('chemistry')->id],
        ]])->assertCreated()->json('data');

        $topics = collect($paper['subjects'][0]['questions'])->pluck('topic')->unique()->values()->all();
        $this->assertSame(['Grammar'], $topics);

        // A year nobody has questions for.
        $this->startJamb(['subjects' => [
            ['id' => $english->id, 'years' => [1999]],
            ['id' => $this->subject('mathematics')->id],
            ['id' => $this->subject('physics')->id],
            ['id' => $this->subject('chemistry')->id],
        ]])->assertStatus(422);
    }

    public function test_submit_grades_and_returns_the_result_slip()
    {
        $paper = $this->startJamb()->json('data');
        $english = $paper['subjects'][0]['questions'];

        $answers = [];
        foreach ($english as $i => $q) {
            $correct = CbtQuestion::find($q['id'])->correct_option;
            // First question right, second wrong, the rest skipped.
            if ($i === 0) {
                $answers[$q['id']] = $correct;
            } elseif ($i === 1) {
                $answers[$q['id']] = $correct === 'A' ? 'B' : 'A';
            }
        }

        $result = $this->postJson("/api/cbt/attempts/{$paper['id']}/submit", [
            'answers' => $answers,
            'time_spent_seconds' => 74,
        ])->assertOk()->json('data');

        $total = $paper['total_questions'];
        $this->assertSame(1, $result['correct']);
        $this->assertSame(1, $result['wrong']);
        $this->assertSame($total - 2, $result['skipped']);
        $this->assertSame(400, $result['max_score']);
        $this->assertSame((int) round(1 / count($english) * 100), $result['score']);
        $this->assertSame('needs_work', $result['verdict']['key']);
        $this->assertStringStartsWith('CBT', $result['candidate']['reg_no']);
        $this->assertCount(4, $result['subjects']);
        $this->assertSame('correct', $result['subjects'][0]['questions'][0]['status']);
        $this->assertSame('wrong', $result['subjects'][0]['questions'][1]['status']);
        $this->assertNotEmpty($result['subjects'][0]['questions'][0]['explanation']);
        $this->assertNotEmpty($result['diagnosis']);
        // The server only just started the clock, so 74 s is capped.
        $this->assertLessThanOrEqual(10, $result['time_spent_seconds']);

        // Submitting again returns the same result instead of regrading.
        $again = $this->postJson("/api/cbt/attempts/{$paper['id']}/submit", ['answers' => []])->json('data');
        $this->assertSame(1, $again['correct']);
    }

    public function test_history_summary_and_privacy()
    {
        $paper = $this->startJamb()->json('data');
        $this->postJson("/api/cbt/attempts/{$paper['id']}/submit", ['answers' => []])->assertOk();

        $this->getJson('/api/cbt/attempts')
            ->assertOk()
            ->assertJsonCount(1, 'data.attempts')
            ->assertJsonPath('data.summary.tests_taken', 1)
            ->assertJsonPath('data.summary.last.id', $paper['id']);

        $this->getJson("/api/cbt/attempts/{$paper['id']}")->assertOk();

        // Another user can't see or submit it.
        Sanctum::actingAs(User::factory()->create());
        $this->getJson("/api/cbt/attempts/{$paper['id']}")->assertNotFound();
        $this->postJson("/api/cbt/attempts/{$paper['id']}/submit", ['answers' => []])->assertNotFound();
        $this->assertSame(1, CbtAttempt::count());
    }

    public function test_requires_sign_in()
    {
        $this->app['auth']->forgetGuards();
        $this->getJson('/api/cbt/home')->assertUnauthorized();
    }
}

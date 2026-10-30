<?php

namespace Tests\Feature;

use App\Models\CbtQuestion;
use App\Models\CbtSlide;
use App\Models\CbtSubject;
use App\Models\CbtTopic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CbtAdminTest extends TestCase
{
    use RefreshDatabase;

    private function asAdmin(): static
    {
        return $this->withSession([
            'admin_logged_in' => true,
            'admin_user' => ['email' => 'admin@example.com'],
        ]);
    }

    public function test_pages_require_an_admin_and_render()
    {
        $this->get('/admin/cbt/questions')->assertRedirect(route('admin.login'));

        $this->asAdmin()->get('/admin/cbt/questions')->assertOk()->assertSee('CBT Questions');
        $this->asAdmin()->get('/admin/cbt/questions/create')->assertOk();
        $this->asAdmin()->get('/admin/cbt/questions/import')->assertOk();
        $this->asAdmin()->get('/admin/cbt/questions/template')->assertOk();
        $this->asAdmin()->get('/admin/cbt/slides')->assertOk()->assertSee('Start Your Practice');
        $this->asAdmin()->get('/admin/cbt/slides/create')->assertOk();
        $this->asAdmin()->get('/admin/cbt/questions/' . CbtQuestion::first()->id . '/edit')->assertOk();
    }

    public function test_adding_a_question_creates_its_subject_and_topic()
    {
        $this->asAdmin()->post('/admin/cbt/questions', [
            'subject' => 'Agricultural Science',
            'topic' => 'Soil Science',
            'exam_type' => 'waec',
            'year' => 2020,
            'question' => 'Which soil holds the most water?',
            'options' => ['A' => 'Sandy', 'B' => 'Clay', 'C' => 'Loamy', 'D' => '', 'E' => ''],
            'correct_option' => 'B',
            'is_active' => '1',
        ])->assertRedirect(route('admin.cbt.questions.index'));

        $question = CbtQuestion::latest('id')->first();
        $this->assertSame('agricultural-science', $question->subject->slug);
        $this->assertSame('Soil Science', $question->topic->name);
        $this->assertSame(['A' => 'Sandy', 'B' => 'Clay', 'C' => 'Loamy'], $question->options);

        // The correct option must be one that was filled in.
        $this->asAdmin()->post('/admin/cbt/questions', [
            'subject' => 'Mathematics', 'question' => 'x',
            'options' => ['A' => '1', 'B' => '2'], 'correct_option' => 'D',
        ])->assertSessionHasErrors('correct_option');
    }

    public function test_csv_import_saves_good_rows_and_reports_bad_ones()
    {
        $csv = implode("\n", [
            "\xEF\xBB\xBFsubject,topic,exam_type,year,question,option_a,option_b,option_c,option_d,option_e,answer,explanation",
            'English Language,Grammar,jamb,2021,"She ___ to school.",go,goes,going,gone,,B,Singular subject.',
            'Maths,Algebra,,,"2x = 10, x = ?",2,5,,,,b,',
            'Physics,,neco,1800,"Bad year",a,b,,,,A,',
            ',,,,"No subject",a,b,,,,A,',
            'Chemistry,,,,"Bad answer",a,b,,,,C,',
        ]);
        $before = CbtQuestion::count();

        $this->asAdmin()->post('/admin/cbt/questions/import', [
            'file' => UploadedFile::fake()->createWithContent('q.csv', $csv),
        ])->assertRedirect(route('admin.cbt.questions.import'))
            ->assertSessionHas('import_errors', fn ($errors) => count($errors) === 3 && str_contains($errors[0], 'Row 4'));

        $this->assertSame($before + 2, CbtQuestion::count());
        // "English Language" and "Maths" map onto the existing subjects.
        $this->assertSame(1, CbtSubject::where('slug', 'english')->count());
        $maths = CbtQuestion::latest('id')->first();
        $this->assertSame('mathematics', $maths->subject->slug);
        $this->assertSame('B', $maths->correct_option);
        $this->assertTrue(CbtTopic::where('name', 'Algebra')->exists());
        $this->assertSame(2021, CbtQuestion::where('year', 2021)->first()->year);
    }

    public function test_slides_can_be_added_and_target_an_exam()
    {
        $this->asAdmin()->post('/admin/cbt/slides', [
            'title' => 'NECO Week', 'badge' => 'NEW', 'cta_kind' => 'exam', 'cta_exam' => 'neco',
            'color_from' => '#112233', 'color_to' => '#445566', 'is_active' => '1',
        ])->assertRedirect(route('admin.cbt.slides.index'));

        $slide = CbtSlide::where('title', 'NECO Week')->first();
        $this->assertSame('neco', $slide->toAppArray()['cta_exam']);
        $this->assertSame('Begin Now', $slide->cta_label);

        $this->asAdmin()->post("/admin/cbt/slides/{$slide->id}/toggle-active");
        $this->assertFalse($slide->fresh()->is_active);
    }
}

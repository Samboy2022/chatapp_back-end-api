<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CbtQuestion;
use App\Models\CbtSubject;
use App\Models\CbtTopic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Admin for the CBT question bank: browse, add, edit, delete, and bulk-import
 * past questions from CSV. Subjects and topics are created on the fly from
 * the names typed (or imported), so there's no separate screen for them.
 */
class CbtQuestionController extends Controller
{
    /** CSV columns, in the order of the downloadable template. */
    public const CSV_COLUMNS = [
        'subject', 'topic', 'exam_type', 'year', 'question',
        'option_a', 'option_b', 'option_c', 'option_d', 'option_e',
        'answer', 'explanation',
    ];

    public function index(Request $request): View
    {
        $filters = $request->only(['subject_id', 'topic_id', 'exam_type', 'year', 'q']);

        $questions = CbtQuestion::with(['subject', 'topic'])
            ->when($filters['subject_id'] ?? null, fn ($q, $v) => $q->where('subject_id', $v))
            ->when($filters['topic_id'] ?? null, fn ($q, $v) => $q->where('topic_id', $v))
            ->when($filters['exam_type'] ?? null, fn ($q, $v) => $v === 'any' ? $q->whereNull('exam_type') : $q->where('exam_type', $v))
            ->when($filters['year'] ?? null, fn ($q, $v) => $q->where('year', $v))
            ->when($filters['q'] ?? null, fn ($q, $v) => $q->where('question', 'like', '%' . $v . '%'))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.cbt.questions.index', [
            'questions' => $questions,
            'filters' => $filters,
            'subjects' => CbtSubject::ordered()->withCount('questions')->get(),
            'topics' => CbtTopic::when($filters['subject_id'] ?? null, fn ($q, $v) => $q->where('subject_id', $v))->orderBy('name')->get(),
            'years' => CbtQuestion::whereNotNull('year')->distinct()->orderByDesc('year')->pluck('year'),
            'stats' => [
                'questions' => CbtQuestion::count(),
                'subjects' => CbtSubject::count(),
                'topics' => CbtTopic::count(),
                'attempts' => DB::table('cbt_attempts')->where('status', 'submitted')->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.cbt.questions.create', $this->formData(new CbtQuestion(['is_active' => true])));
    }

    public function store(Request $request): RedirectResponse
    {
        CbtQuestion::create($this->validated($request));

        return redirect()
            ->route($request->boolean('add_another') ? 'admin.cbt.questions.create' : 'admin.cbt.questions.index')
            ->with('success', 'Question added.');
    }

    public function edit(CbtQuestion $question): View
    {
        return view('admin.cbt.questions.edit', $this->formData($question->load(['subject', 'topic'])));
    }

    public function update(Request $request, CbtQuestion $question): RedirectResponse
    {
        $question->update($this->validated($request));

        return redirect()->route('admin.cbt.questions.index')->with('success', 'Question updated.');
    }

    public function destroy(CbtQuestion $question): RedirectResponse
    {
        // Past results keep their score; the result sheet notes the question is gone.
        $question->delete();

        return back()->with('success', 'Question deleted.');
    }

    // ─── CSV import ──────────────────────────────────────────────────────────

    public function importForm(): View
    {
        return view('admin.cbt.questions.import', ['columns' => self::CSV_COLUMNS]);
    }

    public function template(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, self::CSV_COLUMNS);
            fputcsv($out, ['English Language', 'Grammar', 'jamb', '2021', 'Choose the correct option: She ___ to school every day.', 'go', 'goes', 'going', 'gone', '', 'B', 'A singular subject takes "goes" in the simple present.']);
            fputcsv($out, ['Mathematics', 'Algebra', '', '', 'Solve: 2x = 10', '2', '5', '10', '20', '', 'B', 'Divide both sides by 2.']);
            fclose($out);
        }, 'cbt-questions-template.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * Import a CSV. Good rows are saved; bad ones are skipped and listed by
     * row number so they can be fixed and re-imported.
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:5120']]);

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        $header = fgetcsv($handle);
        if (!$header) {
            return back()->with('error', 'The file is empty.');
        }
        // Strip a UTF-8 byte-order mark Excel adds, and normalise case.
        $header = array_map(fn ($h) => strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $h))), $header);
        $missing = array_diff(['subject', 'question', 'option_a', 'option_b', 'answer'], $header);
        if ($missing) {
            return back()->with('error', 'Missing column(s): ' . implode(', ', $missing) . '. Download the template for the expected layout.');
        }

        $imported = 0;
        $errors = [];
        $line = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $line++;
            if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue; // blank line
            }
            $data = array_combine($header, array_pad(array_slice($row, 0, count($header)), count($header), ''));
            $data = array_map(fn ($v) => trim((string) $v), $data);

            $options = [];
            foreach (['a', 'b', 'c', 'd', 'e'] as $letter) {
                if (($data["option_$letter"] ?? '') !== '') {
                    $options[strtoupper($letter)] = $data["option_$letter"];
                }
            }
            $answer = strtoupper($data['answer'] ?? '');
            $exam = strtolower($data['exam_type'] ?? '');
            $year = $data['year'] ?? '';

            $problem = match (true) {
                $data['subject'] === '' => 'subject is empty',
                $data['question'] === '' => 'question is empty',
                count($options) < 2 => 'needs at least option_a and option_b',
                !array_key_exists($answer, $options) => "answer \"{$data['answer']}\" isn't one of the given options",
                $exam !== '' && !in_array($exam, CbtQuestion::EXAM_TYPES, true) => "exam_type must be jamb, waec, neco or empty",
                $year !== '' && (!ctype_digit($year) || $year < 1970 || $year > 2100) => 'year must be like 2021',
                default => null,
            };
            if ($problem) {
                $errors[] = "Row $line: $problem.";
                continue;
            }

            $subject = CbtSubject::findOrCreateByName($data['subject']);
            $topicId = ($data['topic'] ?? '') !== ''
                ? CbtTopic::firstOrCreate(['subject_id' => $subject->id, 'name' => $data['topic']])->id
                : null;

            CbtQuestion::create([
                'subject_id' => $subject->id,
                'topic_id' => $topicId,
                'exam_type' => $exam ?: null,
                'year' => $year !== '' ? (int) $year : null,
                'question' => $data['question'],
                'options' => $options,
                'correct_option' => $answer,
                'explanation' => ($data['explanation'] ?? '') ?: null,
                'is_active' => true,
            ]);
            $imported++;
        }
        fclose($handle);

        $message = "Imported $imported question" . ($imported === 1 ? '' : 's') . '.';

        return redirect()
            ->route($errors ? 'admin.cbt.questions.import' : 'admin.cbt.questions.index')
            ->with($imported ? 'success' : 'error', $errors ? $message . ' ' . count($errors) . ' row(s) skipped.' : $message)
            ->with('import_errors', array_slice($errors, 0, 100));
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function formData(CbtQuestion $question): array
    {
        return [
            'question' => $question,
            'subjects' => CbtSubject::ordered()->get(),
            'topicsBySubject' => CbtTopic::orderBy('name')->get()->groupBy('subject_id')->map->pluck('name'),
            'examTypes' => CbtQuestion::EXAM_TYPES,
        ];
    }

    /** Validate the form and resolve subject/topic names to ids. */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:100'],
            'topic' => ['nullable', 'string', 'max:150'],
            'exam_type' => ['nullable', Rule::in(CbtQuestion::EXAM_TYPES)],
            'year' => ['nullable', 'integer', 'min:1970', 'max:2100'],
            'question' => ['required', 'string', 'max:5000'],
            'options' => ['required', 'array'],
            'options.*' => ['nullable', 'string', 'max:1000'],
            'correct_option' => ['required', Rule::in(['A', 'B', 'C', 'D', 'E'])],
            'explanation' => ['nullable', 'string', 'max:5000'],
        ]);

        $options = collect($data['options'])
            ->only(['A', 'B', 'C', 'D', 'E'])
            ->map(fn ($v) => trim((string) $v))
            ->filter(fn ($v) => $v !== '')
            ->all();
        ksort($options);

        if (count($options) < 2) {
            throw \Illuminate\Validation\ValidationException::withMessages(['options' => 'Give at least options A and B.']);
        }
        if (!array_key_exists($data['correct_option'], $options)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['correct_option' => 'The correct option must be one you filled in.']);
        }

        $subject = CbtSubject::findOrCreateByName($data['subject']);
        $topic = filled($data['topic'] ?? null)
            ? CbtTopic::firstOrCreate(['subject_id' => $subject->id, 'name' => trim($data['topic'])])
            : null;

        return [
            'subject_id' => $subject->id,
            'topic_id' => $topic?->id,
            'exam_type' => $data['exam_type'] ?? null,
            'year' => $data['year'] ?? null,
            'question' => $data['question'],
            'options' => $options,
            'correct_option' => $data['correct_option'],
            'explanation' => $data['explanation'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ];
    }
}

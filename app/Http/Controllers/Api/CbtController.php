<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CbtAttempt;
use App\Models\CbtSlide;
use App\Services\Cbt\CbtService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * CBT practice for the app: the CBT home screen, exam setup, sitting an exam
 * and the results.
 *
 *   GET  /cbt/home                  slides, exams, study overview
 *   GET  /cbt/exams/{exam}          setup options for one exam
 *   POST /cbt/attempts              start a sitting → the exam paper
 *   POST /cbt/attempts/{id}/submit  grade it → the result
 *   GET  /cbt/attempts              past results
 *   GET  /cbt/attempts/{id}         one result (slip + answer sheet)
 */
class CbtController extends Controller
{
    public function __construct(private CbtService $cbt)
    {
    }

    public function home(Request $request): JsonResponse
    {
        return $this->ok([
            'slides' => CbtSlide::active()->ordered()->limit(5)->get()->map->toAppArray()->values(),
            'exams' => collect($this->cbt->exams())->keys()->map(fn ($slug) => $this->cbt->examSummary($slug))->values(),
            'summary' => $this->cbt->summary($request->user()),
        ]);
    }

    public function exam(string $exam): JsonResponse
    {
        if (!$this->cbt->exam($exam)) {
            return $this->notFound('That exam isn\'t available.');
        }

        return $this->ok($this->cbt->setup($exam));
    }

    public function start(Request $request): JsonResponse
    {
        $data = $request->validate([
            'exam' => ['required', 'string', Rule::in(array_keys($this->cbt->exams()))],
            'mode' => ['required', 'string', Rule::in(array_keys(config('cbt.modes')))],
            'duration_minutes' => ['required', 'integer', 'min:' . config('cbt.min_duration'), 'max:' . config('cbt.max_duration')],
            'subjects' => ['required', 'array', 'min:1', 'max:10'],
            'subjects.*.id' => ['required', 'integer', 'distinct'],
            'subjects.*.years' => ['sometimes', 'array'],
            'subjects.*.years.*' => ['integer', 'min:1970', 'max:2100'],
            'subjects.*.topic_ids' => ['sometimes', 'array'],
            'subjects.*.topic_ids.*' => ['integer'],
        ]);

        $attempt = $this->cbt->start($request->user(), $data);

        return $this->ok($this->cbt->paper($attempt), 201);
    }

    public function submit(Request $request, int $attempt): JsonResponse
    {
        $data = $request->validate([
            'answers' => ['present', 'array'],
            'answers.*' => ['nullable', 'string', 'size:1'],
            'time_spent_seconds' => ['nullable', 'integer', 'min:0'],
        ]);

        $sitting = $this->mine($request, $attempt);
        if (!$sitting) {
            return $this->notFound('Exam not found.');
        }

        $sitting = $this->cbt->submit($sitting, $data['answers'], $data['time_spent_seconds'] ?? null);

        return $this->ok($this->cbt->result($sitting));
    }

    public function index(Request $request): JsonResponse
    {
        $attempts = CbtAttempt::where('user_id', $request->user()->id)
            ->where('status', 'submitted')
            ->latest('submitted_at')
            ->paginate(20);

        return $this->ok([
            'attempts' => collect($attempts->items())->map(fn ($a) => $this->cbt->historyItem($a))->values(),
            'summary' => $this->cbt->summary($request->user()),
            'has_more' => $attempts->hasMorePages(),
        ]);
    }

    public function show(Request $request, int $attempt): JsonResponse
    {
        $sitting = $this->mine($request, $attempt);
        if (!$sitting || !$sitting->isSubmitted()) {
            return $this->notFound('Result not found.');
        }

        return $this->ok($this->cbt->result($sitting));
    }

    /** Someone else's attempt is a 404, not a 403: don't confirm it exists. */
    private function mine(Request $request, int $id): ?CbtAttempt
    {
        return CbtAttempt::where('id', $id)->where('user_id', $request->user()->id)->first();
    }

    private function ok(mixed $data, int $status = 200): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $data], $status);
    }

    private function notFound(string $message): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message], 404);
    }
}

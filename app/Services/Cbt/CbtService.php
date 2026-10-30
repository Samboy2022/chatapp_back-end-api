<?php

namespace App\Services\Cbt;

use App\Models\CbtAttempt;
use App\Models\CbtAttemptAnswer;
use App\Models\CbtQuestion;
use App\Models\CbtSubject;
use App\Models\CbtTopic;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * The rules of CBT practice: what an exam can be set up with, which questions
 * a sitting gets, how it's graded, and what the result slip says.
 */
class CbtService
{
    // ─── Exams ───────────────────────────────────────────────────────────────

    /** @return array<string, array> slug => config */
    public function exams(): array
    {
        return config('cbt.exams');
    }

    public function exam(string $slug): ?array
    {
        return $this->exams()[$slug] ?? null;
    }

    public function examSummary(string $slug): array
    {
        $exam = $this->exam($slug);

        return [
            'slug' => $slug,
            'title' => $exam['title'],
            'badge' => $exam['badge'],
            'icon' => $exam['icon'],
            'description' => $exam['description'],
        ];
    }

    /**
     * Everything the setup screen offers for one exam: modes, durations,
     * instructions, and each subject with its question count, years and topics.
     */
    public function setup(string $slug): array
    {
        $exam = $this->exam($slug);

        // One grouped query instead of a count per subject/topic/year.
        $counts = CbtQuestion::query()
            ->active()
            ->forExam($slug)
            ->select('subject_id', 'topic_id', 'year', DB::raw('COUNT(*) as total'))
            ->groupBy('subject_id', 'topic_id', 'year')
            ->get();

        $topicNames = CbtTopic::whereIn('id', $counts->pluck('topic_id')->filter()->unique())
            ->pluck('name', 'id');

        $subjects = CbtSubject::active()->ordered()->get()
            ->map(function (CbtSubject $subject) use ($counts, $topicNames, $exam) {
                $rows = $counts->where('subject_id', $subject->id);
                $compulsory = in_array($subject->slug, $exam['compulsory'], true);

                $topics = $rows->whereNotNull('topic_id')
                    ->groupBy('topic_id')
                    ->map(fn (Collection $group, $topicId) => [
                        'id' => (int) $topicId,
                        'name' => $topicNames[$topicId] ?? 'Topic',
                        'question_count' => (int) $group->sum('total'),
                    ])
                    ->sortBy('name')
                    ->values();

                return [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'slug' => $subject->slug,
                    'compulsory' => $compulsory,
                    'question_count' => (int) $rows->sum('total'),
                    'topic_count' => $topics->count(),
                    'years' => $rows->pluck('year')->filter()->unique()->sortDesc()->values(),
                    'topics' => $topics,
                ];
            })
            // A subject with no questions can't be sat, unless it's compulsory
            // (then the app shows it as unavailable rather than hiding it).
            ->filter(fn (array $s) => $s['question_count'] > 0 || $s['compulsory'])
            ->sortByDesc('compulsory')
            ->values();

        $available = $subjects->where('question_count', '>', 0)->count();

        return [
            'exam' => $this->examSummary($slug) + [
                'questions_per_subject' => $exam['questions_per_subject'],
                'marks_per_subject' => $exam['marks_per_subject'],
                'min_subjects' => min($exam['min_subjects'], max(1, $available)),
                'max_subjects' => $exam['max_subjects'],
            ],
            'modes' => collect(config('cbt.modes'))
                ->map(fn (array $mode, string $key) => ['key' => $key] + $mode)
                ->values(),
            'durations' => config('cbt.durations'),
            'default_duration' => $exam['default_duration'],
            'min_duration' => config('cbt.min_duration'),
            'max_duration' => config('cbt.max_duration'),
            'instructions' => config('cbt.instructions'),
            'subjects' => $subjects,
        ];
    }

    // ─── Sitting an exam ─────────────────────────────────────────────────────

    /**
     * Start a sitting: check the choices against the exam's rules, draw the
     * questions, and record the attempt.
     *
     * @param  array{exam: string, mode: string, duration_minutes: int, subjects: array}  $data
     */
    public function start(User $user, array $data): CbtAttempt
    {
        $slug = $data['exam'];
        $exam = $this->exam($slug);
        $setup = $this->setup($slug);

        $chosen = collect($data['subjects'])->keyBy(fn ($s) => (int) $s['id']);
        $offered = collect($setup['subjects'])->keyBy('id');

        $unknown = $chosen->keys()->diff($offered->keys());
        if ($unknown->isNotEmpty()) {
            throw ValidationException::withMessages(['subjects' => 'One of the chosen subjects isn\'t offered for this exam.']);
        }

        foreach ($offered->where('compulsory', true) as $subject) {
            if (!$chosen->has($subject['id'])) {
                throw ValidationException::withMessages(['subjects' => "{$subject['name']} is compulsory."]);
            }
        }

        $min = $setup['exam']['min_subjects'];
        $max = $setup['exam']['max_subjects'];
        if ($chosen->count() < $min || $chosen->count() > $max) {
            $rule = $min === $max ? "exactly $min" : "between $min and $max";
            throw ValidationException::withMessages(['subjects' => "Choose $rule subjects for {$exam['title']}."]);
        }

        $plan = [];
        foreach ($chosen as $subjectId => $choice) {
            $subject = $offered[$subjectId];
            $years = array_values(array_unique(array_map('intval', $choice['years'] ?? [])));
            $topicIds = array_values(array_unique(array_map('intval', $choice['topic_ids'] ?? [])));

            $foreignTopic = collect($topicIds)->diff(collect($subject['topics'])->pluck('id'));
            if ($foreignTopic->isNotEmpty()) {
                throw ValidationException::withMessages(['subjects' => "A chosen topic doesn't belong to {$subject['name']}."]);
            }

            $questionIds = CbtQuestion::query()
                ->active()
                ->forExam($slug)
                ->where('subject_id', $subjectId)
                ->when($years, fn ($q) => $q->whereIn('year', $years))
                ->when($topicIds, fn ($q) => $q->whereIn('topic_id', $topicIds))
                ->inRandomOrder()
                ->limit($exam['questions_per_subject'])
                ->pluck('id')
                ->all();

            if (empty($questionIds)) {
                throw ValidationException::withMessages([
                    'subjects' => "No {$subject['name']} questions match your chosen years and topics.",
                ]);
            }

            $plan[] = [
                'subject_id' => (int) $subjectId,
                'years' => $years,
                'topic_ids' => $topicIds,
                'question_ids' => $questionIds,
            ];
        }

        // Compulsory subjects first, then in the order chosen.
        usort($plan, fn ($a, $b) => (int) $offered[$b['subject_id']]['compulsory'] <=> (int) $offered[$a['subject_id']]['compulsory']);

        return CbtAttempt::create([
            'user_id' => $user->id,
            'exam_type' => $slug,
            'mode' => $data['mode'],
            'duration_minutes' => (int) $data['duration_minutes'],
            'status' => 'in_progress',
            'setup' => $plan,
            'started_at' => now(),
            'total_questions' => collect($plan)->sum(fn ($s) => count($s['question_ids'])),
            'max_score' => count($plan) * $exam['marks_per_subject'],
        ]);
    }

    /**
     * The exam paper for a sitting. Answers are only included in Practice
     * mode, where the app reveals them as the candidate goes.
     */
    public function paper(CbtAttempt $attempt): array
    {
        $withAnswers = $attempt->mode === 'practice';
        $questions = $this->questionsFor($attempt);
        $subjects = CbtSubject::whereIn('id', collect($attempt->setup)->pluck('subject_id'))->pluck('name', 'id');

        return [
            'id' => $attempt->id,
            'exam' => $this->examSummary($attempt->exam_type),
            'mode' => $attempt->mode,
            'mode_title' => $attempt->modeTitle(),
            'duration_minutes' => $attempt->duration_minutes,
            'started_at' => $attempt->started_at->toIso8601String(),
            'ends_at' => $attempt->started_at->copy()->addMinutes($attempt->duration_minutes)->toIso8601String(),
            'total_questions' => $attempt->total_questions,
            'subjects' => collect($attempt->setup)->map(fn (array $part) => [
                'id' => $part['subject_id'],
                'name' => $subjects[$part['subject_id']] ?? 'Subject',
                'questions' => collect($part['question_ids'])
                    ->map(fn ($id) => $questions[$id] ?? null)
                    ->filter()
                    ->map(fn (CbtQuestion $q) => [
                        'id' => $q->id,
                        'text' => $q->question,
                        'options' => $q->optionList(),
                        'topic' => $q->topic?->name,
                        'year' => $q->year,
                    ] + ($withAnswers ? [
                        'correct_option' => $q->correct_option,
                        'explanation' => $q->explanation,
                    ] : []))
                    ->values(),
            ])->values(),
        ];
    }

    /**
     * Grade a sitting. Submitting twice returns the first result unchanged,
     * so a retried request can't regrade with different answers.
     *
     * @param  array<int|string, string|null>  $answers  question id => chosen letter
     */
    public function submit(CbtAttempt $attempt, array $answers, ?int $clientSeconds): CbtAttempt
    {
        if ($attempt->isSubmitted()) {
            return $attempt;
        }

        return DB::transaction(function () use ($attempt, $answers, $clientSeconds) {
            $attempt = CbtAttempt::lockForUpdate()->find($attempt->id);
            if ($attempt->isSubmitted()) {
                return $attempt;
            }

            $questions = $this->questionsFor($attempt);
            $exam = $this->exam($attempt->exam_type);
            $rows = [];
            $correct = $wrong = $skipped = 0;
            $score = 0;
            $position = 0;

            foreach ($attempt->setup as $part) {
                $subjectCorrect = 0;
                $subjectTotal = 0;
                foreach ($part['question_ids'] as $questionId) {
                    $question = $questions[$questionId] ?? null;
                    if (!$question) {
                        continue; // deleted since the exam started: not counted
                    }
                    $subjectTotal++;
                    $chosen = $answers[$questionId] ?? $answers[(string) $questionId] ?? null;
                    $chosen = is_string($chosen) ? strtoupper(trim($chosen)) : null;
                    if ($chosen !== null && !array_key_exists($chosen, $question->options ?? [])) {
                        $chosen = null;
                    }
                    $isCorrect = $chosen !== null && $chosen === $question->correct_option;

                    if ($chosen === null) {
                        $skipped++;
                    } elseif ($isCorrect) {
                        $correct++;
                        $subjectCorrect++;
                    } else {
                        $wrong++;
                    }

                    $rows[] = [
                        'attempt_id' => $attempt->id,
                        'question_id' => $question->id,
                        'subject_id' => $question->subject_id,
                        'topic_id' => $question->topic_id,
                        'position' => ++$position,
                        'selected_option' => $chosen,
                        'correct_option' => $question->correct_option,
                        'is_correct' => $isCorrect,
                    ];
                }
                if ($subjectTotal > 0) {
                    $score += (int) round($subjectCorrect / $subjectTotal * $exam['marks_per_subject']);
                }
            }

            CbtAttemptAnswer::insert($rows);

            // Trust the app's clock only within what the server saw.
            $elapsed = max(0, (int) $attempt->started_at->diffInSeconds(now()));
            $limit = $attempt->duration_minutes * 60;
            $seconds = min($clientSeconds ?? $elapsed, $elapsed + 5, $limit);

            $attempt->update([
                'status' => 'submitted',
                'submitted_at' => now(),
                'time_spent_seconds' => max(0, $seconds),
                'total_questions' => count($rows),
                'correct' => $correct,
                'wrong' => $wrong,
                'skipped' => $skipped,
                'score' => $score,
            ]);

            return $attempt;
        });
    }

    // ─── Results ─────────────────────────────────────────────────────────────

    /** The result slip and the full marked answer sheet. */
    public function result(CbtAttempt $attempt): array
    {
        $attempt->loadMissing(['user', 'answers.question', 'answers.topic', 'answers.subject']);
        $exam = $this->exam($attempt->exam_type);
        $percent = $attempt->percent();

        $subjects = $attempt->answers->groupBy('subject_id')->map(function (Collection $answers) use ($exam) {
            $total = $answers->count();
            $right = $answers->where('is_correct', true)->count();
            $pct = $total ? round($right / $total * 100, 1) : 0.0;

            // The topic that cost the most marks, if any was missed.
            $weakTopic = $answers->whereNotNull('topic_id')
                ->groupBy('topic_id')
                ->map(fn (Collection $g) => [
                    'name' => $g->first()->topic?->name,
                    'missed' => $g->where('is_correct', false)->count(),
                    'rate' => $g->where('is_correct', true)->count() / $g->count(),
                ])
                ->filter(fn ($t) => $t['missed'] > 0 && $t['name'])
                ->sortBy([['rate', 'asc'], ['missed', 'desc']])
                ->first();

            $number = 0;

            return [
                'id' => $answers->first()->subject_id,
                'name' => $answers->first()->subject?->name ?? 'Subject',
                'score' => (int) round($right / max(1, $total) * $exam['marks_per_subject']),
                'max_score' => $exam['marks_per_subject'],
                'correct' => $right,
                'wrong' => $answers->where('is_correct', false)->whereNotNull('selected_option')->count(),
                'skipped' => $answers->whereNull('selected_option')->count(),
                'total' => $total,
                'percent' => $pct,
                'rating' => $this->rating($pct),
                'weak_topic' => $weakTopic['name'] ?? null,
                'questions' => $answers->map(function (CbtAttemptAnswer $a) use (&$number) {
                    $q = $a->question;

                    return [
                        'number' => ++$number,
                        'id' => $a->question_id,
                        'text' => $q?->question ?? 'This question has since been removed.',
                        'options' => $q?->optionList() ?? [],
                        'correct_option' => $a->correct_option,
                        'selected_option' => $a->selected_option,
                        'status' => $a->selected_option === null ? 'skipped' : ($a->is_correct ? 'correct' : 'wrong'),
                        'explanation' => $q?->explanation,
                        'topic' => $a->topic?->name,
                        'year' => $q?->year,
                    ];
                })->values(),
            ];
        })->values();

        return [
            'id' => $attempt->id,
            'exam' => $this->examSummary($attempt->exam_type),
            'mode' => $attempt->mode,
            'mode_title' => $attempt->modeTitle(),
            'candidate' => [
                'name' => $attempt->user?->name ?? 'Candidate',
                'avatar' => $attempt->user?->avatar_url,
                'reg_no' => $this->regNo($attempt->user),
            ],
            'duration_minutes' => $attempt->duration_minutes,
            'submitted_at' => $attempt->submitted_at?->toIso8601String(),
            'time_spent_seconds' => (int) $attempt->time_spent_seconds,
            'score' => $attempt->score,
            'max_score' => $attempt->max_score,
            'percent' => $percent,
            'accuracy' => $attempt->total_questions ? round($attempt->correct / $attempt->total_questions * 100, 1) : 0.0,
            'verdict' => $this->verdict($percent),
            'correct' => $attempt->correct,
            'wrong' => $attempt->wrong,
            'skipped' => $attempt->skipped,
            'total_questions' => $attempt->total_questions,
            'subjects' => $subjects,
            'diagnosis' => $this->diagnosis($attempt, $subjects),
        ];
    }

    /** History list and the CBT home screen's study overview. */
    public function summary(User $user): array
    {
        $done = CbtAttempt::where('user_id', $user->id)->where('status', 'submitted');
        $last = (clone $done)->latest('submitted_at')->first();

        return [
            'tests_taken' => (clone $done)->count(),
            'practice_seconds' => (int) (clone $done)->sum('time_spent_seconds'),
            'average_percent' => round((float) (clone $done)->get()->avg(fn (CbtAttempt $a) => $a->percent()), 1),
            'last' => $last ? $this->historyItem($last) : null,
        ];
    }

    public function historyItem(CbtAttempt $attempt): array
    {
        return [
            'id' => $attempt->id,
            'exam' => $this->examSummary($attempt->exam_type),
            'mode' => $attempt->mode,
            'mode_title' => $attempt->modeTitle(),
            'score' => $attempt->score,
            'max_score' => $attempt->max_score,
            'percent' => $attempt->percent(),
            'verdict' => $this->verdict($attempt->percent()),
            'correct' => $attempt->correct,
            'total_questions' => $attempt->total_questions,
            'time_spent_seconds' => (int) $attempt->time_spent_seconds,
            'submitted_at' => $attempt->submitted_at?->toIso8601String(),
        ];
    }

    public function regNo(?User $user): string
    {
        if (!$user) {
            return 'CBT0000000000';
        }

        return 'CBT' . ($user->created_at?->format('Y') ?? date('Y')) . str_pad((string) $user->id, 6, '0', STR_PAD_LEFT);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /** @return Collection<int, CbtQuestion> keyed by id */
    private function questionsFor(CbtAttempt $attempt): Collection
    {
        $ids = collect($attempt->setup)->pluck('question_ids')->flatten()->all();

        return CbtQuestion::with('topic')->whereIn('id', $ids)->get()->keyBy('id');
    }

    /** @return array{key: string, label: string} */
    private function verdict(float $percent): array
    {
        return match (true) {
            $percent >= 70 => ['key' => 'excellent', 'label' => 'Excellent'],
            $percent >= 50 => ['key' => 'good', 'label' => 'Good'],
            $percent >= 40 => ['key' => 'fair', 'label' => 'Fair'],
            default => ['key' => 'needs_work', 'label' => 'Needs work'],
        };
    }

    private function rating(float $percent): string
    {
        return match (true) {
            $percent >= 70 => 'Strong',
            $percent >= 50 => 'Good',
            $percent >= 40 => 'Fair',
            default => 'Requires review',
        };
    }

    /**
     * Plain-language advice from the numbers: where the marks are, which
     * topics to revisit, and how time and skipped questions were handled.
     */
    private function diagnosis(CbtAttempt $attempt, Collection $subjects): array
    {
        $notes = [];
        if ($subjects->isEmpty()) {
            return ['No questions were graded in this sitting.'];
        }

        $best = $subjects->sortByDesc('percent')->first();
        if ($best['percent'] >= 50) {
            $notes[] = "Strongest subject: {$best['name']} ({$best['percent']}%). Keep it sharp with timed practice.";
        }

        $weak = $subjects->filter(fn ($s) => $s['percent'] < 50)->sortBy('percent');
        if ($weak->isNotEmpty()) {
            $list = $weak->map(fn ($s) => $s['name'] . ($s['weak_topic'] ? " (start with {$s['weak_topic']})" : ''))->join(', ', ' and ');
            $notes[] = "Focus next on $list. These have the most marks to gain.";
        }

        if ($attempt->total_questions > 0 && $attempt->skipped / $attempt->total_questions >= 0.2) {
            $notes[] = "You skipped {$attempt->skipped} of {$attempt->total_questions} questions. A wrong answer costs nothing, so always pick an option.";
        }

        $limit = $attempt->duration_minutes * 60;
        if ($limit > 0 && $attempt->time_spent_seconds !== null) {
            if ($attempt->time_spent_seconds >= $limit - 5) {
                $notes[] = 'You ran out of time. Practise pacing: about ' . max(1, (int) round($limit / max(1, $attempt->total_questions))) . ' seconds per question.';
            } elseif ($attempt->time_spent_seconds < $limit * 0.25 && $attempt->skipped > 0) {
                $notes[] = 'You finished with most of the time left. Use it to review flagged and skipped questions.';
            }
        }

        if (empty($notes)) {
            $notes[] = 'Solid all round. Try a harder mix of years and topics next time.';
        }

        return $notes;
    }
}

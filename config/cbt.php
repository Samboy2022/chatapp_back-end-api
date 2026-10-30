<?php

/**
 * CBT practice: the exams on offer and the rules each one is set up with.
 *
 * Questions, subjects and topics live in the database (admin → CBT
 * Questions); the exam definitions are fixed, so they live here.
 *
 * Per exam:
 *   questions_per_subject  how many questions each chosen subject gets
 *   marks_per_subject      what a subject is scored out of (JAMB: 100)
 *   min/max_subjects       how many subjects the candidate must/can pick,
 *                          compulsory ones included
 *   compulsory             subject slugs that are always included
 *   default_duration       minutes, pre-selected on the setup screen
 *   question_pool          `exam`: only questions tagged for this exam (plus
 *                          untagged ones); `all`: every question
 */
return [
    'exams' => [
        'jamb' => [
            'title' => 'JAMB Practice',
            'badge' => 'UTME 2026',
            'icon' => 'school',
            'description' => 'Unified Tertiary Matriculation Examination practice: Use of English plus three subjects.',
            'questions_per_subject' => 40,
            'marks_per_subject' => 100,
            'min_subjects' => 4,
            'max_subjects' => 4,
            'compulsory' => ['english'],
            'default_duration' => 60,
            'question_pool' => 'exam',
        ],
        'waec' => [
            'title' => 'WAEC Prep',
            'badge' => 'SSCE',
            'icon' => 'book',
            'description' => 'West African Senior School Certificate objective papers.',
            'questions_per_subject' => 50,
            'marks_per_subject' => 100,
            'min_subjects' => 1,
            'max_subjects' => 5,
            'compulsory' => ['english'],
            'default_duration' => 60,
            'question_pool' => 'exam',
        ],
        'neco' => [
            'title' => 'NECO Exam',
            'badge' => 'SSCE / BECE',
            'icon' => 'chip',
            'description' => 'National Examinations Council objective papers.',
            'questions_per_subject' => 50,
            'marks_per_subject' => 100,
            'min_subjects' => 1,
            'max_subjects' => 5,
            'compulsory' => ['english'],
            'default_duration' => 60,
            'question_pool' => 'exam',
        ],
        'mock' => [
            'title' => 'Mock CBT',
            'badge' => 'SPEED TEST',
            'icon' => 'timer',
            'description' => 'A short mixed test from the whole question bank.',
            'questions_per_subject' => 20,
            'marks_per_subject' => 100,
            'min_subjects' => 1,
            'max_subjects' => 4,
            'compulsory' => [],
            'default_duration' => 20,
            'question_pool' => 'all',
        ],
    ],

    'modes' => [
        'cbt' => [
            'title' => 'CBT Mode',
            'description' => 'Timed, like the real exam. Move freely between questions; your result comes at the end.',
        ],
        'practice' => [
            'title' => 'Practice Mode',
            'description' => 'See the right answer and why as soon as you answer. Best for learning.',
        ],
        'mock' => [
            'title' => 'Mock Mode',
            'description' => 'Strict exam conditions: an answer locks once chosen, and the exam submits itself when time runs out.',
        ],
    ],

    /** Minutes offered as quick picks; "Custom" allows any value in range. */
    'durations' => [20, 30, 45, 60],
    'min_duration' => 5,
    'max_duration' => 240,

    'instructions' => [
        'Read each question carefully and pick one option.',
        'Use the subject tabs to switch between subjects, and the question navigator to jump to any question.',
        'Flag a question to come back to it later.',
        'The timer starts as soon as the exam opens. When it reaches zero your answers are submitted automatically.',
        'A calculator is available from the top bar.',
        'Unanswered questions score zero; there is no penalty for a wrong answer.',
    ],
];

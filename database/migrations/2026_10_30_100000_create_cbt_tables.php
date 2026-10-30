<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CBT practice: the question bank (subjects → topics → questions), the slides
 * on the CBT screen, and candidates' attempts with their answers.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cbt_subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('cbt_topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('cbt_subjects')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
            $table->unique(['subject_id', 'name']);
        });

        Schema::create('cbt_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('cbt_subjects')->cascadeOnDelete();
            $table->foreignId('topic_id')->nullable()->constrained('cbt_topics')->nullOnDelete();
            // jamb / waec / neco, or null for a question that suits any exam.
            $table->string('exam_type', 16)->nullable();
            // The past-paper year, when the question comes from one.
            $table->unsignedSmallInteger('year')->nullable();
            $table->text('question');
            // {"A": "...", "B": "...", ...}: two to five options.
            $table->json('options');
            $table->char('correct_option', 1);
            $table->text('explanation')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['subject_id', 'exam_type', 'year']);
        });

        Schema::create('cbt_slides', function (Blueprint $table) {
            $table->id();
            $table->string('badge')->nullable();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('cta_label')->nullable();
            // What the button does: an exam slug (jamb, waec, neco, mock),
            // a web URL, or null for no button.
            $table->string('cta_target', 2048)->nullable();
            $table->string('color_from', 7)->default('#0B1F7A');
            $table->string('color_to', 7)->default('#111827');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('cbt_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('exam_type', 16);
            $table->string('mode', 16);
            $table->unsignedSmallInteger('duration_minutes');
            $table->string('status', 16)->default('in_progress'); // in_progress | submitted
            // [{subject_id, years, topic_ids, question_ids}] in exam order.
            $table->json('setup');
            $table->timestamp('started_at');
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedInteger('time_spent_seconds')->nullable();
            $table->unsignedSmallInteger('total_questions')->default(0);
            $table->unsignedSmallInteger('correct')->default(0);
            $table->unsignedSmallInteger('wrong')->default(0);
            $table->unsignedSmallInteger('skipped')->default(0);
            $table->unsignedInteger('score')->default(0);
            $table->unsignedInteger('max_score')->default(0);
            $table->timestamps();
            $table->index(['user_id', 'status', 'submitted_at']);
        });

        Schema::create('cbt_attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('cbt_attempts')->cascadeOnDelete();
            // Kept even if the question is later deleted, so old results still
            // add up; the result sheet just can't show its text any more.
            $table->foreignId('question_id')->nullable()->constrained('cbt_questions')->nullOnDelete();
            $table->foreignId('subject_id')->constrained('cbt_subjects')->cascadeOnDelete();
            $table->foreignId('topic_id')->nullable()->constrained('cbt_topics')->nullOnDelete();
            $table->unsignedSmallInteger('position');
            $table->char('selected_option', 1)->nullable();
            $table->char('correct_option', 1);
            $table->boolean('is_correct')->default(false);
            $table->index(['attempt_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cbt_attempt_answers');
        Schema::dropIfExists('cbt_attempts');
        Schema::dropIfExists('cbt_slides');
        Schema::dropIfExists('cbt_questions');
        Schema::dropIfExists('cbt_topics');
        Schema::dropIfExists('cbt_subjects');
    }
};

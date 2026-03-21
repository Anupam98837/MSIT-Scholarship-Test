<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doubt_submissions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('student_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // e.g. 'mathematics', 'science', 'english'
            $table->string('subject', 50);

            /*
             * Nested structure: chapter slug → subtopic slug → 1/0
             */
            $table->json('topics');

            $table->text('notes')->nullable();

            $table->timestamp('submitted_at')->useCurrent();

            // MariaDB-safe solution:
            // create a generated date column, then index that column
            $table->date('submitted_date')->storedAs('DATE(submitted_at)');

            $table->unique(
                ['student_id', 'subject', 'submitted_date'],
                'uq_student_subject_day'
            );

            $table->index('student_id');
            $table->index('subject');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doubt_submissions');
    }
};
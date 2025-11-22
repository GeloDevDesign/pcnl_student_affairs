<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('evaluation_cycles', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "1st Sem 2025"
            $table->date('start_date'); // NEW
            $table->date('end_date');   // NEW
            $table->boolean('is_active')->default(true); // Manual override to close early if needed
            $table->timestamps();
        });

          Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_cycle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete(); // Assuming students are users
            $table->foreignId('instructor_id')->constrained('instructors')->cascadeOnDelete();
            // Optional: If you evaluate per subject
            // $table->foreignId('subject_id')->nullable()->constrained(); 
            
            // Qualitative feedback from PDF
            $table->text('comments_teacher')->nullable();
            $table->text('comments_subject')->nullable();
            
            $table->timestamps();

            // Prevent duplicate evaluations for the same instructor in the same cycle
            $table->unique(['evaluation_cycle_id', 'student_id', 'instructor_id'], 'unique_evaluation');
        });

         Schema::create('evaluation_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_id')->constrained()->cascadeOnDelete();
            $table->integer('question_index'); // 1 to 25 (matches your PDF)
            $table->integer('rating'); // 1 to 4
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation_answers');
        Schema::dropIfExists('evaluations');
        Schema::dropIfExists('evaluation_cycles');
    }
};

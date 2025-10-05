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
        Schema::create('votes', function (Blueprint $table) {
            $table->id();

            // Voter (student or whoever is allowed to vote)
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Election where the vote belongs
            $table->foreignId('election_id')
                ->constrained()
                ->cascadeOnDelete();

            // Role being voted for
            $table->foreignId('role_id')
                ->constrained()
                ->cascadeOnDelete();

            // Candidate being voted for
            $table->foreignId('candidate_id')
                ->constrained()
                ->cascadeOnDelete();

            // Optional: to track the exact time of voting
            $table->timestamp('voted_at')->useCurrent();

            $table->timestamps();

            // Ensure a user can vote only ONCE per role per election
            // $table->unique(['user_id', 'election_id', 'role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
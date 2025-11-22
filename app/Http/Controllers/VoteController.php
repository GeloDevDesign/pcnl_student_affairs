<?php

namespace App\Http\Controllers;

use App\Models\Election;
use App\Models\Role;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VoteController extends Controller
{
    public function store(Request $request)
    {
       
        $validated = $request->validate([
            'votes' => 'required|array',
            'votes.*.election_id' => 'required|exists:elections,id',
            'votes.*.role_id' => 'required|exists:roles,id',
            'votes.*.candidate_id' => 'required|exists:candidates,id',
        ]);

    

        $userId = auth()->id();
        if (! $userId) {
            Log::error('No authenticated user found');

            return back()->with('error', 'You must be logged in to vote.');
        }

        $electionId = $validated['votes'][0]['election_id'];

    
        $hasVoted = Vote::where('user_id', $userId)
            ->where('election_id', $electionId)
            ->exists();

        if ($hasVoted) {
            Log::warning('User already voted', ['user_id' => $userId, 'election_id' => $electionId]);

            return back()->with('error', 'You have already voted in this election!');
        }

      
        $election = Election::find($electionId);
        if (! $election) {
            Log::error('Election not found', ['election_id' => $electionId]);

            return back()->with('error', 'Invalid election.');
        }

        $now = now();

   
        $startDate = \Carbon\Carbon::parse($election->start_date);
        $endDate = \Carbon\Carbon::parse($election->end_date)->endOfDay(); 

        if ($now < $startDate || $now > $endDate) {
            Log::warning('Election not active', [
                'election_id' => $electionId,
                'now' => $now->toDateTimeString(),
                'start_date' => $startDate->toDateTimeString(),
                'end_date' => $endDate->toDateTimeString(),
            ]);

            return back()->with('error', 'Voting is not currently open for this election!');
        }

       
        $votedRoles = collect($validated['votes'])->pluck('role_id')->unique()->toArray();

    
        if (count($votedRoles) < 1) {
            return back()->with('error', 'Please vote for at least one position!');
        }

        try {
            DB::beginTransaction();


            Log::info('Vote submission attempt', [
                'user_id' => $userId,
                'election_id' => $electionId,
                'votes' => $validated['votes'],
            ]);

            
            foreach ($validated['votes'] as $voteData) {
               
                $candidate = \App\Models\Candidate::where('id', $voteData['candidate_id'])
                    ->where('role_id', $voteData['role_id'])
                    ->where('election_id', $voteData['election_id'])
                    ->first();

                if (! $candidate) {
                    Log::error('Invalid candidate', [
                        'candidate_id' => $voteData['candidate_id'],
                        'role_id' => $voteData['role_id'],
                        'election_id' => $voteData['election_id'],
                    ]);
                    throw new \Exception("Invalid candidate for role {$voteData['role_id']} in election {$voteData['election_id']}");
                }

                $vote = Vote::create([
                    'user_id' => $userId,
                    'election_id' => $voteData['election_id'],
                    'role_id' => $voteData['role_id'],
                    'candidate_id' => $voteData['candidate_id'],
                    'voted_at' => now(),
                ]);

               
                Log::info('Vote created', [
                    'vote_id' => $vote->id,
                    'user_id' => $userId,
                    'election_id' => $voteData['election_id'],
                    'role_id' => $voteData['role_id'],
                    'candidate_id' => $voteData['candidate_id'],
                ]);
            }

            DB::commit();
            Log::info('Vote submission successful', ['user_id' => $userId, 'election_id' => $electionId]);

            return back()->with('success', 'Your vote has been submitted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            
            Log::error('Vote submission failed', [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Failed to submit vote: '.$e->getMessage());
        }
    }

    public function checkVoteStatus(Request $request)
    {

        $electionId = $request->input('election_id');
        $userId = $request->user()->id;

        $hasVoted = Vote::where('user_id', $userId)
            ->where('election_id', $electionId)
            ->exists();

        return response()->json(['has_voted' => $hasVoted]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PartyList;
use App\Models\Role;
use App\Models\Election;
use App\Models\Candidate;
use App\Models\Vote;
use Inertia\Inertia;

class OfficersController extends Controller
{
    public function index(Request $request)
    {


        $selectedElectionId = $request->filled('election_id') ? $request->election_id
            : null;

        // Party list & roles remain the same
        $partyList = PartyList::with(['user'])->get();
        $roles = Role::select(['id', 'name', 'description'])
            ->with([
                'candidates' => function ($query) use ($selectedElectionId) {
                    if ($selectedElectionId) {
                        $query->where('election_id', $selectedElectionId);
                    }
                    $query->select(['id', 'full_name', 'role_id', 'party_id', 'election_id']);
                },
                'candidates.party_list:id,name'
            ])
            ->get();

        // If election_id not provided, fallback to latest election
        $election = $selectedElectionId
            ? Election::find($selectedElectionId)
            : Election::latest()->first();

        $resultsData = null;

        if ($election) {
            $canViewResults = (auth()->user()->isAdmin() ?? false) || $election->status == Election::CLOSED;

            if ($canViewResults) {
                $totalVoters = Vote::where('election_id', $election->id)
                    ->distinct('user_id')
                    ->count('user_id');

                $rolesForResults = Role::all();
                $results = [];

                foreach ($rolesForResults as $role) {
                    $candidates = Candidate::where('role_id', $role->id)
                        ->where('election_id', $election->id)
                        ->with('party_list:id,name')
                        ->get();

                    $candidatesWithVotes = [];

                    foreach ($candidates as $candidate) {
                        $voteCount = Vote::where('election_id', $election->id)
                            ->where('candidate_id', $candidate->id)
                            ->count();

                        $percentage = $totalVoters > 0
                            ? round(($voteCount / $totalVoters) * 100, 2)
                            : 0;

                        $candidatesWithVotes[] = [
                            'id' => $candidate->id,
                            'full_name' => $candidate->full_name,
                            'party_list' => $candidate->party_list->name ?? 'N/A',
                            'party_id' => $candidate->party_id,
                            'votes' => $voteCount,
                            'percentage' => $percentage,
                        ];
                    }

                    usort($candidatesWithVotes, fn($a, $b) => $b['votes'] - $a['votes']);

                    if (count($candidatesWithVotes) > 0) {
                        $results[] = [
                            'role_id' => $role->id,
                            'role_name' => $role->name,
                            'role_description' => $role->description,
                            'candidates' => $candidatesWithVotes,
                        ];
                    }
                }

                // Party statistics
                $partyStats = PartyList::all()
                    ->map(function ($party) use ($election) {
                        $totalVotes = Vote::where('election_id', $election->id)
                            ->whereIn('candidate_id', function ($query) use ($party, $election) {
                                $query->select('id')
                                    ->from('candidates')
                                    ->where('party_id', $party->id)
                                    ->where('election_id', $election->id);
                            })
                            ->count();

                        return [
                            'id' => $party->id,
                            'name' => $party->name,
                            'total_votes' => $totalVotes,
                        ];
                    })
                    ->filter(fn($party) => $party['total_votes'] > 0)
                    ->sortByDesc('total_votes')
                    ->values();

                $resultsData = [
                    'election' => [
                        'id' => $election->id,
                        'name' => $election->name,
                        'start_date' => $election->start_date,
                        'end_date' => $election->end_date,
                        'status' => $election->status,
                    ],
                    'results' => $results,
                    'totalVoters' => $totalVoters,
                    'partyStats' => $partyStats,
                    'canViewResults' => $canViewResults,
                ];
            }
        }

        $elections = Election::select('id', 'name', 'status', 'start_date', 'end_date')
            ->orderBy('created_at', 'desc')
            ->get();

        $elections = Election::select('id', 'name', 'status', 'start_date', 'end_date')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($election) {
                return [
                    'id' => $election->id,
                    'name' => $election->name,
                ];
            });


        return Inertia::render('ssc-officers/index', [
            'pageTitle' => 'PCNL - SCC Officers',
            'partyList' => $partyList,
            'roles' => $roles,
            'resultsData' => $resultsData,
            'elections' => $elections
        ]);
    }
}

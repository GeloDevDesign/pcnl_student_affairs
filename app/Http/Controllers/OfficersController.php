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
        // Original queries - unchanged
        $partyList = PartyList::with(['user'])->get();
        $roles = Role::select(['id', 'name', 'description'])
            ->with([
                'candidates' => function ($query) {
                    $query->select(['id', 'full_name', 'role_id', 'party_id']);
                },
                'candidates.party_list:id,name'
            ])
            ->get();

        // NEW: Results data query
        $resultsData = null;
        $election = Election::latest()->first();
        
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

                        $percentage = $totalVoters > 0 ? round(($voteCount / $totalVoters) * 100, 2) : 0;

                        $candidatesWithVotes[] = [
                            'id' => $candidate->id,
                            'full_name' => $candidate->full_name,
                            'party_list' => $candidate->party_list->name ?? 'N/A',
                            'party_id' => $candidate->party_id,
                            'votes' => $voteCount,
                            'percentage' => $percentage,
                        ];
                    }

                    usort($candidatesWithVotes, function($a, $b) {
                        return $b['votes'] - $a['votes'];
                    });

                    if (count($candidatesWithVotes) > 0) {
                        $results[] = [
                            'role_id' => $role->id,
                            'role_name' => $role->name,
                            'role_description' => $role->description,
                            'candidates' => $candidatesWithVotes,
                        ];
                    }
                }

                $partyStats = PartyList::all()
                    ->map(function ($party) use ($election) {
                        $totalVotes = Vote::where('election_id', $election->id)
                            ->whereIn('candidate_id', function($query) use ($party, $election) {
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
                    ->filter(function($party) {
                        return $party['total_votes'] > 0;
                    })
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

   

        return Inertia::render('ssc-officers/index', [
            'pageTitle' => 'PCNL - SCC Officers',
            'partyList' => $partyList,
            'roles' => $roles,
            'resultsData' => $resultsData, // NEW: Added results data
        ]);
    }
}
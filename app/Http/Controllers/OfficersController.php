<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Election;
use App\Models\PartyList;
use App\Models\Role;
use App\Models\Vote;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OfficersController extends Controller
{
    public function index(Request $request)
    {
        if ($request->filled('election_id')) {
            $selectedElectionId = $request->election_id;

            Election::where('id', '!=', $selectedElectionId)->update(['is_set' => false]);
            Election::where('id', $selectedElectionId)->update(['is_set' => true]);
        }

        $activeElection = Election::where('is_set', true)->first();

        if (! $activeElection) {
            $activeElection = Election::latest()->first();
            if ($activeElection) {
                $activeElection->update(['is_set' => true]);
            }
        }

        $selectedElectionId = $activeElection?->id;

        $partyList = PartyList::with([
            'candidates' => function ($query) use ($selectedElectionId) {
                if ($selectedElectionId) {
                    $query->where('election_id', $selectedElectionId);
                }
            },
            'user',
        ])
            ->where('election_id', $selectedElectionId)
            ->get();

        $roles = Role::where('election_id', $selectedElectionId)
            ->with(['candidates' => function ($q) use ($selectedElectionId) {
                $q->where('election_id', $selectedElectionId)
                    ->select(['id', 'full_name', 'role_id', 'party_id', 'election_id'])
                    ->with('party_list:id,name');
            }])
            ->get();

        $election = $selectedElectionId
            ? Election::find($selectedElectionId)
            : Election::latest()->first();

        $resultsData = null;

        if ($election) {
            $canViewResults = $election->status == 2 || $request->user()->isAdmin();

            $totalVoters = Vote::where('election_id', $election->id)
                ->distinct('user_id')
                ->count('user_id');

            $rolesForResults = Role::where('election_id', $election->id)->get();
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

                usort($candidatesWithVotes, fn ($a, $b) => $b['votes'] - $a['votes']);

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
                ->filter(fn ($party) => $party['total_votes'] > 0)
                ->sortByDesc('total_votes')
                ->values();

            $resultsData = [
                'election' => [
                    'id' => $election->id,
                    'name' => $election->name,
                    'start_date' => $election->start_date,
                    'start_time' => $election->start_time, // ADDED THIS
                    'end_date' => $election->end_date,
                    'end_time' => $election->end_time,     // ADDED THIS
                    'status' => $election->status,
                ],
                'results' => $results,
                'totalVoters' => $totalVoters,
                'partyStats' => $partyStats,
                'canViewResults' => $canViewResults,
            ];
        }

        // Removed duplicate query. Keep this mapped one.
        $elections = Election::select('id', 'name', 'status', 'start_date', 'end_date', 'end_time', 'start_time')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($election) {
                return [
                    'id' => $election->id,
                    'name' => $election->name,
                    'status' => $election->status,
                    'start_date' => $election->start_date,
                    'end_date' => $election->end_date,
                    'start_time' => $election->start_time,
                    'end_time' => $election->end_time,
                ];
            });

        return Inertia::render('ssc-officers/index', [
            'pageTitle' => 'PCNL - SCC Officers',
            'partyList' => $partyList,
            'roles' => $roles,
            'resultsData' => $resultsData,
            'elections' => $elections,
            'selectedElection' => $election, // PASS THE SELECTED ELECTION PROP
        ]);
    }

    public function createWizard(Request $request)
    {
        // Validate the entire wizard submission
        $validated = $request->validate([
            'election.name' => 'required|string|max:255',
            'election.start_date' => 'required|date',
            'election.start_time' => 'required|date_format:H:i', // Added
            'election.end_date' => 'required|date|after_or_equal:election.start_date',
            'election.end_time' => 'required|date_format:H:i',   // Added
            'election.description' => 'nullable|string',
            'party_lists' => 'required|array|min:1',
            'party_lists.*.name' => 'required|string|max:255',
            'roles' => 'required|array|min:1',
            'roles.*.name' => 'required|string|max:255',
            'roles.*.description' => 'nullable|string',
            'candidates' => 'nullable|array',
            'candidates.*.full_name' => 'required|string|max:255',
            'candidates.*.role_id' => 'required|integer',
            'candidates.*.party_id' => 'required|integer',
        ]);

        try {
            DB::beginTransaction();

            // Step 1: Create Election
            $election = Election::create([
                'name' => $validated['election']['name'],
                'start_date' => $validated['election']['start_date'],
                'start_time' => $validated['election']['start_time'], // Added
                'end_date' => $validated['election']['end_date'],
                'end_time' => $validated['election']['end_time'],     // Added
                'description' => $validated['election']['description'] ?? null,
                'status' => 0, // Not started
                'is_set' => false,
                'user_id' => auth()->id(),
            ]);

            Log::info('Election created', ['election_id' => $election->id]);

            // Step 2: Create Party Lists
            $partyMapping = []; // To map temporary IDs to actual IDs
            foreach ($validated['party_lists'] as $index => $partyData) {
                $party = PartyList::create([
                    'name' => $partyData['name'],
                    'election_id' => $election->id,
                    'user_id' => auth()->id(), // Assuming party creator is current admin
                ]);
                $partyMapping[$index] = $party->id;
                Log::info('Party created', ['party_id' => $party->id, 'election_id' => $election->id]);
            }

            // Step 3: Create Roles
            $roleMapping = []; // To map temporary IDs to actual IDs
            foreach ($validated['roles'] as $index => $roleData) {
                $role = Role::create([
                    'name' => $roleData['name'],
                    'description' => $roleData['description'] ?? null,
                    'election_id' => $election->id,
                ]);
                $roleMapping[$index] = $role->id;
                Log::info('Role created', ['role_id' => $role->id, 'election_id' => $election->id]);
            }

            // Step 4: Create Candidates (if any)
            if (! empty($validated['candidates'])) {
                foreach ($validated['candidates'] as $candidateData) {
                    // Map temporary IDs to actual IDs
                    $actualRoleId = $roleMapping[$candidateData['role_id']] ?? null;
                    $actualPartyId = $partyMapping[$candidateData['party_id']] ?? null;

                    if (! $actualRoleId || ! $actualPartyId) {
                        Log::error('Invalid role or party mapping', [
                            'candidate' => $candidateData,
                            'role_mapping' => $roleMapping,
                            'party_mapping' => $partyMapping,
                        ]);

                        continue; // Skip invalid candidate
                    }

                    $candidate = Candidate::create([
                        'full_name' => $candidateData['full_name'],
                        'role_id' => $actualRoleId,
                        'party_id' => $actualPartyId,
                        'election_id' => $election->id,
                    ]);

                    Log::info('Candidate created', [
                        'candidate_id' => $candidate->id,
                        'election_id' => $election->id,
                        'role_id' => $actualRoleId,
                        'party_id' => $actualPartyId,
                    ]);
                }
            }

            DB::commit();

            Log::info('Election wizard completed successfully', [
                'election_id' => $election->id,
                'parties_count' => count($partyMapping),
                'roles_count' => count($roleMapping),
                'candidates_count' => count($validated['candidates'] ?? []),
            ]);

            return back()->with('success', 'Election created successfully with all details!');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Election wizard failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Failed to create election: '.$e->getMessage());
        }
    }
}

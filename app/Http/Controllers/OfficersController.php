<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PartyList;
use App\Models\Role;
use Inertia\Inertia;

class OfficersController extends Controller
{
    public function index(Request $request)
    {
        $partyList = PartyList::with(['user'])->get();
        $roles = Role::select(['id', 'name', 'description'])
            ->with([
                'candidates' => function ($query) {
                    $query->select(['id', 'full_name', 'role_id', 'party_id']);
                },
                'candidates.party_list:id,name' // 👈 eager load the party list name
            ])
            ->get();






        return Inertia::render('ssc-officers/index', [
            'pageTitle' => 'PCNL - SCC Officers',
            'partyList' => $partyList,
            'roles' => $roles
        ]);
    }
}

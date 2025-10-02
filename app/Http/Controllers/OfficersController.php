<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PartyList;
use Inertia\Inertia;

class OfficersController extends Controller
{
    public function index(Request $request)
    {
        $partyList = PartyList::with(['user'])->get();


        return Inertia::render('ssc-officers/index', [
            'pageTitle' => 'PCNL - SCC Officers',
            'partyList' => $partyList
        ]);
    }
}

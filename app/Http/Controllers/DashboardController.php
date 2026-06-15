<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientFollowup;

class DashboardController extends Controller
{
    public function index()
    {
        $clientsCount = Client::query()->count();

        $newClientsCount = Client::query()
            ->where('status', 'new')
            ->count();

        $activeClientsCount = Client::query()
            ->where('status', 'active')
            ->count();

        $todayFollowupsCount = ClientFollowup::query()
            ->whereDate('next_followup_at', today())
            ->count();

        $overdueFollowupsCount = ClientFollowup::query()
            ->where('status', 'pending')
            ->whereNotNull('next_followup_at')
            ->where('next_followup_at', '<', now())
            ->count();

        $pendingFollowupsCount = ClientFollowup::query()
            ->where('status', 'pending')
            ->count();

        $latestClients = Client::query()
            ->with('assignedUser')
            ->latest()
            ->limit(5)
            ->get();

        $latestFollowups = ClientFollowup::query()
            ->with(['client', 'user'])
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'clientsCount',
            'newClientsCount',
            'activeClientsCount',
            'todayFollowupsCount',
            'overdueFollowupsCount',
            'pendingFollowupsCount',
            'latestClients',
            'latestFollowups',
        ));
    }
}
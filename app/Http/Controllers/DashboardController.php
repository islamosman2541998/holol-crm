<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientFollowup;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\LeadFollowup;

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

        $leadsCount = Lead::query()->count();

        $newLeadsCount = Lead::query()
            ->where('status', 'new')
            ->count();

        $qualifiedLeadsCount = Lead::query()
            ->where('status', 'qualified')
            ->count();

        $convertedLeadsCount = Lead::query()
            ->where('status', 'converted')
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

        $salesTotal = Sale::query()
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        $paymentsTotal = Payment::query()
            ->sum('amount');

        $remainingTotal = max($salesTotal - $paymentsTotal, 0);

        $monthlySalesTotal = Sale::query()
            ->where('status', '!=', 'cancelled')
            ->whereYear('sold_at', now()->year)
            ->whereMonth('sold_at', now()->month)
            ->sum('total');

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

        $latestSales = Sale::query()
            ->with(['client', 'user', 'payments'])
            ->latest()
            ->limit(5)
            ->get();

        $latestLeads = Lead::query()
            ->with('assignedUser')
            ->latest()
            ->limit(5)
            ->get();
        $todayLeadFollowupsCount = LeadFollowup::query()
            ->whereDate('next_followup_at', today())
            ->count();

        $overdueLeadFollowupsCount = LeadFollowup::query()
            ->where('status', 'pending')
            ->whereNotNull('next_followup_at')
            ->where('next_followup_at', '<', now())
            ->count();

        $pendingLeadFollowupsCount = LeadFollowup::query()
            ->where('status', 'pending')
            ->count();
        $latestLeadFollowups = LeadFollowup::query()
            ->with(['lead', 'user'])
            ->latest()
            ->limit(5)
            ->get();
        return view('admin.dashboard', compact(
            'clientsCount',
            'newClientsCount',
            'activeClientsCount',
            'leadsCount',
            'newLeadsCount',
            'qualifiedLeadsCount',
            'convertedLeadsCount',
            'todayFollowupsCount',
            'overdueFollowupsCount',
            'pendingFollowupsCount',
            'salesTotal',
            'paymentsTotal',
            'remainingTotal',
            'monthlySalesTotal',
            'latestClients',
            'latestFollowups',
            'latestSales',
            'latestLeads',
            'todayLeadFollowupsCount',
            'overdueLeadFollowupsCount',
            'pendingLeadFollowupsCount',
            'latestLeadFollowups',
        ));
    }
}

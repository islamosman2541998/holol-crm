<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientFollowup;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\LeadFollowup;
use App\Models\Member;
use App\Models\Task;
use App\Models\Project;

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
        $taskQuery = Task::query();

        $user = auth()->user();

        if (! $user->can('tasks.view_all')) {
            $member = $user->member;

            if (! $member) {
                $taskQuery->whereRaw('1 = 0');
            } elseif ($member->is_manager && $member->team_id) {
                $teamMemberIds = Member::query()
                    ->where('team_id', $member->team_id)
                    ->pluck('id');

                $taskQuery->whereIn('assigned_member_id', $teamMemberIds);
            } else {
                $taskQuery->where('assigned_member_id', $member->id);
            }
        }

        $todayTasksCount = (clone $taskQuery)
            ->whereDate('due_at', today())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        $overdueTasksCount = (clone $taskQuery)
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        $inProgressTasksCount = (clone $taskQuery)
            ->where('status', 'in_progress')
            ->count();

        $reviewTasksCount = (clone $taskQuery)
            ->where('status', 'review')
            ->count();

        $completedTasksCount = (clone $taskQuery)
            ->where('status', 'completed')
            ->count();

        $latestTasks = (clone $taskQuery)
            ->with(['assignedMember.team', 'client', 'lead'])
            ->latest()
            ->limit(6)
            ->get();

        $projectQuery = Project::query();

        $user = auth()->user();

        if (! $user->can('projects.view_all')) {
            $member = $user->member;

            if (! $member) {
                $projectQuery->whereRaw('1 = 0');
            } else {
                $projectQuery->where(function ($query) use ($member) {
                    $query->where('manager_member_id', $member->id)
                        ->orWhereHas('tasks', function ($query) use ($member) {
                            $query->where('assigned_member_id', $member->id);
                        });

                    if ($member->is_manager && $member->team_id) {
                        $query->orWhere('team_id', $member->team_id);
                    }
                });
            }
        }

        $activeProjectsCount = (clone $projectQuery)
            ->whereIn('status', ['new', 'planning', 'in_progress', 'on_hold'])
            ->count();

        $inProgressProjectsCount = (clone $projectQuery)
            ->where('status', 'in_progress')
            ->count();

        $overdueProjectsCount = (clone $projectQuery)
            ->whereNotNull('due_date')
            ->where('due_date', '<', today())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        $completedProjectsCount = (clone $projectQuery)
            ->where('status', 'completed')
            ->count();

        $latestProjects = (clone $projectQuery)
            ->with(['client', 'service', 'team', 'manager'])
            ->withCount(['tasks', 'openTasks'])
            ->latest()
            ->limit(6)
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
            'todayTasksCount',
            'overdueTasksCount',
            'inProgressTasksCount',
            'reviewTasksCount',
            'completedTasksCount',
            'latestTasks',
            'activeProjectsCount',
            'inProgressProjectsCount',
            'overdueProjectsCount',
            'completedProjectsCount',
            'latestProjects',
        ));
    }
}

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
use App\Traits\AuthorizesOwnedRecords;

class DashboardController extends Controller
{
    use AuthorizesOwnedRecords;

    public function index()
    {
        $clientQuery = Client::query();
        $this->applyOwnedRecordScope($clientQuery, 'clients.view_all', 'assigned_to');

        $clientsCount = (clone $clientQuery)->count();

        $newClientsCount = (clone $clientQuery)
            ->where('status', 'new')
            ->count();

        $activeClientsCount = (clone $clientQuery)
            ->where('status', 'active')
            ->count();

        $leadQuery = Lead::query();
        $this->applyOwnedRecordScope($leadQuery, 'leads.view_all', 'assigned_to');

        $leadsCount = (clone $leadQuery)->count();

        $newLeadsCount = (clone $leadQuery)
            ->where('status', 'new')
            ->count();

        $qualifiedLeadsCount = (clone $leadQuery)
            ->where('status', 'qualified')
            ->count();

        $convertedLeadsCount = (clone $leadQuery)
            ->where('status', 'converted')
            ->count();

        $followupQuery = ClientFollowup::query()
            ->whereHas('client', function ($query) {
                $this->applyOwnedRecordScope($query, 'clients.view_all', 'assigned_to');
            });

        $todayFollowupsCount = (clone $followupQuery)
            ->whereDate('next_followup_at', today())
            ->count();

        $overdueFollowupsCount = (clone $followupQuery)
            ->where('status', 'pending')
            ->whereNotNull('next_followup_at')
            ->where('next_followup_at', '<', now())
            ->count();

        $pendingFollowupsCount = (clone $followupQuery)
            ->where('status', 'pending')
            ->count();

        $saleQuery = Sale::query();
        $this->applyOwnedRecordScope($saleQuery, 'sales.view_all', 'user_id');

        $salesTotal = (clone $saleQuery)
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        $paymentsTotal = Payment::query()
            ->whereHas('sale', function ($query) {
                $this->applyOwnedRecordScope($query, 'sales.view_all', 'user_id');
            })
            ->sum('amount');

        $remainingTotal = max($salesTotal - $paymentsTotal, 0);

        $monthlySalesTotal = (clone $saleQuery)
            ->where('status', '!=', 'cancelled')
            ->whereYear('sold_at', now()->year)
            ->whereMonth('sold_at', now()->month)
            ->sum('total');

        $latestClients = (clone $clientQuery)
            ->with('assignedUser')
            ->latest()
            ->limit(5)
            ->get();

        $latestFollowups = (clone $followupQuery)
            ->with(['client', 'user'])
            ->latest()
            ->limit(5)
            ->get();

        $latestSales = (clone $saleQuery)
            ->with(['client', 'user', 'payments'])
            ->latest()
            ->limit(5)
            ->get();

        $latestLeads = (clone $leadQuery)
            ->with('assignedUser')
            ->latest()
            ->limit(5)
            ->get();

        $leadFollowupQuery = LeadFollowup::query()
            ->whereHas('lead', function ($query) {
                $this->applyOwnedRecordScope($query, 'leads.view_all', 'assigned_to');
            });

        $todayLeadFollowupsCount = (clone $leadFollowupQuery)
            ->whereDate('next_followup_at', today())
            ->count();

        $overdueLeadFollowupsCount = (clone $leadFollowupQuery)
            ->where('status', 'pending')
            ->whereNotNull('next_followup_at')
            ->where('next_followup_at', '<', now())
            ->count();

        $pendingLeadFollowupsCount = (clone $leadFollowupQuery)
            ->where('status', 'pending')
            ->count();

        $latestLeadFollowups = (clone $leadFollowupQuery)
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

        $showTasksPopup = false;
        $popupTodayTasks = collect();
        $popupOverdueTasks = collect();

        if (session('show_tasks_popup') && $user->member) {
            $showTasksPopup = true;
            $popupTodayTasks = Task::query()->forMember($user->member->id)->dueToday()->get();
            $popupOverdueTasks = Task::query()->forMember($user->member->id)->overdue()->get();
        }

        return view('admin.dashboard', compact(
            'showTasksPopup',
            'popupTodayTasks',
            'popupOverdueTasks',
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

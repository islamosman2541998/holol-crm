<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\FollowupController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\LeadFollowupController;
use App\Http\Controllers\Admin\TeamController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\QuotationController;
use App\Http\Controllers\Admin\ReportController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.submit');
});

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.view')
        ->name('dashboard');

    Route::get('/settings', [SettingController::class, 'edit'])
        ->middleware('permission:settings.view')
        ->name('settings.edit');

    Route::post('/settings', [SettingController::class, 'update'])
        ->middleware('permission:settings.edit')
        ->name('settings.update');
    Route::get('/followups', [FollowupController::class, 'index'])
        ->middleware('permission:followups.view')
        ->name('followups.index');
    Route::get('/teams', [TeamController::class, 'index'])
        ->middleware('permission:teams.view')
        ->name('teams.index');

    Route::get('/teams/create', [TeamController::class, 'create'])
        ->middleware('permission:teams.create')
        ->name('teams.create');

    Route::post('/teams', [TeamController::class, 'store'])
        ->middleware('permission:teams.create')
        ->name('teams.store');

    Route::get('/teams/{team}', [TeamController::class, 'show'])
        ->middleware('permission:teams.view')
        ->name('teams.show');

    Route::get('/teams/{team}/edit', [TeamController::class, 'edit'])
        ->middleware('permission:teams.edit')
        ->name('teams.edit');

    Route::put('/teams/{team}', [TeamController::class, 'update'])
        ->middleware('permission:teams.edit')
        ->name('teams.update');

    Route::delete('/teams/{team}', [TeamController::class, 'destroy'])
        ->middleware('permission:teams.delete')
        ->name('teams.destroy');

    Route::get('/reports/clients', [ReportController::class, 'clients'])
        ->middleware('permission:reports.view')
        ->name('reports.clients');
        Route::get('/reports/leads', [ReportController::class, 'leads'])
    ->middleware('permission:reports.view')
    ->name('reports.leads');
    Route::get('/reports/sales-payments', [ReportController::class, 'salesPayments'])
    ->middleware('permission:reports.view')
    ->name('reports.sales-payments');
    Route::get('/reports/quotations', [ReportController::class, 'quotations'])
    ->middleware('permission:reports.view')
    ->name('reports.quotations');
    Route::get('/quotations', [QuotationController::class, 'index'])
        ->middleware('permission:quotations.view')
        ->name('quotations.index');

    Route::get('/quotations/create', [QuotationController::class, 'create'])
        ->middleware('permission:quotations.create')
        ->name('quotations.create');

    Route::post('/quotations', [QuotationController::class, 'store'])
        ->middleware('permission:quotations.create')
        ->name('quotations.store');

    Route::get('/quotations/{quotation}', [QuotationController::class, 'show'])
        ->middleware('permission:quotations.view')
        ->name('quotations.show');

    Route::get('/quotations/{quotation}/edit', [QuotationController::class, 'edit'])
        ->middleware('permission:quotations.edit')
        ->name('quotations.edit');

    Route::put('/quotations/{quotation}', [QuotationController::class, 'update'])
        ->middleware('permission:quotations.edit')
        ->name('quotations.update');

    Route::patch('/quotations/{quotation}/status', [QuotationController::class, 'changeStatus'])
        ->middleware('permission:quotations.change_status')
        ->name('quotations.change-status');

    Route::post('/quotations/{quotation}/create-sale', [QuotationController::class, 'createSale'])
        ->middleware('permission:quotations.convert_to_sale')
        ->name('quotations.create-sale');

    Route::delete('/quotations/{quotation}', [QuotationController::class, 'destroy'])
        ->middleware('permission:quotations.delete')
        ->name('quotations.destroy');
    Route::post('/sales/{sale}/payments', [PaymentController::class, 'store'])
        ->middleware('permission:payments.create')
        ->name('sales.payments.store');
    Route::get('/quotations/{quotation}/pdf', [QuotationController::class, 'pdf'])
        ->middleware('permission:quotations.view')
        ->name('quotations.pdf');
    Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])
        ->middleware('permission:payments.delete')
        ->name('payments.destroy');
    Route::get('/members', [MemberController::class, 'index'])
        ->middleware('permission:members.view')
        ->name('members.index');

    Route::get('/members/create', [MemberController::class, 'create'])
        ->middleware('permission:members.create')
        ->name('members.create');

    Route::post('/members', [MemberController::class, 'store'])
        ->middleware('permission:members.create')
        ->name('members.store');

    Route::get('/members/{member}', [MemberController::class, 'show'])
        ->middleware('permission:members.view')
        ->name('members.show');

    Route::get('/members/{member}/edit', [MemberController::class, 'edit'])
        ->middleware('permission:members.edit')
        ->name('members.edit');

    Route::put('/members/{member}', [MemberController::class, 'update'])
        ->middleware('permission:members.edit')
        ->name('members.update');

    Route::delete('/members/{member}', [MemberController::class, 'destroy'])
        ->middleware('permission:members.delete')
        ->name('members.destroy');

    Route::get('/projects', [ProjectController::class, 'index'])
        ->middleware('permission:projects.view')
        ->name('projects.index');

    Route::get('/projects/create', [ProjectController::class, 'create'])
        ->middleware('permission:projects.create')
        ->name('projects.create');

    Route::post('/projects', [ProjectController::class, 'store'])
        ->middleware('permission:projects.create')
        ->name('projects.store');

    Route::get('/projects/{project}', [ProjectController::class, 'show'])
        ->middleware('permission:projects.view')
        ->name('projects.show');

    Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])
        ->middleware('permission:projects.edit')
        ->name('projects.edit');

    Route::put('/projects/{project}', [ProjectController::class, 'update'])
        ->middleware('permission:projects.edit')
        ->name('projects.update');

    Route::patch('/projects/{project}/status', [ProjectController::class, 'changeStatus'])
        ->middleware('permission:projects.change_status')
        ->name('projects.change-status');

    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])
        ->middleware('permission:projects.delete')
        ->name('projects.destroy');

    Route::get('/projects/{project}/attachments/{attachment}/download', [ProjectController::class, 'downloadAttachment'])
        ->middleware('permission:projects.view')
        ->name('projects.attachments.download');

    Route::post('/teams/{team}/sync-members', [TeamController::class, 'syncMembers'])
        ->middleware('permission:teams.permissions')
        ->name('teams.sync-members');

    Route::get('/tasks', [TaskController::class, 'index'])
        ->middleware('permission:tasks.view')
        ->name('tasks.index');

    Route::get('/tasks/create', [TaskController::class, 'create'])
        ->middleware('permission:tasks.create')
        ->name('tasks.create');

    Route::post('/tasks', [TaskController::class, 'store'])
        ->middleware('permission:tasks.create')
        ->name('tasks.store');

    Route::get('/tasks/{task}', [TaskController::class, 'show'])
        ->middleware('permission:tasks.view')
        ->name('tasks.show');

    Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])
        ->middleware('permission:tasks.edit')
        ->name('tasks.edit');

    Route::put('/tasks/{task}', [TaskController::class, 'update'])
        ->middleware('permission:tasks.edit')
        ->name('tasks.update');

    Route::patch('/tasks/{task}/status', [TaskController::class, 'changeStatus'])
        ->middleware('permission:tasks.change_status')
        ->name('tasks.change-status');

    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])
        ->middleware('permission:tasks.delete')
        ->name('tasks.destroy');

    Route::get('/tasks/{task}/attachments/{attachment}/download', [TaskController::class, 'downloadAttachment'])
        ->middleware('permission:tasks.view')
        ->name('tasks.attachments.download');

    Route::get('/clients', [ClientController::class, 'index'])
        ->middleware('permission:clients.view')
        ->name('clients.index');

    Route::get('/clients/create', [ClientController::class, 'create'])
        ->middleware('permission:clients.create')
        ->name('clients.create');

    Route::post('/clients', [ClientController::class, 'store'])
        ->middleware('permission:clients.create')
        ->name('clients.store');
    Route::get('/clients/{client}', [ClientController::class, 'show'])
        ->middleware('permission:clients.view')
        ->name('clients.show');
    Route::get('/clients/{client}/edit', [ClientController::class, 'edit'])
        ->middleware('permission:clients.edit')
        ->name('clients.edit');

    Route::put('/clients/{client}', [ClientController::class, 'update'])
        ->middleware('permission:clients.edit')
        ->name('clients.update');

    Route::delete('/clients/{client}', [ClientController::class, 'destroy'])
        ->middleware('permission:clients.delete')
        ->name('clients.destroy');
    Route::get('/leads', [LeadController::class, 'index'])
        ->middleware('permission:leads.view')
        ->name('leads.index');

    Route::get('/leads/create', [LeadController::class, 'create'])
        ->middleware('permission:leads.create')
        ->name('leads.create');

    Route::post('/leads', [LeadController::class, 'store'])
        ->middleware('permission:leads.create')
        ->name('leads.store');

    Route::post('/leads/{lead}/convert', [LeadController::class, 'convert'])
        ->middleware('permission:leads.convert')
        ->name('leads.convert');
    Route::get('/leads/{lead}', [LeadController::class, 'show'])
        ->middleware('permission:leads.view')
        ->name('leads.show');
    Route::get('/leads/{lead}/edit', [LeadController::class, 'edit'])
        ->middleware('permission:leads.edit')
        ->name('leads.edit');

    Route::put('/leads/{lead}', [LeadController::class, 'update'])
        ->middleware('permission:leads.edit')
        ->name('leads.update');

    Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])
        ->middleware('permission:leads.delete')
        ->name('leads.destroy');
    Route::get('/services', [ServiceController::class, 'index'])
        ->middleware('permission:services.view')
        ->name('services.index');

    Route::get('/services/create', [ServiceController::class, 'create'])
        ->middleware('permission:services.create')
        ->name('services.create');

    Route::post('/services', [ServiceController::class, 'store'])
        ->middleware('permission:services.create')
        ->name('services.store');

    Route::get('/services/{service}/edit', [ServiceController::class, 'edit'])
        ->middleware('permission:services.edit')
        ->name('services.edit');

    Route::put('/services/{service}', [ServiceController::class, 'update'])
        ->middleware('permission:services.edit')
        ->name('services.update');

    Route::delete('/services/{service}', [ServiceController::class, 'destroy'])
        ->middleware('permission:services.delete')
        ->name('services.destroy');
    Route::get('/lead-followups', [LeadFollowupController::class, 'index'])
        ->middleware('permission:leads.view')
        ->name('lead-followups.index');
    Route::get('/sales', [SaleController::class, 'index'])
        ->middleware('permission:sales.view')
        ->name('sales.index');

    Route::get('/sales/create', [SaleController::class, 'create'])
        ->middleware('permission:sales.create')
        ->name('sales.create');

    Route::post('/sales', [SaleController::class, 'store'])
        ->middleware('permission:sales.create')
        ->name('sales.store');

    Route::get('/sales/{sale}', [SaleController::class, 'show'])
        ->middleware('permission:sales.view')
        ->name('sales.show');

    Route::get('/sales/{sale}/edit', [SaleController::class, 'edit'])
        ->middleware('permission:sales.edit')
        ->name('sales.edit');

    Route::put('/sales/{sale}', [SaleController::class, 'update'])
        ->middleware('permission:sales.edit')
        ->name('sales.update');

    Route::delete('/sales/{sale}', [SaleController::class, 'destroy'])
        ->middleware('permission:sales.delete')
        ->name('sales.destroy');
    Route::get('/payments', [PaymentController::class, 'index'])
        ->middleware('permission:payments.view')
        ->name('payments.index');
    Route::resource('users', UserController::class)
        ->except(['show'])
        ->middleware([
            'index' => 'permission:users.view',
            'create' => 'permission:users.create',
            'store' => 'permission:users.create',
            'edit' => 'permission:users.edit',
            'update' => 'permission:users.edit',
            'destroy' => 'permission:users.delete',
        ]);

    Route::resource('roles', RoleController::class)
        ->except(['show'])
        ->middleware([
            'index' => 'permission:roles.view',
            'create' => 'permission:roles.create',
            'store' => 'permission:roles.create',
            'edit' => 'permission:roles.edit',
            'update' => 'permission:roles.edit',
            'destroy' => 'permission:roles.delete',
        ]);

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

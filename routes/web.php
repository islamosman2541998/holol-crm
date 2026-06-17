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

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
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

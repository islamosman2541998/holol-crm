<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\FollowupController;

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

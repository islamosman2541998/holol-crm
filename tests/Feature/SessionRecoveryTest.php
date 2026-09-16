<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SessionRecoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_available_even_with_an_existing_session(): void
    {
        $user = User::factory()->create(['status' => true]);

        $this->actingAs($user)
            ->get(route('login'))
            ->assertOk();
    }

    public function test_login_redirects_to_a_page_the_user_can_access(): void
    {
        $user = User::factory()->create(['status' => true]);
        $permission = Permission::findOrCreate('tasks.view', 'web');
        $user->givePermissionTo($permission);

        $this->post(route('login.submit'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.tasks.index'));
    }

    public function test_login_without_page_permissions_falls_back_to_profile(): void
    {
        $user = User::factory()->create(['status' => true]);

        $this->post(route('login.submit'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.profile.edit'));
    }

    public function test_expired_web_session_returns_to_login_instead_of_an_error_page(): void
    {
        Route::middleware('web')->get('/test-expired-session', function () {
            throw new TokenMismatchException;
        });

        $this->actingAs(User::factory()->create(['status' => true]))
            ->get('/test-expired-session')
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}

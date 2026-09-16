<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActiveUserMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_inactive_users_existing_session_is_ended(): void
    {
        $user = User::factory()->create(['status' => false]);

        $response = $this->actingAs($user)->get(route('admin.profile.edit'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}

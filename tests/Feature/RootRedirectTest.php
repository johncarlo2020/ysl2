<?php

namespace Tests\Feature;

use App\Models\User;
use Mockery;
use Tests\TestCase;

class RootRedirectTest extends TestCase
{
    public function test_signed_in_client_is_redirected_to_dashboard(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 1;
        $user->shouldReceive('hasRole')->with('admin')->andReturn(false);

        $this->actingAs($user)->get('/')->assertRedirect(route('dashboard'));
    }

    public function test_signed_in_admin_is_redirected_to_admin_dashboard(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 1;
        $user->shouldReceive('hasRole')->with('admin')->andReturn(true);

        $this->actingAs($user)->get('/')->assertRedirect(route('admin'));
    }
}

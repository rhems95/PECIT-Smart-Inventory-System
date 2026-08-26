<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarIconsTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_layout_uses_local_colored_sidebar_icons(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Administrator');

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('psis-sidebar-icon', false);
        $response->assertSee('Dashboard');
        $response->assertSee('Inventory');
        $response->assertSee('#F4B400', false);
        $response->assertSee('#38BDF8', false);
        $response->assertDontSee('cdn.jsdelivr.net');
        $response->assertDontSee('fonts.bunny.net');
        $response->assertSee('<svg', false);
    }
}

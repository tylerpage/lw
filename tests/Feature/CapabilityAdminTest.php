<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CapabilityAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_access_capability_and_career_admin_pages(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/capabilities')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin/career-companies')
            ->assertOk();
    }
}

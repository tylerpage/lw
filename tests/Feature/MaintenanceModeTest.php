<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Page;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\PreviewUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MaintenanceModeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_public_site_shows_maintenance_page_when_enabled(): void
    {
        SiteSetting::set('maintenance_mode', true);
        SiteSetting::set('maintenance_allowlist_ips', []);

        $this->get('/')
            ->assertStatus(503)
            ->assertSee('Lindsey Wegmann', false);
    }

    public function test_allowlisted_ip_can_view_public_site_during_maintenance(): void
    {
        SiteSetting::set('maintenance_mode', true);
        SiteSetting::set('maintenance_allowlist_ips', ['127.0.0.1']);

        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->get('/')
            ->assertOk();
    }

    public function test_admin_is_not_affected_by_maintenance_mode(): void
    {
        SiteSetting::set('maintenance_mode', true);
        SiteSetting::set('maintenance_allowlist_ips', []);

        $this->get('/admin/login')->assertOk();
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_admin_livewire_requests_are_not_blocked_during_maintenance(): void
    {
        SiteSetting::set('maintenance_mode', true);
        SiteSetting::set('maintenance_allowlist_ips', []);

        $response = $this->postJson(route('default-livewire.update'), [], [
            'Referer' => url('/admin/login'),
            'X-Livewire' => '1',
        ]);

        $this->assertNotSame(503, $response->getStatusCode());
    }

    public function test_public_site_is_normal_when_maintenance_mode_is_disabled(): void
    {
        SiteSetting::set('maintenance_mode', false);

        $this->get('/')->assertOk();
    }

    public function test_signed_preview_bypasses_maintenance_mode(): void
    {
        SiteSetting::set('maintenance_mode', true);
        SiteSetting::set('maintenance_allowlist_ips', []);

        $page = Page::query()->where('slug', 'home')->firstOrFail();

        $this->get(PreviewUrl::for($page))
            ->assertOk()
            ->assertSee($page->title, false);
    }

    public function test_unsigned_preview_is_blocked_during_maintenance(): void
    {
        SiteSetting::set('maintenance_mode', true);
        SiteSetting::set('maintenance_allowlist_ips', []);

        $page = Page::query()->where('slug', 'home')->firstOrFail();

        $this->get(route('preview', ['type' => 'page', 'id' => $page->id]))
            ->assertStatus(503);
    }

    public function test_authenticated_staff_can_view_public_site_during_maintenance(): void
    {
        foreach (UserRole::cases() as $role) {
            Role::findOrCreate($role->value);
        }

        SiteSetting::set('maintenance_mode', true);
        SiteSetting::set('maintenance_allowlist_ips', []);

        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $admin->assignRole(UserRole::SuperAdmin->value);

        $this->actingAs($admin)
            ->get('/')
            ->assertOk();
    }

    public function test_guest_still_sees_maintenance_when_not_allowlisted(): void
    {
        SiteSetting::set('maintenance_mode', true);
        SiteSetting::set('maintenance_allowlist_ips', []);

        $this->get('/about')->assertStatus(503);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFacilityManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_facilities_can_be_deactivated_and_reactivated_without_deleting_them(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $facility = Facility::create([
            'name' => 'Test Hall',
            'location' => 'Main Campus',
            'owner_type' => 'gsu',
            'is_active' => true,
            'availability_status' => 'available',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.facilities.deactivate', $facility))
            ->assertRedirect(route('admin.facilities.index'));

        $this->assertDatabaseHas('facilities', ['id' => $facility->id, 'is_active' => false]);

        $this->patch(route('admin.facilities.activate', $facility))
            ->assertRedirect(route('admin.facilities.index'));

        $this->assertDatabaseHas('facilities', ['id' => $facility->id, 'is_active' => true]);
    }

    public function test_admin_facility_page_uses_manage_controls_without_a_delete_action(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Facility::create([
            'name' => 'Test Hall',
            'location' => 'Main Campus',
            'owner_type' => 'gsu',
            'is_active' => true,
            'availability_status' => 'available',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.facilities.index'))
            ->assertOk()
            ->assertSee('Manage facilities')
            ->assertSee('aria-label="Edit Test Hall"', false)
            ->assertSee('aria-label="Deactivate Test Hall"', false)
            ->assertDontSee('Delete facility');
    }

    public function test_admin_user_actions_are_gated_by_the_manage_users_control(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create(['name' => 'Jordan Staff', 'role' => 'college_staff']);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Manage users')
            ->assertSee('id="admin-users-table"', false)
            ->assertSee('.usr-table:not(.is-managing) .usr-action-cell { display: none; }', false)
            ->assertSee('aria-label="Edit Jordan Staff"', false)
            ->assertSee('aria-label="Deactivate Jordan Staff"', false);
    }
}

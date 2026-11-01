<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\FormSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuditTrailTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_review_account_changes_and_secrets_are_redacted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create([
            'name' => 'Taylor Example',
            'last_name' => 'Example',
            'role' => 'org_staff',
            'organization_name' => 'Student Council',
        ]);
        $password = str_repeat('a', 92) . 'A!';

        $this->actingAs($admin)->put(route('admin.users.update', $user), [
            'name' => 'Morgan Example',
            'last_name' => 'Example',
            'contact_number' => '',
            'email' => $user->email,
            'password' => $password,
            'password_confirmation' => $password,
            'role' => 'org_staff',
            'organization_name' => 'Student Council',
        ])->assertRedirect(route('admin.users.index'));

        $log = AuditLog::query()
            ->where('action', 'updated')
            ->where('subject_type', User::class)
            ->where('subject_id', $user->id)
            ->latest('id')
            ->firstOrFail();

        $this->assertSame($admin->id, $log->actor_id);
        $this->assertSame('Taylor Example', $log->old_values['name']);
        $this->assertSame('Morgan Example', $log->new_values['name']);
        $this->assertSame('[REDACTED]', $log->new_values['password']);
        $this->assertTrue(Hash::check($password, $user->fresh()->password));

        $this->actingAs($admin)
            ->get(route('admin.audit-logs.index'))
            ->assertOk()
            ->assertSee('Audit trail')
            ->assertSee('Morgan Example')
            ->assertSee('[REDACTED]');
    }

    public function test_non_admin_cannot_access_the_audit_trail(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'college_staff']))
            ->get(route('admin.audit-logs.index'))
            ->assertForbidden();
    }

    public function test_submission_audit_keeps_schedule_details_but_redacts_guest_identity(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $submission = FormSubmission::create([
            'type' => 'facilities_utilization',
            'requester_id' => $user->id,
            'requester_type' => 'guest',
            'requester_unit' => 'Test organization',
            'status' => 'pending',
            'payload' => [
                'client_name' => 'Private Guest',
                'date_activity' => '2027-01-01',
                'facility_ids' => [1, 2],
                'time_range' => ['start' => '09:00', 'end' => '12:00'],
            ],
        ]);

        $log = AuditLog::query()
            ->where('subject_type', FormSubmission::class)
            ->where('subject_id', $submission->id)
            ->firstOrFail();

        $this->assertSame('[REDACTED]', $log->new_values['payload']['client_name']);
        $this->assertSame([1, 2], $log->new_values['payload']['facility_ids']);
        $this->assertSame('09:00', $log->new_values['payload']['time_range']['start']);
    }

    public function test_legacy_bcrypt_passwords_remain_verifiable(): void
    {
        $this->assertTrue(Hash::check('legacy-password', bcrypt('legacy-password')));
    }
}

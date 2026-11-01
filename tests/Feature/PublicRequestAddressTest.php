<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\FormSubmission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PublicRequestAddressTest extends TestCase
{
    use DatabaseTransactions;

    public function test_address_pickers_are_saved_as_one_address_line(): void
    {
        $this->submit(['address_street' => 'Purok 3', 'address_barangay' => 'Bahuyan', 'address_town' => 'Barbaza', 'address_province' => 'Antique'])
            ->assertRedirect();
        $this->assertSame('Purok 3, Brgy. Bahuyan, Barbaza, Antique', FormSubmission::latest('id')->first()->payload['address']);

        // No street, and a barangay that already says "Barangay": no doubled prefix
        $this->submit(['address_barangay' => 'Barangay 1', 'address_town' => 'Manila City', 'address_province' => 'Metro Manila'])
            ->assertRedirect();
        $this->assertSame('Barangay 1, Manila City, Metro Manila', FormSubmission::latest('id')->first()->payload['address']);
    }

    public function test_province_town_and_barangay_are_required(): void
    {
        $this->submit([])->assertSessionHasErrors(['address_province', 'address_town', 'address_barangay']);
    }

    private function submit(array $address)
    {
        Mail::fake();
        $facility = Facility::create([
            'name' => 'Address Test Hall',
            'location' => 'Test Campus',
            'owner_type' => 'gsu',
            'is_active' => true,
            'availability_status' => 'available',
        ]);

        return $this->post('/request-facility', array_merge([
            'client_name' => 'Maria Jo',
            'last_name' => 'Dela Cruz',
            'contact_number' => '0917-123-4567',
            'email' => 'guest@example.test',
            'organization' => 'Community Partner',
            'noted_signatory_id' => 'custom',
            'noted_signatory_custom' => 'Barangay Captain',
            'date_activity' => now()->addDays(40)->toDateString(),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'facility_ids' => [$facility->id],
            'purpose' => 'Community assembly',
            'number_of_persons' => 50,
        ], $address));
    }
}

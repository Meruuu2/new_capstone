<?php

namespace Tests\Feature;

use App\Models\FormSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class RequestTicketDownloadTest extends TestCase
{
    use DatabaseTransactions;

    public function test_signed_link_downloads_the_ticket_as_a_pdf_and_unsigned_links_are_refused(): void
    {
        $requester = User::create([
            'name' => 'Taylor Requester',
            'email' => uniqid('ticket-', true) . '@example.test',
            'password' => Hash::make('password'),
            'role' => 'college_staff',
            'college_name' => 'CCIS',
        ]);
        $submission = FormSubmission::create([
            'type' => 'facilities_utilization',
            'requester_id' => $requester->id,
            'requester_type' => 'college',
            'requester_unit' => 'CCIS',
            'status' => 'pending',
            'payload' => [
                'requester_name' => 'Taylor Requester',
                'facility_names' => ['New AVR'],
                'date_activity' => now()->addDays(40)->toDateString(),
                'time_range' => ['start' => '09:00', 'end' => '10:00'],
                'purpose' => 'Seminar',
                'number_of_persons' => 100,
                'equipment' => ['monobloc_chair' => 100, 'sound' => 1],
            ],
        ]);
        $ticketNo = $submission->ticketData()['ticketNo'];

        $response = $this->get(URL::temporarySignedRoute('request-ticket.download', now()->addHour(), $submission));
        $response->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'attachment; filename="' . $ticketNo . '.pdf"');
        $this->assertStringStartsWith('%PDF', $response->getContent());

        $this->get(route('request-ticket.download', $submission))->assertForbidden();
        $this->get(URL::temporarySignedRoute('request-ticket.download', now()->addHour(), $submission) . 'x')->assertForbidden();
    }
}

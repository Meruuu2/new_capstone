<?php

namespace Tests\Unit;

use App\Mail\GuestRequestStatusUpdate;
use App\Models\FormSubmission;
use App\Services\GuestRequestEmailService;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class GuestRequestEmailServiceTest extends TestCase
{
    public function test_status_update_email_is_sent_to_public_guest_contact(): void
    {
        Mail::fake();
        $submission = new FormSubmission([
            'requester_type' => 'guest',
            'payload' => [
                'email' => 'guest@example.com',
                'client_name' => 'Taylor Dela Cruz',
                'requester_last_name' => 'Dela Cruz',
            ],
        ]);
        $submission->setAttribute('id', 42);

        app(GuestRequestEmailService::class)->sendSubmissionUpdate($submission, 'approved');

        Mail::assertSent(GuestRequestStatusUpdate::class, function (GuestRequestStatusUpdate $mail) {
            return $mail->hasTo('guest@example.com')
                && $mail->trackingCode === 'FM-' . now()->format('Y') . '-00042'
                && $mail->lastName === 'Dela Cruz'
                && $mail->event === 'approved';
        });
    }

    public function test_status_update_email_is_not_sent_to_non_guest_requesters(): void
    {
        Mail::fake();
        $submission = new FormSubmission([
            'requester_type' => 'college',
            'payload' => ['email' => 'staff@example.com', 'client_name' => 'College Staff'],
        ]);

        app(GuestRequestEmailService::class)->sendSubmissionUpdate($submission, 'approved');

        Mail::assertNothingSent();
    }
}
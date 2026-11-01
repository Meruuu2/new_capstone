<?php

namespace App\Services;

use App\Mail\GuestRequestStatusUpdate;
use App\Models\Booking;
use App\Models\FormSubmission;
use Illuminate\Support\Facades\Mail;
use Throwable;

class GuestRequestEmailService
{
    public function sendSubmissionUpdate(FormSubmission $submission, string $event, array $details = []): void
    {
        if ($submission->requester_type !== 'guest') {
            return;
        }

        $payload = $submission->payload ?? [];
        $email = trim((string) ($payload['email'] ?? ''));
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $requesterName = trim((string) ($payload['client_name'] ?? $payload['requester_name'] ?? 'Guest'));
        $lastName = trim((string) ($payload['requester_last_name'] ?? ''));
        if ($lastName === '') {
            $lastName = collect(preg_split('/\s+/', $requesterName))->last() ?? '';
        }
        $trackingCode = 'FM-' . ($submission->created_at?->format('Y') ?? now()->format('Y')) . '-'
            . str_pad((string) $submission->id, 5, '0', STR_PAD_LEFT);

        try {
            Mail::to($email)->send(new GuestRequestStatusUpdate(
                $requesterName,
                $trackingCode,
                $lastName,
                $event,
                $details
            ));
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    public function sendBookingUpdate(Booking $booking, string $event, array $details = []): void
    {
        $submissionId = $booking->additional_details['form_submission_id'] ?? null;
        if (! $submissionId) {
            return;
        }

        $submission = FormSubmission::find($submissionId);
        if (! $submission || $submission->requester_type !== 'guest') {
            return;
        }

        $booking->loadMissing('facilities');
        $details = array_merge($details, [
            'booking_code' => $booking->booking_code,
            'facility' => $booking->facilities->pluck('name')->join(', '),
            'booking_date' => $booking->start_time?->format('D, M j, Y'),
            'booking_time' => $booking->start_time && $booking->end_time
                ? $booking->start_time->format('g:i A') . ' - ' . $booking->end_time->format('g:i A')
                : null,
        ]);

        $this->sendSubmissionUpdate($submission, $event, $details);
    }
}
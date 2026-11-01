<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\FormSubmission;
use App\Models\MaintenanceTicket;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

/**
 * Public "Track a request" lookup used by the landing page.
 *
 * Public status lookup. All request types require both the code and the requester's
 * separately recorded last name; legacy records without one retain old name matching.
 */
class TicketLookupController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $type = strtoupper((string) $request->query('type', 'FM'));
        $code = strtoupper(preg_replace('/\s+/', '', (string) $request->query('code', '')));
        $lastName = mb_strtolower(trim((string) $request->query('last_name', '')));

        $notFound = response()->json(['status' => 'notfound'], 404);

        if ($type === 'RM') {
            return $this->repairTicket($code, $lastName) ?? $notFound;
        }
        if ($type === 'BK') {
            return $this->booking($code, $lastName) ?? $notFound;
        }
        if ($type !== 'FM') {
            return $notFound;
        }

        if (! preg_match('/^FM-(\d{4})-(\d{1,10})$/', $code, $m)) {
            return $notFound;
        }

        $submission = FormSubmission::with('requester')
            ->where('type', 'facilities_utilization')
            ->find((int) $m[2]);

        if (! $submission || $submission->created_at?->format('Y') !== $m[1]) {
            return $notFound;
        }

        $payload = $submission->payload ?? [];

        $explicitLastNames = array_filter([
            $payload['requester_last_name'] ?? null,
            $submission->requester?->last_name,
        ]);
        $names = array_filter([
            $payload['requester_name'] ?? null,
            $payload['client_name'] ?? null,
            optional($submission->requester)->name,
        ]);

        if ($lastName === '') {
            return response()->json(['status' => 'needs_last_name']);
        }
        if (! $this->lastNameMatches($lastName, $explicitLastNames, $names)) {
            return $notFound;
        }

        // GSU reschedules the booking, not the request: report its new schedule with the same approved actions.
        $rescheduled = Booking::with('facilities')
            ->where('additional_details->form_submission_id', $submission->id)
            ->where('status', 'rescheduled')
            ->orderBy('start_time')
            ->first();

        $isGuestRequest = $submission->requester_type === 'guest';
        $approved = $isGuestRequest && in_array($submission->status, ['approved', 'reserved'], true);
        $canUploadPayment = $approved && $submission->requester_type === 'guest'
            && in_array($submission->payment_status, ['pending_payment', 'not_required'], true);

        return response()->json([
            'status' => $rescheduled ? 'rescheduled' : $submission->status,
            'code'   => $code,
            'venue'  => $rescheduled
                ? ($rescheduled->facilities->pluck('name')->join(', ') ?: null)
                : ($payload['facility_name'] ?? ($payload['facility_name_custom'] ?? null)),
            'date'   => $rescheduled
                ? $rescheduled->start_time?->format('D, M j, Y')
                : $this->dateLabel($payload['date_activity'] ?? null, $payload['date_end'] ?? null),
            'time'   => $rescheduled && $rescheduled->start_time && $rescheduled->end_time
                ? $rescheduled->start_time->format('g:i A') . ' – ' . $rescheduled->end_time->format('g:i A')
                : $this->timeLabel($payload['time_range'] ?? []),
            'payment_status' => $submission->payment_status ?? 'not_required',
            'can_download_pdf' => $approved,
            'pdf_url' => $approved
                ? URL::temporarySignedRoute('guest.forms.facilities.pdf', now()->addMinutes(30), ['submission' => $submission->id], false)
                : null,
            'can_upload_payment' => $canUploadPayment,
            'payment_upload_url' => $canUploadPayment
                ? URL::temporarySignedRoute('ticket.payment.upload', now()->addMinutes(15), ['submission' => $submission->id], false)
                : null,
        ]);
    }

    public function uploadPayment(Request $request, FormSubmission $submission): JsonResponse
    {
        abort_unless(
            $submission->type === 'facilities_utilization' && $submission->requester_type === 'guest',
            404
        );
        abort_unless(in_array($submission->status, ['approved', 'reserved'], true), 403, 'Payment can only be uploaded after approval.');
        abort_unless(
            in_array($submission->payment_status, ['pending_payment', 'not_required'], true),
            409,
            'A payment receipt has already been submitted.'
        );

        $validated = $request->validate([
            'payment_attachment' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'mimetypes:image/jpeg,image/png,application/pdf',
                'max:10240',
            ],
        ]);

        $submission->payment_attachment = $validated['payment_attachment']->store('receipts', 'payment_receipts');
        $submission->payment_status = 'payment_uploaded';
        $submission->save();

        return response()->json([
            'payment_status' => $submission->payment_status,
            'message' => 'Receipt submitted. GSU will verify your payment.',
        ]);
    }

    /** Return maintenance status only after matching the requester's last name. */
    private function repairTicket(string $code, string $lastName): ?JsonResponse
    {
        if (! preg_match('/^RM-[A-Z0-9-]{3,40}$/', $code)) {
            return null;
        }

        $ticket = MaintenanceTicket::with(['requester', 'facility'])->where('ticket_code', $code)->first();

        if (! $ticket) {
            return null;
        }
        if ($lastName === '') {
            return response()->json(['status' => 'needs_last_name']);
        }
        if (! $this->lastNameMatches(
            $lastName,
            array_filter([$ticket->requester?->last_name]),
            array_filter([$ticket->requester?->name])
        )) {
            return null;
        }

        return response()->json([
            'status'  => $ticket->status,
            'code'    => $ticket->ticket_code,
            'venue'   => $ticket->facility?->name ?? $ticket->facility_other,
            'subject' => $ticket->subject,
            'sent'    => ($ticket->requested_at ?? $ticket->created_at)?->format('M j, Y · g:i A'),
        ]);
    }

    private function booking(string $code, string $lastName): ?JsonResponse
    {
        if (! preg_match('/^BKG-[A-Z0-9-]{10,40}$/', $code)) {
            return null;
        }

        $booking = Booking::with(['requester', 'facilities'])
            ->where('booking_code', $code)
            ->first();

        if (! $booking) {
            return null;
        }

        $submissionId = $booking->additional_details['form_submission_id'] ?? null;
        $submission = $submissionId ? FormSubmission::find($submissionId) : null;

        if ($lastName === '') {
            return response()->json(['status' => 'needs_last_name']);
        }
        if (! $this->lastNameMatches(
            $lastName,
            array_filter([
                $booking->requester?->last_name,
                $submission?->payload['requester_last_name'] ?? null,
            ]),
            array_filter([$booking->requester?->name])
        )) {
            return null;
        }

        return response()->json([
            'status' => $booking->status,
            'code' => $booking->booking_code,
            'venue' => $booking->facilities->pluck('name')->join(', ') ?: null,
            'purpose' => $booking->purpose,
            'date' => $booking->start_time?->format('D, M j, Y'),
            'time' => $booking->start_time && $booking->end_time
                ? $booking->start_time->format('g:i A') . ' – ' . $booking->end_time->format('g:i A')
                : null,
        ]);
    }

    private function lastNameMatches(string $input, array $explicitLastNames, array $legacyNames): bool
    {
        $norm = fn ($s) => trim(preg_replace('/\s+/u', ' ', mb_strtolower(str_replace([',', '.'], ' ', (string) $s))));
        $typed = $norm($input);
        if ($typed === '') {
            return false;
        }

        if ($explicitLastNames !== []) {
            foreach ($explicitLastNames as $lastName) {
                if ($typed === $norm($lastName)) {
                    return true;
                }
            }

            return false;
        }

        $dropSuffix = fn ($s) => preg_replace('/\s+(jr|sr|ii|iii|iv|v)$/u', '', $s);
        $typedBare = $dropSuffix($typed);

        foreach ($legacyNames as $name) {
            $full = $norm($name);
            if (str_ends_with(' ' . $full, ' ' . $typed)
                || str_ends_with(' ' . $dropSuffix($full), ' ' . $typedBare)) {
                return true;
            }
        }

        return false;
    }

    private function dateLabel(?string $start, ?string $end): ?string
    {
        try {
            if (! $start) {
                return null;
            }
            $from = Carbon::parse($start);
            if ($end && Carbon::parse($end)->gt($from)) {
                return $from->format('M j') . ' – ' . Carbon::parse($end)->format('M j, Y');
            }

            return $from->format('D, M j, Y');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function timeLabel(array $range): ?string
    {
        $fmt = function ($t) {
            try {
                return $t ? Carbon::createFromFormat('H:i', substr($t, 0, 5))->format('g:i A') : null;
            } catch (\Throwable $e) {
                return null;
            }
        };
        $start = $fmt($range['start'] ?? null);
        $end = $fmt($range['end'] ?? null);

        return $start && $end ? $start . ' – ' . $end : ($start ?: null);
    }
}

<?php

namespace App\Http\Controllers\Traits;

use App\Http\Requests\StoreFacilitiesUtilizationRequest;
use App\Models\Facility;
use App\Models\FormSubmission;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Str;

trait SubmitsFacilitiesForm
{
    abstract protected function requesterType(): string;

    abstract protected function requesterUnit(): string;

    abstract protected function submittedRedirectRoute(): string;

    protected function handleStoreFacilities(StoreFacilitiesUtilizationRequest $request): \Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();
        $validated = $request->validated();

        $facilityIds = array_map('intval', $validated['facility_ids']);
        $facilitiesById = Facility::whereIn('id', $facilityIds)->get()->keyBy('id');
        $facilities = collect($facilityIds)
            ->map(fn (int $facilityId) => $facilitiesById->get($facilityId))
            ->filter()
            ->values();
        $facilityNames = $facilities->pluck('name')->all();

        // Handle payment attachment upload
        $paymentAttachmentPath = null;
        if ($request->hasFile('payment_attachment')) {
            $paymentAttachmentPath = $request->file('payment_attachment')->store('receipts', 'payment_receipts');
        }

        $attachmentPaths = [];
        foreach ($request->file('attachments', []) as $attachment) {
            $attachmentPaths[] = $attachment->store('attachments', 'payment_receipts');
        }

        $payload = [
            'control_no'      => null,
            'date_request'    => now()->toDateString(),
            'requester_name'  => $user->name,
            'requester_last_name' => $validated['last_name'],
            'requester_unit'  => $this->requesterUnit(),
            'date_activity'   => $validated['date_activity'],
            // Last day of a multi-day event; null for single-day requests
            'date_end'        => (!empty($validated['date_end']) && $validated['date_end'] !== $validated['date_activity']) ? $validated['date_end'] : null,
            'time_range'      => [
                'start' => $validated['start_time'],
                'end'   => $validated['end_time'],
            ],
            'facility_id'     => $facilityIds[0],
            'facility_ids'    => $facilityIds,
            'facility_name'   => $facilityNames[0],
            'facility_names'  => $facilityNames,
            'venue_others'    => $validated['venue_others'] ?? '',
            'purpose'         => $validated['purpose'],
            'equipment'       => [
                'monobloc_chair' => (int) $request->input('qty_monobloc', 0),
                'table'          => (int) $request->input('qty_table', 0),
                'electric_fan'   => (int) $request->input('qty_fan', 0),
                'rostrum'        => (int) $request->input('qty_rostrum', 0),
                'flag'           => (int) $request->input('qty_flag', 0),
                'sound'          => (int) $request->input('qty_sound', 0),
                'led'            => (int) $request->input('qty_led', 0),
            ],
        ];

        if ($attachmentPaths) {
            $payload['attachments'] = $attachmentPaths;
        }

        // Determine initial status and payment_status
        $status = $paymentAttachmentPath ? 'pending_payment' : 'pending';
        $paymentStatus = $paymentAttachmentPath ? 'payment_uploaded' : 'not_required';

        $submission = FormSubmission::create([
            'type'           => 'facilities_utilization',
            'requester_id'   => $user->id,
            'requester_type' => $this->requesterType(),
            'requester_unit' => $this->requesterUnit(),
            'status'         => $status,
            'payload'        => $payload,
            'payment_attachment' => $paymentAttachmentPath,
            'payment_status' => $paymentStatus,
        ]);

        $notifications = app(NotificationService::class);
        $notifications->notifyFormSubmitted($user->id, $submission->id);
        $notifications->notifyAdmins(
            'form_pending_admin',
            'New facilities request from ' . $this->requesterType(),
            $this->requesterUnit() . ' submitted a new utilization request.',
            ['submission_id' => $submission->id]
        );

        return redirect()->route($this->submittedRedirectRoute())
            ->with('status', 'Facilities utilization request submitted to GSU.');
    }

    protected function handleIndexFacilities()
    {
        $user = Auth::user();

        return FormSubmission::with('requester')
            ->where('type', 'facilities_utilization')
            ->where('requester_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(10);
    }
}

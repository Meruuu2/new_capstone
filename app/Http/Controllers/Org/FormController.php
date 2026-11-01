<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\SubmitsFacilitiesForm;
use App\Http\Requests\StoreFacilitiesUtilizationRequest;
use App\Models\Facility;
use App\Models\FormSubmission;
use App\Models\Signatory;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FormController extends Controller
{
    use SubmitsFacilitiesForm;

    public function __construct(protected NotificationService $notifications)
    {
    }

    /**
     * Show the Facilities Utilization online request form.
     */
    public function createFacilities()
    {
        $user = Auth::user();

        // Org can request any active facility (including college-owned AVRs).
        $coreFacilities = Facility::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('org.requests.facilities_create', compact(
            'coreFacilities',
            'user'
        ));

    }

    /**
     * Store Facilities Utilization request as a JSON form submission.
     *
     * Delegates core creation/notifications to SubmitsFacilitiesForm, then enriches the submission
     * payload with extra "Noted by" fields so we don't lose the signatory feature.
     */
    public function storeFacilities(StoreFacilitiesUtilizationRequest $request)
    {
        $user = Auth::user();

        // "Noted by" is required: a college Dean or Program Head (type their name), or someone else (type position + name)
        $request->validate([
            'noted_signatory_id'       => ['required', 'in:dean,program_head,custom'],
            'noted_signatory_name'     => ['required', 'string', 'max:255'],
            'noted_signatory_position' => ['nullable', 'required_if:noted_signatory_id,custom', 'string', 'max:120'],
        ], [
            'noted_signatory_id.required'          => 'Choose who will note your request: the Dean, a Program Head, or someone else.',
            'noted_signatory_id.in'                => 'Choose who will note your request: the Dean, a Program Head, or someone else.',
            'noted_signatory_name.required'        => 'Type the name of the person who will note your request.',
            'noted_signatory_position.required_if' => 'Type the position of the person who will note your request.',
        ]);

        $notedName = trim((string) $request->input('noted_signatory_name'));
        $notedPosition = match ($request->input('noted_signatory_id')) {
            'dean'         => 'Dean',
            'program_head' => 'Program Head',
            default        => trim((string) $request->input('noted_signatory_position')),
        };

        $notedDatetime = $notedName ? now()->format('Y-m-d H:i:s') : null;

        $response = $this->handleStoreFacilities($request);


        $submission = FormSubmission::where('requester_id', $user->id)
            ->where('type', 'facilities_utilization')
            ->latest('id')
            ->first();

        if ($submission) {
            $payload = $submission->payload ?? [];
            $payload['noted_signatory_name']     = $notedName;
            $payload['noted_signatory_position'] = $notedPosition;
            $payload['noted_datetime']           = $notedDatetime;
            $submission->payload = $payload;
            $submission->save();
        }

        return $response;
    }

    protected function requesterType(): string
    {
        return 'org';
    }

    protected function requesterUnit(): string
    {
        return Auth::user()->organization_name;
    }

    protected function submittedRedirectRoute(): string
    {
        // After submitting a facilities request, go back to "My requests"
        return 'org.requests.facilities.index';
    }

    /**
     * List the current org staff member's requests, with filters.
     */
    public function indexFacilities(Request $request)
    {
        $user = Auth::user();
        $query = FormSubmission::with('requester')
            ->where('type', 'facilities_utilization')
            ->where('requester_id', $user->id);

        // Filter: control number (payload->control_no)
        if ($search = request('search')) {
            $query->whereRaw("JSON_EXTRACT(payload, '$.control_no') LIKE ?", ['%'.$search.'%']);
        }

        // Filter: status
        if ($status = request('status')) {
            $query->where('status', $status);
        }

        // Filter: activity date (payload->date_activity)
        if ($activityDate = request('activity_date')) {
            $query->whereRaw("JSON_EXTRACT(payload, '$.date_activity') = ?", [$activityDate]);
        }

        // Order: control_no first (non-null), then created_at desc
        $submissions = $query
            ->orderByRaw("CASE WHEN JSON_EXTRACT(payload, '$.control_no') IS NULL THEN 1 ELSE 0 END ASC")
            ->orderByRaw("JSON_EXTRACT(payload, '$.control_no') ASC")
            ->orderByDesc('created_at')
            ->paginate(15)
            ->appends(request()->query());

        return view('org.requests.facilities_index', compact('submissions'));
    }

    /**
     * Show a single facilities utilization request for this org staff user.
     */
    public function showFacilities(FormSubmission $submission)
    {
        $user = Auth::user();

        if (
            $submission->type !== 'facilities_utilization' ||
            $submission->requester_id !== $user->id
        ) {
            abort(404);
        }

        $payload = $submission->payload ?? [];
        $facilityIds = $payload['facility_ids'] ?? (isset($payload['facility_id']) ? [$payload['facility_id']] : []);
        $facilityById = Facility::whereIn('id', $facilityIds)->get()->keyBy('id');
        $facilities = collect($facilityIds)->map(fn ($id) => $facilityById->get($id))->filter()->values();

        return view('org.requests.facilities_show', compact('submission', 'payload', 'facilities'));
    }

    /**
     * Cancel a pending facilities utilization request (org side).
     */
    public function cancelFacilities(FormSubmission $submission)
    {
        $user = Auth::user();

        if (
            $submission->type !== 'facilities_utilization' ||
            $submission->requester_id !== $user->id
        ) {
            abort(404);
        }

        if ($submission->status !== 'pending') {
            return back()->withErrors([
                'status' => 'Only pending requests can be cancelled.',
            ]);
        }

        $submission->markCancelled();

        return redirect()
            ->route('org.requests.facilities.index')
            ->with('status', 'Request cancelled.');
    }
}

<?php

namespace App\Http\Controllers\College;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\SubmitsFacilitiesForm;
use App\Http\Requests\StoreFacilitiesUtilizationRequest;
use App\Models\Facility;
use App\Models\FormSubmission;
use App\Models\Signatory;
use App\Models\User;
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

        // Facility request dropdown:
        // - allow all GSU-owned and College-owned facilities
        $coreFacilities = Facility::where('is_active', true)
            ->where(function ($q) {
                $q->where('owner_type', 'gsu')
                  ->orWhere('owner_type', 'college');
            })
            ->orderBy('name')
            ->get();

        // Also get a list of facilities specific to the user's college to display as a hint
        $collegeFacilities = Facility::where('is_active', true)
            ->where('owner_type', 'college')
            ->where('owner_college', $user->college_name)
            ->orderBy('name')
            ->get();

        // Signatories (dean + program heads) for this college
        $deans = Signatory::where('type', 'dean')
            ->forCollege($user->college_id, $user->college_name)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $programHeads = Signatory::where('type', 'program_head')
            ->forCollege($user->college_id, $user->college_name)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('college.requests.facilities_create', compact(
            'coreFacilities',
            'collegeFacilities',
            'user',
            'deans',
            'programHeads'
        ) + ['teamSignatories' => $this->teamSignatories()]);

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

        $notedName = null;
        if ($request->filled('noted_signatory_id') && $request->input('noted_signatory_id') !== 'custom') {
            [$type, $id] = explode(':', $request->input('noted_signatory_id')) + [null, null];
            if ($id && $type === 'user') {
                // A member of the college's team (Users page) who holds a position
                $notedName = $this->teamSignatories()->firstWhere('id', (int) $id)?->name;
            } elseif ($id) {
                $signatory = Signatory::find($id);
                if ($signatory) {
                    $notedName = $signatory->name;
                }
            }
        } elseif ($request->filled('noted_signatory_custom')) {
            $notedName = $request->input('noted_signatory_custom');
        }

        $notedDatetime = $notedName ? now()->format('Y-m-d H:i:s') : null;

        $response = $this->handleStoreFacilities($request);

        $submission = FormSubmission::where('requester_id', $user->id)
            ->where('type', 'facilities_utilization')
            ->latest('id')
            ->first();

        if ($submission) {
            $payload = $submission->payload ?? [];
            $payload['noted_signatory_name'] = $notedName;
            $payload['noted_datetime']       = $notedDatetime;
            $submission->payload = $payload;
            $submission->save();
        }

        return $response;
    }

    /**
     * "Noted by" choices from the college's own team (Users page): accounts with a position, Dean first.
     */
    protected function teamSignatories()
    {
        $user = Auth::user();
        $rank = ['dean' => 0, 'program head' => 1];

        return User::where('role', 'college_staff')
            ->where(fn ($q) => $user->college_id
                ? $q->where('college_id', $user->college_id)
                : $q->where('college_name', $user->college_name))
            ->whereNotNull('position')
            ->where('position', '!=', '')
            ->orderBy('name')
            ->get(['id', 'name', 'position'])
            ->sortBy(fn ($u) => $rank[strtolower(trim($u->position))] ?? 9)
            ->values();
    }

    protected function requesterType(): string
    {
        return 'college';
    }

    protected function requesterUnit(): string
    {
        return Auth::user()->college_name;
    }

    protected function submittedRedirectRoute(): string
    {
        return 'college.dashboard';
    }

    /**
     * List the current college staff member's requests, with filters.
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
            if ($status === 'approved') {
                $query->whereIn('status', ['approved', 'reserved']);
            } else {
                $query->where('status', $status);
            }
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

        return view('college.requests.facilities_index', compact('submissions'));
    }

    /**
     * Show a single facilities utilization request for this college staff user.
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

        return view('college.requests.facilities_show', compact('submission', 'payload', 'facilities'));
    }

    /**
     * Cancel a pending facilities utilization request (college side).
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
            ->route('college.requests.index')
            ->with('status', 'Request cancelled.');
    }
}
<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\SubmitsFacilitiesForm;
use App\Http\Requests\StoreFacilitiesUtilizationRequest;
use App\Models\Facility;
use App\Models\FormSubmission;
use App\Models\Signatory;
use Illuminate\Http\Request;

class FormController extends Controller
{
    use SubmitsFacilitiesForm;

    protected function requesterType(): string
    {
        return 'guest';
    }

    protected function requesterUnit(): string
    {
        return auth()->user()->organization_name ?? 'Guest User';
    }

    protected function submittedRedirectRoute(): string
    {
        return 'guest.requests.facilities.index';
    }

    public function createFacilities()
    {
        $coreFacilities = Facility::where('is_active', true)
            ->whereNull('college_id')
            ->orderBy('name')
            ->get();

        // Guests don't have signatories, they fill custom names
        $deans = collect();
        $programHeads = collect();
        $presidents = collect();
        $advisers = collect();

        return view('guest.requests.facilities_create', compact(
            'coreFacilities',
            'deans',
            'programHeads',
            'presidents',
            'advisers'
        ));
    }

    public function storeFacilities(StoreFacilitiesUtilizationRequest $request)
    {
        return $this->handleStoreFacilities($request);
    }

    public function indexFacilities()
    {
        $submissions = $this->handleIndexFacilities();

        return view('guest.requests.facilities_index', ['submissions' => $submissions]);
    }

    public function showFacilities(FormSubmission $submission)
    {
        // Ensure guest can only view their own submissions
        if ($submission->requester_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $payload = $submission->payload ?? [];
        $facilityIds = $payload['facility_ids'] ?? (isset($payload['facility_id']) ? [$payload['facility_id']] : []);
        $facilityById = Facility::whereIn('id', $facilityIds)->get()->keyBy('id');
        $facilities = collect($facilityIds)->map(fn ($id) => $facilityById->get($id))->filter()->values();
        $facility = $facilities->first();

        return view('guest.requests.facilities_show', compact('submission', 'payload', 'facility', 'facilities'));
    }

    public function cancelFacilities(FormSubmission $submission)
    {
        // Ensure guest can only cancel their own submissions
        if ($submission->requester_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        if (!in_array($submission->status, ['pending', 'pending_payment'], true)) {
            return back()->withErrors(['status' => 'Only pending requests can be cancelled.']);
        }

        $submission->markCancelled();

        return redirect()->route('guest.requests.facilities.index')
            ->with('status', 'Request cancelled successfully.');
    }
}

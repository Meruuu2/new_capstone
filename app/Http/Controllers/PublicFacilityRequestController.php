<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\SubmitsFacilitiesForm;
use App\Http\Requests\StoreFacilitiesUtilizationRequest;
use App\Mail\GuestRequestTrackingNumber;
use App\Models\Facility;
use App\Models\FormSubmission;
use App\Models\User;
use App\Rules\PersonName;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

/**
 * Facility requests from guests (outsiders) without an account.
 *
 * Requests are filed under one shared "Walk-in guest" system account (nobody signs in with it),
 * because every form submission must belong to a user. The guest's own name, contact, address,
 * organization and "Noted by" are saved with the request, and they follow up with the ticket code.
 */
class PublicFacilityRequestController extends Controller
{
    use SubmitsFacilitiesForm;

    private const WALK_IN_EMAIL = 'walk-in-guest@ua-fms.local';

    public function create()
    {
        $coreFacilities = Facility::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('public.request-facility', compact('coreFacilities'));
    }

    public function store(StoreFacilitiesUtilizationRequest $request)
    {
        // Spam trap: real visitors never see or fill the "website" field
        if ($request->filled('website')) {
            return redirect()->route('public.request.create');
        }

        $guest = $request->validate([
            'client_name'            => ['required', 'string', 'max:120', new PersonName],
            'last_name'              => ['required', 'string', 'max:120', new PersonName],
            'contact_number'         => 'required|string|max:40',
            'email'                  => 'required|email|max:160',
            'address'                => 'required|string|max:255',
            'organization'           => 'required|string|max:160',
            'noted_signatory_id'     => 'required|in:custom',
            'noted_signatory_custom' => 'required_if:noted_signatory_id,custom|string|max:255',
        ]);

        $walkIn = User::firstOrCreate(
            ['email' => self::WALK_IN_EMAIL],
            ['name' => 'Walk-in Guest', 'role' => 'guest', 'password' => Hash::make(Str::random(40))]
        );

        // File the request under the walk-in account for this request only (nobody is signed in)
        $lastId = (int) FormSubmission::max('id');
        Auth::setUser($walkIn);
        $this->handleStoreFacilities($request);
        session()->forget('status'); // the ticket page replaces the generic "submitted" toast

        $submission = FormSubmission::where('requester_id', $walkIn->id)
            ->where('type', 'facilities_utilization')
            ->where('id', '>', $lastId)
            ->latest('id')
            ->firstOrFail();

        // Save who actually asked (the shared submit code only knows the walk-in account)
        $payload = $submission->payload ?? [];
        $payload['requester_name']       = $guest['client_name'];
        $payload['requester_last_name']  = $guest['last_name'];
        $payload['requester_unit']       = $guest['organization'];
        $payload['client_name']          = $guest['client_name'];
        $payload['contact_number']       = $guest['contact_number'];
        $payload['email']                = $guest['email'];
        $payload['address']              = $guest['address'];
        $payload['organization']         = $guest['organization'];
        $payload['noted_signatory_id']   = $guest['noted_signatory_id'];
        $payload['noted_signatory_name'] = $guest['noted_signatory_custom'];
        $payload['noted_datetime']       = now()->format('Y-m-d H:i:s');
        $submission->payload        = $payload;
        $submission->requester_unit = $guest['organization'];
        $submission->save();

        // Only this visitor may open the ticket page for this request
        session()->push('public_ticket_ids', $submission->id);

        $trackingCode = 'FM-' . ($submission->created_at?->format('Y') ?? now()->format('Y')) . '-'
            . str_pad((string) $submission->id, 5, '0', STR_PAD_LEFT);
        $lastName = $guest['last_name'];
        $emailSent = false;

        try {
            Mail::to($guest['email'])->send(new GuestRequestTrackingNumber(
                $guest['client_name'],
                $trackingCode,
                $lastName
            ));
            $emailSent = true;
        } catch (Throwable $exception) {
            report($exception);
        }

        session()->flash('tracking_email_sent', $emailSent);

        return redirect()->route('public.request.submitted', $submission);
    }

    public function submitted(FormSubmission $submission)
    {
        if (! in_array($submission->id, session('public_ticket_ids', []), true)) {
            abort(404);
        }

        return view('public.request-submitted', compact('submission'));
    }

    protected function requesterType(): string
    {
        return 'guest';
    }

    protected function requesterUnit(): string
    {
        return (string) request()->input('organization', 'Guest');
    }

    protected function submittedRedirectRoute(): string
    {
        return 'public.request.create'; // not used: store() redirects to the ticket page itself
    }
}

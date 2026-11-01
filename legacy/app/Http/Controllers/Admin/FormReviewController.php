<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormSubmission;
use App\Models\Booking;
use App\Models\Facility;
use App\Models\Facility;
use Illuminate\Http\Request;
 
class FormReviewController extends Controller
{
    public function index()
    {
        $submissions = FormSubmission::orderBy('created_at', 'desc')->get();
        return view('admin.forms.index', compact('submissions'));
    }

    public function show($id)
    {
        $submission = FormSubmission::findOrFail($id);
        return view('admin.forms.show', compact('submission'));
    }

    public function approve(Request $request, $id)
    {
        $submission = FormSubmission::findOrFail($id);
        
        $submission->update([
            'status' => 'approved',
            'payload' => array_merge($submission->payload, [
                'control_no' => $request->input('control_no', 'GSU-' . str_pad($submission->id, 6, '0', STR_PAD_LEFT)),
            ]),
        ]);

        return redirect()->route('admin.forms.index')->with('success', 'Form approved. Requester informed to proceed to GSU office for signing.');
    }

    public function disapprove(Request $request, $id)
    {
        $submission = FormSubmission::findOrFail($id);
        
        $submission->update([
            'status' => 'disapproved',
            'payload' => array_merge($submission->payload, [
                'disapproval_reason' => $request->input('reason'),
            ]),
        ]);

        return redirect()->route('admin.forms.index')->with('success', 'Form disapproved.');
    }

    public function setBooking($id)
    {
        $submission = FormSubmission::findOrFail($id);

        if ($submission->status !== 'approved') {
            return redirect()->back()->with('error', 'Only approved forms can be converted to bookings.');
        }

        $payload = $submission->payload;
        
        // Check for booking concurrency
        $facilityId = $payload['facility_id'];
        $dateActivity = $payload['date_activity'];
        $startTime = $payload['time_range']['start'];
        $endTime = $payload['time_range']['end'];

        $hasConflict = Booking::where('facility_id', $facilityId)
            ->where('date', $dateActivity)
            ->where('status', 'approved')
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<=', $startTime)
                      ->where('end_time', '>', $startTime);
                })->orWhere(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime)
                      ->where('end_time', '>=', $endTime);
                });
            })
            ->exists();

        if ($hasConflict) {
            return redirect()->back()->with('error', 'Time slot conflict detected. Cannot create booking.');
        }

        // Check facility availability status
        $facility = Facility::find($facilityId);
        if ($facility && in_array($facility->availability_status, ['maintenance', 'unavailable'])) {
            return redirect()->back()->with('error', 'Facility is not available for booking.');
        }

        Booking::create([
            'user_id' => $submission->requester_id,
            'facility_id' => $facilityId,
            'date' => $dateActivity,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'purpose' => $payload['purpose'],
            'status' => 'approved',
            'additional_details' => [
                'form_submission_id' => $submission->id,
                'equipment' => $payload['equipment'],
                'control_no' => $payload['control_no'],
            ],
        ]);

        $submission->update(['status' => 'converted']);

        return redirect()->route('admin.forms.index')->with('success', 'Booking created successfully.');
    }

    public function cancel($id)
    {
        $submission = FormSubmission::findOrFail($id);
        $submission->update(['status' => 'cancelled']);

        return redirect()->route('admin.forms.index')->with('success', 'Form cancelled.');
    }
}

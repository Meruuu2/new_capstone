<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\FormSubmission;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AccountDeactivationService
{
    /**
     * @return array{already_deactivated: bool, bookings: int, requests: int}
     */
    public function deactivate(User $user): array
    {
        return DB::transaction(function () use ($user) {
            $user = User::query()->lockForUpdate()->findOrFail($user->id);
            if (! $user->is_active) {
                return ['already_deactivated' => true, 'bookings' => 0, 'requests' => 0];
            }

            $now = now();
            $bookings = Booking::query()
                ->where('requester_id', $user->id)
                ->whereIn('status', ['pending', 'reserved', 'rescheduled', 'booked'])
                ->where('end_time', '>', $now)
                ->lockForUpdate()
                ->get();

            $submissionIds = [];
            foreach ($bookings as $booking) {
                $booking->markCancelled('Requester account deactivated', 'System');
                $booking->save();

                $submissionId = $booking->additional_details['form_submission_id'] ?? null;
                if ($submissionId) {
                    $submissionIds[] = (int) $submissionId;
                }
            }

            $submissions = FormSubmission::query()
                ->where('requester_id', $user->id)
                ->where('type', 'facilities_utilization')
                ->whereNotIn('status', ['cancelled', 'disapproved', 'completed', 'failed'])
                ->lockForUpdate()
                ->get()
                ->filter(fn (FormSubmission $submission) =>
                    in_array($submission->id, $submissionIds, true)
                    || $submission->isCancellableOnAccountDeactivation($now)
                );

            foreach ($submissions as $submission) {
                $submission->markCancelled();
            }

            $user->is_active = false;
            $user->save();

            return [
                'already_deactivated' => false,
                'bookings' => $bookings->count(),
                'requests' => $submissions->count(),
            ];
        });
    }
}

<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    public function notifyUser(int $userId, string $type, string $title, string $message, array $data = []): Notification
    {
        return Notification::create([
            'user_id' => $userId,
            'type'    => $type,
            'title'   => $title,
            'message' => $message,
            'data'    => $data,
        ]);
    }

    public function notifyAdmins(string $type, string $title, string $message, array $data = []): void
    {
        User::where('role', 'admin')->each(function (User $admin) use ($type, $title, $message, $data) {
            $this->notifyUser($admin->id, $type, $title, $message, $data);
        });
    }

    public function notifyAdminsExcept(int $excludeUserId, string $type, string $title, string $message, array $data = []): void
    {
        User::where('role', 'admin')
            ->where('id', '!=', $excludeUserId)
            ->each(function (User $admin) use ($type, $title, $message, $data) {
                $this->notifyUser($admin->id, $type, $title, $message, $data);
            });
    }

    public function notifyFormSubmitted(int $requesterId, int $submissionId): void
    {
        $this->notifyUser(
            $requesterId,
            'form_pending',
            'Facilities utilization request submitted',
            'Your request has been sent to GSU for review.',
            ['submission_id' => $submissionId]
        );
    }

    public function notifyFormApproved(int $requesterId, int $submissionId): void
    {
        $this->notifyUser(
            $requesterId,
            'form_approved',
            'Facilities utilization request approved',
            'Please proceed to the GSU office to sign and finalize the form.',
            ['submission_id' => $submissionId]
        );
    }

    public function notifyFormDisapproved(int $requesterId, int $submissionId, ?string $reason = null): void
    {
        $message = 'Your request was disapproved by GSU.';
        if ($reason) {
            $message .= ' Reason: ' . $reason;
        }

        $this->notifyUser(
            $requesterId,
            'form_disapproved',
            'Facilities utilization request disapproved',
            $message,
            ['submission_id' => $submissionId]
        );
    }

    public function notifyBookingCreated(int $requesterId, int $submissionId, int $bookingId, string $bookingCode): void
    {
        $this->notifyUser(
            $requesterId,
            'booking_created',
            'Booking confirmed',
            "Your facilities utilization request has been booked ({$bookingCode}).",

            ['submission_id' => $submissionId, 'booking_id' => $bookingId]
        );
    }

    public function notifyBookingRescheduled(int $requesterId, string $bookingCode, string $reason, int $bookingId, int $facilityId): void
    {
        $this->notifyUser(
            $requesterId,
            'booking_rescheduled',
            'Booking updated by GSU',
            "GSU has changed your booking ({$bookingCode}). Reason: {$reason}",
            ['booking_id' => $bookingId, 'facility_id' => $facilityId]
        );
    }

    public function notifyBookingPreempted(int $requesterId, int $bookingId, string $bookingCode): void
    {
        $this->notifyUser(
            $requesterId,
            'booking_preempted',
            'Urgent: Your Booking Has Been Preempted',
            "Your booking ({$bookingCode}) has been preempted by a high-priority event. Please contact the GSU office to reschedule.",
            ['booking_id' => $bookingId]
        );
    }

    public function notifyBookingCancelled(int $requesterId, string $bookingCode, string $reason, int $bookingId): void
    {
        $this->notifyUser(
            $requesterId,
            'booking_cancelled',
            'Booking cancelled by GSU',
            "Your booking ({$bookingCode}) was cancelled by GSU. Reason: {$reason}",
            ['booking_id' => $bookingId]
        );
    }

    public function notifyAdminsOfBookingUpdate(int $bookingId, string $bookingCode, string $adminName): void
    {
        $this->notifyAdminsExcept(
            auth()->id(),
            'booking_rescheduled_admin',
            'Booking updated',
            "Booking {$bookingCode} was rescheduled by {$adminName}.",
            ['booking_id' => $bookingId]
        );
    }

    public function notifyAdminsOfBookingCancellation(int $bookingId, string $bookingCode, string $adminName): void
    {
        $this->notifyAdminsExcept(
            auth()->id(),
            'booking_cancelled_admin',
            'Booking cancelled',
            "Booking {$bookingCode} was cancelled by {$adminName}.",
            ['booking_id' => $bookingId]
        );
    }
}
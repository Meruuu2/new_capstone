<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',           // e.g. 'facilities_utilization', 'repair_maintenance'
        'requester_id',
        'requester_type', // 'college', 'org', 'admin'
        'requester_unit', // e.g. college_name or organization_name
        'status',         // 'pending', 'approved', 'disapproved', 'cancelled', 'reserved', 'pending_payment'
        'payload',        // JSON string
        'payment_attachment',
        'payment_status', // 'not_required', 'pending_payment', 'payment_uploaded', 'payment_verified'
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isFacilitiesUtilization(): bool
    {
        return $this->type === 'facilities_utilization';
    }

    public function markApproved(): void
    {
        $this->status = 'approved';
        $this->save();
    }

    public function markDisapproved(): void
    {
        $this->status = 'disapproved';
        $this->save();
    }

    public function markBooked(): void
    {
        $this->status = 'reserved';
        $this->save();
    }

    // Backward-compat (if older code still references converted)
    public function markConverted(): void
    {
        $this->status = 'reserved';
        $this->save();
    }


    public function markCancelled(): void
    {
        $this->status = 'cancelled';
        $this->save();
    }

    public function isCancellableOnAccountDeactivation(Carbon $at): bool
    {
        if (in_array($this->status, ['cancelled', 'disapproved', 'completed', 'failed'], true)) {
            return false;
        }

        $payload = $this->payload ?? [];
        $date = $payload['date_end'] ?? $payload['date_activity'] ?? null;
        if (! is_string($date) || ! Carbon::hasFormat($date, 'Y-m-d')) {
            return false;
        }

        $endTime = $payload['time_range']['end'] ?? null;
        if ($endTime === null || $endTime === '') {
            return Carbon::parse($date)->endOfDay()->gt($at);
        }
        if (! is_string($endTime) || ! Carbon::hasFormat($endTime, 'H:i')) {
            return false;
        }

        return Carbon::parse($date . ' ' . $endTime)->gt($at);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function requiresPayment(): bool
    {
        return $this->payment_status !== 'not_required';
    }

    public function hasPaymentUploaded(): bool
    {
        return $this->payment_status === 'payment_uploaded' || $this->payment_status === 'payment_verified';
    }

    public function isPaymentVerified(): bool
    {
        return $this->payment_status === 'payment_verified';
    }

    public function markPendingPayment(): void
    {
        $this->payment_status = 'pending_payment';
        $this->status = 'pending_payment';
        $this->save();
    }

    public function markPaymentVerified(): void
    {
        $this->payment_status = 'payment_verified';
        $this->save();
    }
}
 
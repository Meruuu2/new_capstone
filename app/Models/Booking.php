<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;
 
    protected $fillable = [
        'requester_id',
        'start_time',
        'end_time',
        'requester_type',
        'requester_unit',
        'status',
        'request_method',
        'purpose',
        'additional_details',
        'requested_at',
        'approved_at',
        'booking_code',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'additional_details' => 'array',
    ];

    public function markRescheduled(array $equipment, string $reason, string $rescheduledBy): void
    {
        $details = $this->additional_details ?? [];

        $details['equipment'] = $equipment;
        $details['reschedule_note'] = $reason;
        $details['rescheduled_at'] = now()->toDateTimeString();
        $details['rescheduled_by'] = $rescheduledBy;

        $this->status = 'rescheduled';
        $this->additional_details = $details;
    }

    public function markCompleted(): void
    {
        if ($this->end_time && $this->end_time->isPast()) {
            $this->status = 'completed';
        }
    }

    public function markCancelled(string $reason, string $cancelledBy): void
    {
        $details = $this->additional_details ?? [];

        $details['cancel_reason'] = $reason;
        $details['cancelled_by'] = $cancelledBy;
        $details['cancelled_at'] = now()->toDateTimeString();

        $this->status = 'cancelled';
        $this->additional_details = $details;
    }

    public function isCancellableOnAccountDeactivation(Carbon $at): bool
    {
        return in_array($this->status, ['pending', 'reserved', 'rescheduled', 'booked'], true)
            && $this->end_time !== null
            && $this->end_time->gt($at);
    }

    public function scopeOverlapping($query, array $facilityIds, $startTime, $endTime)
    {
        return $query->whereHas('facilities', function ($q) use ($facilityIds) {
            $q->whereIn('facility_id', $facilityIds);
        })
            ->whereIn('status', ['reserved', 'rescheduled', 'booked'])
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function facilities()
    {
        return $this->belongsToMany(Facility::class, 'booking_facility');
    }

    public function maintenanceTickets()
    {
        return $this->hasMany(MaintenanceTicket::class);
    }

    public function scopeMarkCompleted($query)
    {
        return $query->where('status', 'reserved')
            ->where('end_time', '<', now())
            ->update(['status' => 'completed']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
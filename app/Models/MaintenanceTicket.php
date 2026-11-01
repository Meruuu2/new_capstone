<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_code',
        'facility_id',
        'facility_other',
        'requester_id',
        'booking_id',
        'request_method',
        'subject',
        'status',
        'issue_description',
        'admin_remarks',
        'requested_at',
        'approved_at',
        'completed_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function messages()
    {
        return $this->hasMany(MaintenanceTicketMessage::class)->orderBy('id');
    }

    /** Success / Failed tickets are closed: their conversation is read-only. */
    public function isClosed(): bool
    {
        return in_array($this->status, ['success', 'failed'], true);
    }

    public function staffAssignments()
    {
        return $this->hasMany(StaffAssignment::class);
    }

    public function maintenanceLogs()
    {
        return $this->hasMany(MaintenanceLog::class);
    }
} 
 
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'maintenance_ticket_id',
        'staff_id',
        'assigned_at',
        'preferred_time',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'preferred_time' => 'datetime',
    ];

    public function maintenanceTicket()
    {
        return $this->belongsTo(MaintenanceTicket::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}

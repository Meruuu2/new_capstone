<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'maintenance_ticket_id',
        'staff_id',
        'work_done',
        'remarks',
        'logged_at',
        'staff_signature',
    ];

    protected $casts = [
        'logged_at' => 'datetime',
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
  
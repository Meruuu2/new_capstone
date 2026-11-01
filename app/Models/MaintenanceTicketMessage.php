<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceTicketMessage extends Model
{
    protected $fillable = [
        'maintenance_ticket_id',
        'user_id',
        'body',
    ];

    public function ticket()
    {
        return $this->belongsTo(MaintenanceTicket::class, 'maintenance_ticket_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

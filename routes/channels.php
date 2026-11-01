<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\MaintenanceTicket;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('repair-ticket.{ticketId}', function ($user, int $ticketId) {
    $ticket = MaintenanceTicket::with('requester')->find($ticketId);
    if (! $ticket) {
        return false;
    }

    return $user->isAdmin()
        || ($user->isCollegeStaff()
            && $ticket->requester?->isCollegeStaff()
            && in_array($user->id, $ticket->requester->collegeTeamIds(), true));
});

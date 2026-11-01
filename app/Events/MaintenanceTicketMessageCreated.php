<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MaintenanceTicketMessageCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $ticketId,
        public readonly int $messageId,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('repair-ticket.' . $this->ticketId)];
    }

    public function broadcastAs(): string
    {
        return 'repair-ticket.message-created';
    }

    public function broadcastWith(): array
    {
        return [
            'ticket_id' => $this->ticketId,
            'message_id' => $this->messageId,
        ];
    }
}

<?php

namespace App\Http\Controllers\Traits;

use App\Events\MaintenanceTicketMessageCreated;
use App\Models\MaintenanceTicket;
use App\Models\MaintenanceTicketMessage;
use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Repair ticket conversation between the admins and the college staff who sent the ticket.
 * Used by Admin\MaintenanceTicketController and College\MaintenanceTicketController.
 *
 * messages():    JSON list of messages newer than ?after=<id> (the page polls this every few seconds)
 * sendMessage(): adds a message (JSON for the page script, redirect for a plain form post) and notifies the other side
 * Closed tickets (Success / Failed) are read-only.
 */
trait HandlesTicketMessages
{
    /** 404 unless the signed-in user may see this ticket's conversation. */
    abstract protected function authorizeConversation(MaintenanceTicket $ticket): void;

    /** Tell the other side about a new message. */
    abstract protected function notifyNewMessage(MaintenanceTicket $ticket, MaintenanceTicketMessage $message): void;

    /** Route to the ticket page on this side (fallback redirect for non-JS posts). */
    abstract protected function ticketRoute(): string;

    public function messages(Request $request, MaintenanceTicket $ticket)
    {
        $this->authorizeConversation($ticket);

        $messages = $ticket->messages()
            ->with('user')
            ->where('id', '>', (int) $request->query('after', 0))
            ->get()
            ->map(fn ($m) => $this->messageJson($m));

        return response()->json([
            'messages' => $messages,
            'closed'   => $ticket->isClosed(),
            'status'   => $ticket->status,
        ]);
    }

    public function sendMessage(Request $request, MaintenanceTicket $ticket)
    {
        $this->authorizeConversation($ticket);

        if ($ticket->isClosed()) {
            $error = 'This ticket is closed, so the conversation is read-only.';

            return $request->expectsJson()
                ? response()->json(['message' => $error], 422)
                : back()->with('error', $error);
        }

        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ], [
            'body.required' => 'Type a message first.',
        ]);

        $message = $ticket->messages()->create([
            'user_id' => auth()->id(),
            'body'    => trim($data['body']),
        ]);
        $message->setRelation('user', auth()->user());

        $this->notifyNewMessage($ticket, $message);
        try {
            broadcast(new MaintenanceTicketMessageCreated($ticket->id, $message->id));
        } catch (BroadcastException $exception) {
            Log::warning('Repair ticket message broadcast failed; polling remains available.', [
                'ticket_id' => $ticket->id,
                'message_id' => $message->id,
                'exception' => $exception::class,
            ]);
        }

        return $request->expectsJson()
            ? response()->json(['message' => $this->messageJson($message)], 201)
            : redirect()->route($this->ticketRoute(), $ticket)->withFragment('conversation');
    }

    protected function messageJson(MaintenanceTicketMessage $m): array
    {
        $user = $m->user;

        return [
            'id'      => $m->id,
            'body'    => $m->body,
            'mine'    => $m->user_id === auth()->id(),
            'author'  => $user?->name ?? 'Deleted account',
            'side'    => $user?->isAdmin() ? 'admin' : 'college',
            'role'    => $user?->isAdmin() ? ($user->position ?: 'GSU Admin') : ($user?->college_name ?: 'College staff'),
            'time'    => $m->created_at?->format('M j, g:i A'),
            'iso'     => $m->created_at?->toIso8601String(),
        ];
    }

    protected function messagePreview(MaintenanceTicketMessage $m): string
    {
        return mb_strimwidth($m->body, 0, 120, '…');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HandlesTicketMessages;
use App\Models\MaintenanceTicket;
use App\Models\MaintenanceTicketMessage;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MaintenanceTicketController extends Controller
{
    use HandlesTicketMessages;

    private const ACTIVE_STATUSES = ['notified', 'pending', 'ongoing'];

    private const COMPLETED_STATUSES = ['success', 'failed'];

    public function __construct(protected NotificationService $notifications)
    {
    }

    public function index()
    {
        $activeTickets = MaintenanceTicket::with(['requester', 'facility'])
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->latest()
            ->paginate(15, ['*'], 'active_page');

        $completedTickets = MaintenanceTicket::with(['requester', 'facility'])
            ->whereIn('status', self::COMPLETED_STATUSES)
            ->latest('completed_at')
            ->paginate(15, ['*'], 'completed_page');

        return view('admin.maintenance-tickets.index', compact('activeTickets', 'completedTickets'));
    }

    public function show(MaintenanceTicket $ticket)
    {
        $ticket->load(['requester', 'facility', 'messages.user']);

        return view('admin.maintenance-tickets.show', compact('ticket'));
    }

    /** The conversation is with college staff only (org tickets have no thread). */
    protected function authorizeConversation(MaintenanceTicket $ticket): void
    {
        abort_unless($ticket->requester?->isCollegeStaff(), 404);
    }

    protected function notifyNewMessage(MaintenanceTicket $ticket, MaintenanceTicketMessage $message): void
    {
        // Repair tickets are shared within the college, so the whole college hears the reply
        foreach ($ticket->requester->collegeTeamIds() as $memberId) {
            $this->notifications->notifyUser(
                $memberId,
                'maintenance_ticket_message',
                'New reply on a repair request',
                auth()->user()->name . " replied on {$ticket->ticket_code} ({$ticket->subject}): " . $this->messagePreview($message),
                ['ticket_id' => $ticket->id, 'ticket_code' => $ticket->ticket_code, 'message_id' => $message->id]
            );
        }
    }

    protected function ticketRoute(): string
    {
        return 'admin.maintenance-tickets.show';
    }

    public function update(Request $request, MaintenanceTicket $ticket)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'ongoing', 'success', 'failed'])],
            'admin_remarks' => ['nullable', 'string', 'max:5000'],
        ]);

        $statusChanged = $ticket->status !== $validated['status'];
        $remarksChanged = trim((string) $ticket->admin_remarks) !== trim((string) ($validated['admin_remarks'] ?? ''));
        $ticket->status = $validated['status'];
        $ticket->admin_remarks = $validated['admin_remarks'] ?? null;
        $ticket->completed_at = in_array($ticket->status, self::COMPLETED_STATUSES, true) ? now() : null;
        $ticket->save();

        // Tell the sender (and, for college tickets, the whole college) about a new status or a new update
        if (($statusChanged || $remarksChanged) && $ticket->requester) {
            $statusLabel = ucfirst($ticket->status);
            $remarks = $ticket->admin_remarks ? " Admin update: {$ticket->admin_remarks}" : '';
            foreach ($ticket->requester->collegeTeamIds() as $memberId) {
                $this->notifications->notifyUser(
                    $memberId,
                    'maintenance_ticket_updated',
                    $statusChanged ? "Repair request {$statusLabel}" : 'Repair request updated',
                    "{$ticket->ticket_code} ({$ticket->subject}) is now {$statusLabel}.{$remarks}",
                    ['ticket_id' => $ticket->id, 'ticket_code' => $ticket->ticket_code, 'status' => $ticket->status]
                );
            }
        }

        return redirect()->route('admin.maintenance-tickets.show', $ticket)
            ->with('status', 'Ticket updated.');
    }
}
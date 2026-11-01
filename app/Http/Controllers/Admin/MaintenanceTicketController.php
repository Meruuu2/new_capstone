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

    private const COMPLETED_STATUSES = ['success', 'replacement'];

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

    /** Repair records for a whole year: per-month counts, plus the tickets of one selected month. */
    public function records(Request $request)
    {
        $year = filter_var($request->query('year', now()->year), FILTER_VALIDATE_INT);
        $year = $year !== false && $year >= 2024 && $year <= 2100 ? $year : now()->year;
        $month = filter_var($request->query('month', $year === now()->year ? now()->month : 12), FILTER_VALIDATE_INT);
        $month = $month !== false && $month >= 1 && $month <= 12 ? $month : 1;

        // Tickets count in the month they were received
        $byStatus = MaintenanceTicket::whereYear('created_at', $year)
            ->selectRaw('MONTH(created_at) as m, status, COUNT(*) as n')
            ->groupByRaw('MONTH(created_at), status')
            ->get()
            ->groupBy('m');

        $months = collect(range(1, 12))->mapWithKeys(function ($m) use ($byStatus) {
            $rows = ($byStatus[$m] ?? collect())->pluck('n', 'status');
            $open = (int) (($rows['pending'] ?? 0) + ($rows['notified'] ?? 0));

            return [$m => [
                'open' => $open,
                'ongoing' => (int) ($rows['ongoing'] ?? 0),
                'success' => (int) ($rows['success'] ?? 0),
                'replacement' => (int) ($rows['replacement'] ?? 0),
                'total' => (int) $rows->sum(),
            ]];
        });

        $completedByMonth = DashboardController::countByMonth(MaintenanceTicket::where('status', 'success'), 'completed_at', $year);

        $finished = MaintenanceTicket::where('status', 'success')->whereYear('completed_at', $year)->get(['created_at', 'completed_at']);
        $avgDays = $finished->isEmpty() ? null : round($finished->avg(fn ($t) => $t->created_at->diffInHours($t->completed_at) / 24), 1);

        $tickets = MaintenanceTicket::with(['requester', 'facility'])
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->oldest()
            ->get();

        return view('admin.repair-records.index', compact('year', 'month', 'months', 'completedByMonth', 'avgDays', 'tickets'));
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
            'status' => ['required', Rule::in(['pending', 'ongoing', 'success', 'replacement'])],
            // Replacement closes the ticket, so the sender must be told why it couldn't be repaired
            'admin_remarks' => ['nullable', 'required_if:status,replacement', 'string', 'max:5000'],
        ], [
            'admin_remarks.required_if' => 'Explain why the item can\'t be repaired and needs replacement.',
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
            $remarksLabel = $ticket->status === 'replacement' ? 'Reason' : 'Admin update';
            $remarks = $ticket->admin_remarks ? " {$remarksLabel}: {$ticket->admin_remarks}" : '';
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
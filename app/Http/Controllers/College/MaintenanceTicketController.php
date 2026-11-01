<?php

namespace App\Http\Controllers\College;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HandlesTicketMessages;
use App\Models\Facility;
use App\Models\MaintenanceTicket;
use App\Models\MaintenanceTicketMessage;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MaintenanceTicketController extends Controller
{
    use HandlesTicketMessages;

    public function __construct(protected NotificationService $notifications)
    {
    }

    public function index()
    {
        // Repair tickets are shared within the college: everyone sees the team's tickets
        $tickets = MaintenanceTicket::with(['facility', 'requester'])
            ->whereIn('requester_id', auth()->user()->collegeTeamIds())
            ->latest()
            ->paginate(15);

        return view('college.maintenance-tickets.index', compact('tickets'));
    }

    public function create()
    {
        $facilities = Facility::where('is_active', true)->orderBy('name')->get();

        return view('college.maintenance-tickets.create', compact('facilities'));
    }

    public function store(Request $request)
    {
        $request->merge(['requester_last_name' => auth()->user()->last_name]);

        // "Others" in the facility list: the requester types the place instead of picking a facility
        $isOther = $request->input('facility_id') === 'other';

        $validated = $request->validate([
            'requester_last_name' => ['required', 'string', 'max:120'],
            'facility_id' => $isOther
                ? ['required']
                : ['required', Rule::exists('facilities', 'id')->where('is_active', true)],
            'facility_other' => $isOther ? ['required', 'string', 'max:160'] : ['nullable'],
            'subject' => ['required', 'string', 'max:255'],
            'issue_description' => ['required', 'string', 'max:10000'],
        ], [
            'requester_last_name.required' => 'Update your account with a separate last name before submitting a maintenance request.',
            'facility_other.required' => 'Type the place that needs repair in Others.',
        ]);
        unset($validated['requester_last_name']);

        $validated['facility_id'] = $isOther ? null : $validated['facility_id'];
        $validated['facility_other'] = $isOther ? trim($validated['facility_other']) : null;

        do {
            $ticketCode = 'RM-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));
        } while (MaintenanceTicket::where('ticket_code', $ticketCode)->exists());

        $ticket = MaintenanceTicket::create([
            ...$validated,
            'ticket_code' => $ticketCode,
            'requester_id' => auth()->id(),
            'request_method' => 'email',
            'status' => 'notified',
            'requested_at' => now(),
        ]);

        $ticketData = ['ticket_id' => $ticket->id, 'ticket_code' => $ticket->ticket_code];
        $this->notifications->notifyUser(
            auth()->id(),
            'maintenance_ticket_submitted',
            'Repair request sent',
            "Your repair and maintenance request ({$ticketCode}) was sent to the administrators.",
            $ticketData
        );
        $this->notifications->notifyAdmins(
            'maintenance_ticket_submitted',
            'New repair request',
            "{$ticketCode}: {$ticket->subject} was submitted by " . auth()->user()->name . '.',
            $ticketData
        );

        return redirect()->route('college.maintenance-tickets.show', $ticket)
            ->with('status', "Request sent. Track it with code {$ticketCode}.");
    }

    public function show(MaintenanceTicket $ticket)
    {
        $this->authorizeConversation($ticket);

        $ticket->load(['facility', 'requester', 'messages.user']);

        return view('college.maintenance-tickets.show', compact('ticket'));
    }

    /** Anyone in the sender's college can open the ticket and talk with the admins about it. */
    protected function authorizeConversation(MaintenanceTicket $ticket): void
    {
        abort_unless(in_array($ticket->requester_id, auth()->user()->collegeTeamIds(), true), 404);
    }

    protected function notifyNewMessage(MaintenanceTicket $ticket, MaintenanceTicketMessage $message): void
    {
        $data = ['ticket_id' => $ticket->id, 'ticket_code' => $ticket->ticket_code, 'message_id' => $message->id];
        $text = auth()->user()->name . " wrote on {$ticket->ticket_code}: " . $this->messagePreview($message);

        $this->notifications->notifyAdmins('maintenance_ticket_message', 'New message on a repair request', $text, $data);

        // The rest of the college sees the ticket too, so let them know
        foreach (array_diff(auth()->user()->collegeTeamIds(), [auth()->id()]) as $memberId) {
            $this->notifications->notifyUser($memberId, 'maintenance_ticket_message', 'New message on a college repair request', $text, $data);
        }
    }

    protected function ticketRoute(): string
    {
        return 'college.maintenance-tickets.show';
    }
}
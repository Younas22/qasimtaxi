<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Notifications\Customer\SupportTicketRepliedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function index(Request $request): View
    {
        $tickets = SupportTicket::with('customer')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->query('search');

                $q->where(fn ($q) => $q
                    ->where('ticket_number', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', "%{$search}%")));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.support-tickets.index', compact('tickets'));
    }

    public function show(SupportTicket $ticket): View
    {
        $ticket->load(['customer', 'booking', 'replies.admin', 'replies.customer']);

        return view('admin.support-tickets.show', compact('ticket'));
    }

    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $ticket->replies()->create([
            'admin_id' => auth('admin')->id(),
            'message' => $data['message'],
        ]);

        if ($ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        $ticket->customer?->notify(new SupportTicketRepliedNotification($ticket));

        return back()->with('status', 'Reply sent.');
    }

    public function updateStatus(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(SupportTicket::STATUSES))],
        ]);

        $ticket->update($data);

        return back()->with('status', "Ticket \"{$ticket->ticket_number}\" marked as {$ticket->status_label}.");
    }
}

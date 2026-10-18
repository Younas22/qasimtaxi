<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\SupportTicket;
use App\Notifications\SupportTicketCreatedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class SupportController extends Controller
{
    public function index(Request $request): View
    {
        $customer = Auth::guard('customer')->user();
        $status = $request->query('status');
        $search = $request->query('search');

        $tickets = $customer->supportTickets()
            ->when(in_array($status, array_keys(SupportTicket::STATUSES), true), fn ($q) => $q->where('status', $status))
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('ticket_number', 'like', "%{$search}%")
                ->orWhere('subject', 'like', "%{$search}%")
            ))
            ->paginate(10)
            ->withQueryString();

        return view('customer.support.index', [
            'tickets' => $tickets,
            'status' => $status,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        $customer = Auth::guard('customer')->user();

        return view('customer.support.create', [
            'customer' => $customer,
            'bookings' => $customer->bookings()->latest('pickup_datetime')->limit(20)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $customer = Auth::guard('customer')->user();

        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
            'booking_id' => ['nullable', 'exists:bookings,id'],
        ]);

        if (! empty($data['booking_id'])) {
            abort_unless(
                $customer->bookings()->whereKey($data['booking_id'])->exists(),
                404
            );
        }

        $ticket = SupportTicket::create($data + [
            'customer_id' => $customer->id,
            'status' => 'open',
        ]);

        $admins = Admin::withPermission('support.view')->get();

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new SupportTicketCreatedNotification($ticket));
        }

        return redirect()->route('customer.support.show', $ticket)
            ->with('status', "Your ticket {$ticket->ticket_number} has been created. Our team will respond shortly.");
    }

    public function show(SupportTicket $ticket): View
    {
        abort_unless($ticket->customer_id === Auth::guard('customer')->id(), 404);

        $ticket->load(['replies.admin', 'replies.customer', 'booking']);

        return view('customer.support.show', compact('ticket'));
    }

    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        abort_unless($ticket->customer_id === Auth::guard('customer')->id(), 404);

        if ($ticket->status === 'closed') {
            return back()->with('error', 'This ticket is closed and can no longer receive replies.');
        }

        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $ticket->replies()->create([
            'customer_id' => Auth::guard('customer')->id(),
            'message' => $data['message'],
        ]);

        if ($ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        return back()->with('status', 'Your reply has been sent.');
    }

    public function close(SupportTicket $ticket): RedirectResponse
    {
        abort_unless($ticket->customer_id === Auth::guard('customer')->id(), 404);

        $ticket->update(['status' => 'closed']);

        return back()->with('status', "Ticket {$ticket->ticket_number} has been closed.");
    }
}

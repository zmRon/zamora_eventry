<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Models\Event;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\Registration;
use Illuminate\Support\Facades\Auth;

class OrganizerController extends Controller
{
    public function index()
    {
        $events = Auth::user()->events();
        $eventsCount = $events->count();
        $activeEvents = (clone $events)->whereIn('status', ['upcoming', 'ongoing'])->count();
        $completedEvents = (clone $events)->where('status', 'completed')->count();

        $totalAttendees = Registration::whereHas('ticket', function ($q) {
            $q->whereHas('event', function ($eq) {
                $eq->where('organizer_id', Auth::id());
            });
        })->count();

        $totalRevenue = \App\Models\Transaction::where('user_id', Auth::id())
            ->whereIn('type', ['earning', 'refund_deduction'])
            ->sum('amount');

        $recentEvents = Auth::user()->events()->latest()->take(5)->get();

        return view('organizer.dashboard', compact(
            'eventsCount', 'activeEvents', 'completedEvents',
            'totalAttendees', 'totalRevenue', 'recentEvents'
        ));
    }

    public function events(Request $request)
    {
        $query = Auth::user()->events()->with('category');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $events = $query->latest()->get();
        $categories = Category::all();

        return view('organizer.events.index', compact('events', 'categories'));
    }

    public function createEvent()
    {
        $categories = Category::all();
        return view('organizer.events.create', compact('categories'));
    }

    public function storeEvent(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'location' => 'required|string',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'capacity' => 'required|integer|min:1',
            'category_id' => 'required|exists:categories,id',
            'tickets' => 'required|array|min:1',
            'tickets.*.name' => 'required|string|max:255',
            'tickets.*.price' => 'required|numeric|min:0',
            'tickets.*.quantity' => 'required|integer|min:1',
            'is_public' => 'boolean',
        ], [
            'start_date.after_or_equal' => 'The event start date must be today or a future date.',
            'end_date.after_or_equal' => 'The event end date must be on or after the start date.',
        ]);
        $validated['is_public'] = $request->boolean('is_public');

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('events', 'public');
        }

        $event = Auth::user()->events()->create($validated);

        foreach ($request->tickets as $ticket) {
            $event->tickets()->create($ticket);
        }

        return redirect()->route('organizer.events')->with('success', 'Event created successfully.');
    }

    public function editEvent(Event $event)
    {
        if ($event->organizer_id !== Auth::id()) { abort(403); }
        $categories = Category::all();
        return view('organizer.events.edit', compact('event', 'categories'));
    }

    public function updateEvent(Request $request, Event $event)
    {
        if ($event->organizer_id !== Auth::id()) { abort(403); }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'location' => 'required|string',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'capacity' => 'required|integer|min:1',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:upcoming,ongoing,completed,cancelled',
            'tickets' => 'required|array|min:1',
            'tickets.*.id' => 'nullable|exists:tickets,id',
            'tickets.*.name' => 'required|string|max:255',
            'tickets.*.price' => 'required|numeric|min:0',
            'tickets.*.quantity' => 'required|integer|min:1',
            'is_public' => 'boolean',
        ], [
            'start_date.after_or_equal' => 'The event start date must be today or a future date.',
            'end_date.after_or_equal' => 'The event end date must be on or after the start date.',
        ]);

        $validated['is_public'] = $request->boolean('is_public');

        if ($request->hasFile('image')) {
            if ($event->image) {
                Storage::disk('public')->delete($event->image);
            }
            $validated['image'] = $request->file('image')->store('events', 'public');
        }

        $event->update($validated);

        $ticketIds = [];
        foreach ($request->tickets as $ticketData) {
            if (isset($ticketData['id'])) {
                $ticket = Ticket::find($ticketData['id']);
                if ($ticket && $ticket->event_id === $event->id) {
                    $ticket->update([
                        'name' => $ticketData['name'],
                        'price' => $ticketData['price'],
                        'quantity' => $ticketData['quantity'],
                    ]);
                    $ticketIds[] = $ticket->id;
                }
            } else {
                $newTicket = $event->tickets()->create([
                    'name' => $ticketData['name'],
                    'price' => $ticketData['price'],
                    'quantity' => $ticketData['quantity'],
                ]);
                $ticketIds[] = $newTicket->id;
            }
        }

        $ticketsToDelete = $event->tickets()->whereNotIn('id', $ticketIds)->get();
        $failedDeletes = 0;
        foreach ($ticketsToDelete as $t) {
            if ($t->registrations()->count() === 0) {
                $t->delete();
            } else {
                $failedDeletes++;
            }
        }

        $redirect = redirect()->route('organizer.events')->with('success', 'Event updated successfully.');
        if ($failedDeletes > 0) {
            $redirect->with('error', "$failedDeletes ticket(s) could not be removed because attendees have already registered for them.");
        }

        return $redirect;
    }

    public function deleteEvent(Event $event)
    {
        if ($event->organizer_id !== Auth::id()) { abort(403); }
        if ($event->image) {
            Storage::disk('public')->delete($event->image);
        }
        $event->delete();
        return redirect()->route('organizer.events')->with('success', 'Event deleted successfully.');
    }

    public function cloneEvent(Event $event)
    {
        if ($event->organizer_id !== Auth::id()) { abort(403); }

        $clone = $event->replicate();
        $clone->title = $event->title . ' (Copy)';
        $clone->status = 'upcoming';
        $clone->push();

        foreach ($event->tickets as $ticket) {
            $clone->tickets()->create([
                'name' => $ticket->name,
                'price' => $ticket->price,
                'quantity' => $ticket->quantity,
            ]);
        }

        return redirect()->route('organizer.events')->with('success', 'Event cloned successfully.');
    }

    public function updateStatus(Request $request, Event $event)
    {
        if ($event->organizer_id !== Auth::id()) { abort(403); }
        $validated = $request->validate(['status' => 'required|in:upcoming,ongoing,completed,cancelled']);
        $event->update(['status' => $validated['status']]);

        if ($validated['status'] === 'cancelled') {
            foreach ($event->tickets as $ticket) {
                foreach ($ticket->registrations as $registration) {
                    $registration->attendee->notify(new \App\Notifications\EventCancelled($event));
                }
            }
        }

        return back()->with('success', 'Event status updated.');
    }

    public function tickets(Event $event)
    {
        if ($event->organizer_id !== Auth::id()) { abort(403); }
        return view('organizer.tickets.index', compact('event'));
    }

    public function createTicket(Event $event)
    {
        if ($event->organizer_id !== Auth::id()) { abort(403); }
        return view('organizer.tickets.create', compact('event'));
    }

    public function storeTicket(Request $request, Event $event)
    {
        if ($event->organizer_id !== Auth::id()) { abort(403); }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1'
        ]);

        $event->tickets()->create($validated);
        return redirect()->route('organizer.events.tickets', $event)->with('success', 'Ticket created.');
    }

    public function deleteTicket(Ticket $ticket)
    {
        if ($ticket->event->organizer_id !== Auth::id()) { abort(403); }
        if ($ticket->registrations()->count() > 0) {
            return back()->with('error', 'Cannot delete a ticket that has registrations.');
        }
        $ticket->delete();
        return back()->with('success', 'Ticket deleted.');
    }

    public function attendees(Event $event)
    {
        if ($event->organizer_id !== Auth::id()) { abort(403); }
        $registrations = Registration::whereHas('ticket', function($q) use ($event) {
            $q->where('event_id', $event->id);
        })->with('attendee', 'ticket')->get();
        return view('organizer.attendees.index', compact('event', 'registrations'));
    }

    public function updateRegistrationStatus(Request $request, Registration $registration)
    {
        if ($registration->ticket->event->organizer_id !== Auth::id()) { abort(403); }
        $validated = $request->validate(['status' => 'required|in:pending,approved,cancelled,checked-in']);
        $registration->update(['status' => $validated['status']]);

        $registration->attendee->notify(new \App\Notifications\RegistrationStatusUpdated($registration->ticket->event, $validated['status']));

        return back()->with('success', 'Registration status updated.');
    }



    public function wallet(Request $request)
    {
        $user = Auth::user();
        
        // 1. Per-event revenue breakdown
        $events = Event::where('organizer_id', $user->id)->get();
        $eventBreakdown = $events->map(function ($event) {
            $earnings = \App\Models\Transaction::where('event_id', $event->id)
                ->where('type', 'earning')
                ->sum('amount');
            
            $refundDeductions = \App\Models\Transaction::where('event_id', $event->id)
                ->where('type', 'refund_deduction')
                ->sum('amount'); // Negative value

            $netRevenue = $earnings + $refundDeductions;

            $ticketsSold = Registration::whereHas('ticket', function ($q) use ($event) {
                $q->where('event_id', $event->id);
            })->count();

            return (object) [
                'id' => $event->id,
                'title' => $event->title,
                'tickets_sold' => $ticketsSold,
                'earnings' => $earnings,
                'refunds' => abs($refundDeductions),
                'net' => $netRevenue,
            ];
        });

        // 2. Earnings transaction log
        $logQuery = \App\Models\Transaction::where('user_id', $user->id)
            ->whereIn('type', ['earning', 'refund_deduction'])
            ->with(['event', 'registration.attendee']);

        if ($request->filled('event_id')) {
            $logQuery->where('event_id', $request->event_id);
        }

        if ($request->filled('date_from')) {
            $logQuery->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $logQuery->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $logQuery->latest()->paginate(10)->withQueryString();

        return view('organizer.wallet.index', compact('user', 'eventBreakdown', 'transactions', 'events'));
    }
}
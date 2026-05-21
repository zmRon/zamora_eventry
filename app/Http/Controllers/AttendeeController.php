<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Event;
use App\Models\Category;
use App\Models\Registration;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\Feedback;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class AttendeeController extends Controller
{
    public function index()
    {
        $upcomingEvents = Event::where('status', 'upcoming')->where('is_public', true)->take(6)->get();
        $myRegistrations = Auth::user()->registrations()->with('ticket.event')->latest()->take(3)->get();
        $favoriteEvents = Auth::user()->favoriteEvents()->take(3)->get();

        return view('attendee.dashboard', compact('upcomingEvents', 'myRegistrations', 'favoriteEvents'));
    }

    public function calendar()
    {
        $registrations = \Illuminate\Support\Facades\Auth::user()->registrations()->with('ticket.event')->get();
        $events = $registrations->map(function ($registration) {
            $event = $registration->ticket->event;
            return [
                'title' => $event->title,
                'start' => $event->start_date ? $event->start_date->format('Y-m-d\TH:i:s') : null,
                'end' => $event->end_date ? $event->end_date->format('Y-m-d\TH:i:s') : null,
                'url' => route('attendee.events.show', $event->id),
                'color' => '#0047CC', // brand blue
            ];
        });

        return view('attendee.calendar', compact('events'));
    }

    public function events(Request $request)
    {
        $query = Event::with('category')->where('is_public', true);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_range')) {
            $range = $request->date_range;
            if ($range === 'today') {
                $query->whereDate('start_date', today());
            } elseif ($range === 'this_weekend') {
                $saturday = now()->startOfWeek()->addDays(5)->startOfDay();
                $sunday = now()->startOfWeek()->addDays(6)->endOfDay();
                $query->whereBetween('start_date', [$saturday, $sunday]);
            } elseif ($range === 'next_week') {
                $start = now()->addWeek()->startOfWeek()->startOfDay();
                $end = now()->addWeek()->endOfWeek()->endOfDay();
                $query->whereBetween('start_date', [$start, $end]);
            } elseif ($range === 'this_month') {
                $query->whereMonth('start_date', now()->month)
                      ->whereYear('start_date', now()->year);
            }
        }

        $events = $query->latest()->paginate(12)->withQueryString();
        $categories = Category::all();

        return view('attendee.events.index', compact('events', 'categories'));
    }

    public function show(Event $event)
    {
        $event->load('category', 'tickets', 'feedbacks.attendee');
        $avgRating = $event->feedbacks()->avg('rating') ?? 0;
        $ratingCount = $event->feedbacks()->count();

        return view('attendee.events.show', compact('event', 'avgRating', 'ratingCount'));
    }

    public function checkout(Request $request, Event $event)
    {
        $ticketId = $request->query('ticket_id');
        $ticket = Ticket::where('event_id', $event->id)->findOrFail($ticketId);

        return view('attendee.checkout', compact('event', 'ticket'));
    }

    public function confirm(Request $request, Event $event)
    {
        $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
        ]);

        $ticket = Ticket::where('event_id', $event->id)->findOrFail($request->ticket_id);

        if (in_array($event->status, ['completed', 'cancelled'])) {
            return back()->with('error', 'You cannot register for this event.');
        }

        try {
            $registration = \Illuminate\Support\Facades\DB::transaction(function () use ($event, $ticket) {
                // Lock attendee
                $attendee = \App\Models\User::where('id', Auth::id())->lockForUpdate()->firstOrFail();
                // Lock organizer
                $organizer = \App\Models\User::where('id', $event->organizer_id)->lockForUpdate()->firstOrFail();

                $existingRegistration = Registration::where('attendee_id', $attendee->id)
                    ->whereHas('ticket', function ($q) use ($event) {
                        $q->where('event_id', $event->id);
                    })->exists();

                if ($existingRegistration) {
                    throw new \Exception('You are already registered for this event.');
                }

                $eventRegistrationsCount = Registration::whereHas('ticket', function ($q) use ($event) {
                    $q->where('event_id', $event->id);
                })->count();

                if ($eventRegistrationsCount >= $event->capacity) {
                    throw new \Exception('This event has reached its maximum capacity.');
                }

                if ($ticket->registrations()->count() >= $ticket->quantity) {
                    throw new \Exception('This ticket type is sold out.');
                }

                if ($ticket->price > 0) {
                    // Deduct attendee balance
                    $attendee->deductCredits($ticket->price);
                    // Add organizer balance
                    $organizer->addCredits($ticket->price);
                }

                $registration = Registration::create([
                    'attendee_id' => $attendee->id,
                    'ticket_id' => $ticket->id,
                    'status' => 'confirmed',
                ]);

                if ($ticket->price > 0) {
                    // Log attendee transaction
                    Transaction::create([
                        'user_id' => $attendee->id,
                        'type' => 'purchase',
                        'amount' => -$ticket->price,
                        'running_balance' => $attendee->credits,
                        'registration_id' => $registration->id,
                        'event_id' => $event->id,
                        'payment_method' => 'Credits',
                        'status' => 'success',
                        'description' => 'Ticket purchase: ' . $ticket->name . ' for ' . $event->title,
                    ]);

                    // Log organizer transaction
                    Transaction::create([
                        'user_id' => $organizer->id,
                        'type' => 'earning',
                        'amount' => $ticket->price,
                        'running_balance' => $organizer->credits,
                        'registration_id' => $registration->id,
                        'event_id' => $event->id,
                        'payment_method' => 'Credits',
                        'status' => 'success',
                        'description' => 'Ticket sale: ' . $ticket->name . ' for ' . $event->title,
                    ]);
                }

                $event->organizer->notify(new \App\Notifications\EventRegistered($event, $attendee));

                return $registration;
            });

            return redirect()->route('attendee.bookings.receipt', $registration->id)->with('success', 'Booking confirmed!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function downloadICS(Event $event)
    {
        $startDate = $event->start_date->timezone('Asia/Manila')->format('Ymd\THis');
        $endDate = $event->end_date->timezone('Asia/Manila')->format('Ymd\THis');
        
        $ics = "BEGIN:VCALENDAR\r\n";
        $ics .= "VERSION:2.0\r\n";
        $ics .= "PRODID:-//Zamora Eventry//EN\r\n";
        $ics .= "BEGIN:VEVENT\r\n";
        $ics .= "UID:" . uniqid() . "@zamoraeventry.com\r\n";
        $ics .= "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n";
        $ics .= "DTSTART;TZID=Asia/Manila:" . $startDate . "\r\n";
        $ics .= "DTEND;TZID=Asia/Manila:" . $endDate . "\r\n";
        $ics .= "SUMMARY:" . $event->title . "\r\n";
        $ics .= "LOCATION:" . $event->location . "\r\n";
        $ics .= "DESCRIPTION:" . str_replace(["\r", "\n"], ["", "\\n"], $event->description) . "\r\n";
        $ics .= "END:VEVENT\r\n";
        $ics .= "END:VCALENDAR\r\n";

        return response($ics)
            ->header('Content-Type', 'text/calendar; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . Str::slug($event->title) . '.ics"');
    }

    public function register(Request $request, Event $event)
    {
        $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
        ]);

        if ($request->has('checkout') && $request->checkout) {
            return redirect()->route('attendee.checkout', [
                'event' => $event->id,
                'ticket_id' => $request->ticket_id,
            ]);
        }

        if (in_array($event->status, ['completed', 'cancelled'])) {
            return back()->with('error', 'You cannot register for this event.');
        }

        $existingRegistration = Registration::where('attendee_id', Auth::id())
            ->whereHas('ticket', function ($q) use ($event) {
                $q->where('event_id', $event->id);
            })->exists();

        if ($existingRegistration) {
            return back()->with('error', 'You are already registered for this event.');
        }

        $ticket = Ticket::findOrFail($request->ticket_id);

        if ($ticket->event_id !== $event->id) {
            return back()->with('error', 'Invalid ticket for this event.');
        }

        $eventRegistrationsCount = Registration::whereHas('ticket', function ($q) use ($event) {
            $q->where('event_id', $event->id);
        })->count();

        if ($eventRegistrationsCount >= $event->capacity) {
            return back()->with('error', 'This event has reached its maximum capacity.');
        }

        if ($ticket->registrations()->count() >= $ticket->quantity) {
            return back()->with('error', 'This ticket type is sold out.');
        }

        Registration::create([
            'attendee_id' => Auth::id(),
            'ticket_id' => $ticket->id,
            'status' => 'pending',
        ]);

        $event->organizer->notify(new \App\Notifications\EventRegistered($event, Auth::user()));

        return redirect()->route('attendee.tickets')->with('success', 'Registered successfully.');
    }

    public function myTickets()
    {
        $registrations = Auth::user()->registrations()->with('ticket.event')->latest()->get();
        return view('attendee.tickets.index', compact('registrations'));
    }

    public function cancelRegistration(Registration $registration)
    {
        if ($registration->attendee_id !== Auth::id()) { abort(403); }
        
        $event = $registration->ticket->event;
        if (in_array($event->status, ['completed', 'cancelled']) || $event->start_date->isPast()) {
            return back()->with('error', 'You cannot cancel a registration for a past or cancelled event.');
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($registration, $event) {
                $ticket = $registration->ticket;
                
                // Lock attendee
                $attendee = \App\Models\User::where('id', Auth::id())->lockForUpdate()->firstOrFail();
                // Lock organizer
                $organizer = \App\Models\User::where('id', $event->organizer_id)->lockForUpdate()->firstOrFail();

                if ($ticket->price > 0) {
                    // Refund attendee
                    $attendee->addCredits($ticket->price);

                    // Guard organizer balance (deduct up to their current balance)
                    $refundDeduction = $ticket->price;
                    if ($organizer->credits < $refundDeduction) {
                        $actualDeduction = $organizer->credits;
                        $organizer->credits = 0.00;
                        $organizer->save();
                    } else {
                        $actualDeduction = $refundDeduction;
                        $organizer->deductCredits($refundDeduction);
                    }

                    // Create attendee refund transaction
                    Transaction::create([
                        'user_id' => $attendee->id,
                        'type' => 'refund',
                        'amount' => $ticket->price,
                        'running_balance' => $attendee->credits,
                        'registration_id' => null,
                        'event_id' => $event->id,
                        'payment_method' => 'Credits',
                        'status' => 'success',
                        'description' => 'Refund: Ticket cancellation for ' . $event->title,
                    ]);

                    // Create organizer refund deduction transaction
                    Transaction::create([
                        'user_id' => $organizer->id,
                        'type' => 'refund_deduction',
                        'amount' => -$actualDeduction,
                        'running_balance' => $organizer->credits,
                        'registration_id' => null,
                        'event_id' => $event->id,
                        'payment_method' => 'Credits',
                        'status' => 'success',
                        'description' => 'Refund deduction: Ticket cancellation for ' . $event->title,
                    ]);
                }

                $registration->delete();
            });

            return back()->with('success', 'Registration cancelled and credits refunded successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to cancel registration: ' . $e->getMessage());
        }
    }

    public function submitFeedback(Request $request, Event $event)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:5',
        ]);

        $existing = $event->feedbacks()->where('attendee_id', Auth::id())->first();
        if ($existing) {
            return back()->with('error', 'You have already submitted feedback for this event.');
        }

        $event->feedbacks()->create([
            'attendee_id' => Auth::id(),
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return back()->with('success', 'Feedback submitted successfully.');
    }

    public function toggleFavorite(Request $request, Event $event)
    {
        $user = Auth::user();
        if ($user->favoriteEvents()->where('event_id', $event->id)->exists()) {
            $user->favoriteEvents()->detach($event->id);
            $message = 'Event removed from favorites.';
        } else {
            $user->favoriteEvents()->attach($event->id);
            $message = 'Event added to favorites.';
        }

        return back()->with('success', $message);
    }

    public function favorites()
    {
        $favorites = Auth::user()->favoriteEvents()->with('category')->latest()->get();
        return view('attendee.events.index', ['events' => $favorites, 'categories' => Category::all(), 'isFavorites' => true]);
    }

    public function receipt(Registration $registration)
    {
        if ($registration->attendee_id !== Auth::id()) {
            abort(403);
        }
        $registration->load('ticket.event.category');
        return view('attendee.bookings.receipt', compact('registration'));
    }

    public function downloadTicket(Registration $registration)
    {
        if ($registration->attendee_id !== Auth::id()) {
            abort(403);
        }
        $registration->load('ticket.event.category');

        $pdf = Pdf::loadView('attendee.bookings.ticket_pdf', compact('registration'));
        return $pdf->download('ticket-' . str_pad($registration->id, 6, '0', STR_PAD_LEFT) . '.pdf');
    }
}
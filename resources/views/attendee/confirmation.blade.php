<x-app-layout>
    <x-slot name="header">
        <h2 class="h2 text-gradient">Booking Confirmed!</h2>
    </x-slot>

    <div class="booking-steps">
        <div class="booking-step done">
            <span class="step-number">✓</span> Select Ticket
        </div>
        <div class="booking-step done">
            <span class="step-number">✓</span> Checkout
        </div>
        <div class="booking-step active">
            <span class="step-number">3</span> Confirmation
        </div>
    </div>

    <div style="max-width: 600px; margin: 0 auto;">
        <div class="glass-panel text-center mb-6">
            <div style="font-size: 3rem; margin-bottom: 1rem;">🎉</div>
            <h3 class="h3 text-gradient mb-2">Booking Confirmed!</h3>
            <p class="text-muted mb-4">Your registration has been confirmed. Here are your ticket details:</p>

            <div class="glass-panel p-4 mb-4" style="background: rgba(0,0,0,0.2); text-align: left;">
                <div class="flex justify-between mb-2">
                    <span class="text-muted">Event</span>
                    <strong>{{ $event->title }}</strong>
                </div>
                <div class="flex justify-between mb-2">
                    <span class="text-muted">Date</span>
                    <strong>{{ $event->start_date->format('F d, Y h:i A') }}</strong>
                </div>
                <div class="flex justify-between mb-2">
                    <span class="text-muted">Location</span>
                    <strong>{{ $event->location }}</strong>
                </div>
                <div class="flex justify-between mb-2">
                    <span class="text-muted">Ticket</span>
                    <strong>{{ $ticket->name }}</strong>
                </div>
                <div class="flex justify-between mb-2">
                    <span class="text-muted">Amount Paid</span>
                    <strong>₱{{ number_format($ticket->price, 2) }}</strong>
                </div>
                <div class="flex justify-between mb-2">
                    <span class="text-muted">Status</span>
                    <span class="badge badge-primary">{{ ucfirst($registration->status) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted">Booking ID</span>
                    <strong class="text-sm">#{{ str_pad($registration->id, 6, '0', STR_PAD_LEFT) }}</strong>
                </div>
            </div>

            <div class="flex gap-4 justify-center">
                <a href="{{ route('attendee.tickets') }}" class="btn btn-primary">View My Tickets</a>
                <a href="{{ route('attendee.events') }}" class="btn btn-glass">Browse More Events</a>
            </div>
        </div>
    </div>
</x-app-layout>
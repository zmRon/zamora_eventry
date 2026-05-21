<x-app-layout>
    <x-slot name="header">
        <h2 class="h2 text-gradient">Checkout</h2>
    </x-slot>

    <div class="booking-steps">
        <div class="booking-step active">
            <span class="step-number">1</span> Select Ticket
        </div>
        <div class="booking-step active">
            <span class="step-number">2</span> Checkout
        </div>
        <div class="booking-step">
            <span class="step-number">3</span> Confirmation
        </div>
    </div>

    <div class="grid grid-cols-2 gap-6" style="max-width: 900px; margin: 0 auto;">
        <div class="glass-panel">
            <h3 class="h3 mb-4">Event Details</h3>
            @if($event->image_url)
                <img src="{{ $event->image_url }}" alt="{{ $event->title }}" style="width: 100%; height: 160px; object-fit: cover; border-radius: 8px; margin-bottom: 12px;">
            @endif
            <h4 class="font-bold mb-2">{{ $event->title }}</h4>
            <p class="text-sm text-muted mb-1">{{ $event->category->name ?? 'General' }}</p>
            <p class="text-sm mb-1">{{ $event->start_date->format('F d, Y h:i A') }}</p>
            <p class="text-sm mb-3">{{ $event->location }}</p>
        </div>

        <div class="glass-panel">
            <h3 class="h3 mb-4">Order Summary</h3>
            <div class="flex justify-between mb-3 pb-2" style="border-bottom: 1px solid var(--border);">
                <span>{{ $ticket->name }}</span>
                <span>₱{{ number_format($ticket->price, 2) }}</span>
            </div>
            <div class="flex justify-between mb-3 pb-2" style="border-bottom: 1px solid var(--border);">
                <span>Quantity</span>
                <span>1</span>
            </div>
            <div class="flex justify-between mb-4">
                <strong>Total</strong>
                <strong class="text-lg">₱{{ number_format($ticket->price, 2) }}</strong>
            </div>

            <div class="glass-panel p-4 mb-4" style="background: rgba(139,92,246,0.05); border-color: rgba(139,92,246,0.2);">
                <p class="text-sm text-muted mb-2">Payment Method</p>
                <div class="flex items-center gap-2">
                    <span>💳</span>
                    <strong>Online Payment (Demo)</strong>
                </div>
            </div>

            <form action="{{ route('attendee.confirm', $event) }}" method="POST">
                @csrf
                <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
                <button type="submit" class="btn btn-primary" style="width: 100%;">Confirm Booking</button>
            </form>
            <a href="{{ route('attendee.events.show', $event) }}" class="btn btn-glass mt-3" style="width: 100%;">Cancel</a>
        </div>
    </div>
</x-app-layout>
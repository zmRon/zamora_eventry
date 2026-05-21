<x-app-layout>
    <x-slot name="header">
        <h2 class="h2 text-gradient">My Dashboard</h2>
    </x-slot>

    <div>
        <div style="margin-bottom: 2rem;">
            <div class="glass-panel" style="background: linear-gradient(135deg, rgba(0, 71, 204, 0.07) 0%, rgba(0, 71, 204, 0.02) 100%); border: 1px solid rgba(0, 71, 204, 0.15); display: flex; justify-content: space-between; align-items: center; padding: 1.5rem 2rem; border-radius: 12px;">
                <div>
                    <span style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-med);">Available Wallet Balance</span>
                    <h1 style="font-size: 2.25rem; font-weight: 800; color: var(--text-high); margin-top: 0.25rem; font-family: 'Inter', sans-serif; margin-bottom: 0;">
                        ₱{{ number_format(auth()->user()->credits, 2) }}
                    </h1>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <a href="{{ route('attendee.wallet.index') }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 12px rgba(0, 71, 204, 0.2);">
                        <span>⚡</span> Top Up Credits
                    </a>
                    <a href="{{ route('attendee.wallet.index') }}#history" class="btn btn-glass" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                        <span>📜</span> View History
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="glass-panel text-center hover-scale">
                <h3 class="h3">Browse Events</h3>
                <p class="text-muted">Discover upcoming events</p>
                <a href="{{ route('attendee.events') }}" class="btn btn-primary mt-4">View All Events</a>
            </div>
            <div class="glass-panel text-center hover-scale">
                <h3 class="h3">My Tickets</h3>
                <p class="text-muted">View your registrations</p>
                <a href="{{ route('attendee.tickets') }}" class="btn btn-glass mt-4">View Tickets</a>
            </div>
            <div class="glass-panel text-center hover-scale">
                <h3 class="h3">Favorites</h3>
                <p class="text-muted">Saved events</p>
                <a href="{{ route('attendee.favorites') }}" class="btn btn-glass mt-4">View Favorites</a>
            </div>
        </div>

        @php
            $nextUpcomingReg = auth()->user()->registrations()
                ->whereHas('ticket.event', function($q) { $q->where('start_date', '>', now()); })
                ->with('ticket.event')
                ->get()
                ->sortBy('ticket.event.start_date')
                ->first();
        @endphp
        
        @if($nextUpcomingReg)
        <div class="glass-panel p-6" style="border: 1px solid #D0E0FC; border-left: 4px solid var(--primary); background: #F0F6FF; margin-top: 2.25rem; margin-bottom: 2.5rem; transform: none !important;">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="h3 mb-1" style="color: var(--primary);">🔔 Next Upcoming Event</h3>
                    <p class="text-lg font-bold">{{ $nextUpcomingReg->ticket->event->title }}</p>
                    <p class="text-muted text-sm mt-1">
                        📅 {{ $nextUpcomingReg->ticket->event->start_date->format('l, F j, Y \a\t g:i A') }} ({{ $nextUpcomingReg->ticket->event->start_date->diffForHumans() }})
                    </p>
                </div>
                <a href="{{ route('attendee.events.show', $nextUpcomingReg->ticket->event) }}" class="btn btn-primary" style="box-shadow: 0 4px 12px rgba(0, 71, 204, 0.2);">View Details</a>
            </div>
        </div>
        @else
        <div style="margin-bottom: 2.5rem;"></div>
        @endif
    </div>
        
    

    @if($myRegistrations->count() > 0)
    <div style="margin-bottom: 3.5rem; padding-bottom: 1.5rem;">
        <h3 class="h3 text-gradient mb-4">My Recent Registrations</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($myRegistrations as $reg)
            <div class="glass-panel p-4">
                <h4 class="font-bold mb-1">{{ $reg->ticket->event->title ?? 'N/A' }}</h4>
                <p class="text-sm text-muted">{{ $reg->ticket->name }} - ₱{{ number_format($reg->ticket->price, 2) }}</p>
                <span class="badge badge-primary mt-2" style="background-color: var(--primary); color: white;">{{ ucfirst($reg->status) }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($upcomingEvents->count() > 0)
    <div style="margin-bottom: 3rem;">
        <div class="flex justify-between items-center mb-4">
            <h3 class="h3 text-gradient">Upcoming Events</h3>
            <a href="{{ route('attendee.events') }}" class="btn btn-glass text-sm">View All</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($upcomingEvents as $event)
            <div class="glass-panel hover-scale">
                @if($event->image_url)
                    <img src="{{ $event->image_url }}" alt="{{ $event->title }}" style="width: 100%; height: 140px; object-fit: cover; border-radius: 8px; margin-bottom: 12px;">
                @endif
                <h3 class="h3" style="margin-bottom: 4px;">{{ $event->title }}</h3>
                <p class="text-sm text-muted mb-2">{{ $event->category->name ?? 'General' }}</p>
                <p class="text-sm mb-1"><strong>{{ $event->start_date->format('M d, Y H:i') }}</strong></p>
                <p class="text-sm mb-3">{{ $event->location }}</p>
                <a href="{{ route('attendee.events.show', $event) }}" class="btn btn-primary" style="width: 100%; box-shadow: 0 4px 12px rgba(0, 71, 204, 0.1);">View Details</a>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</x-app-layout>
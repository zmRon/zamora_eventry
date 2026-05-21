<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="h2 text-gradient">{{ $isFavorites ?? false ? 'My Favorites' : 'Browse Events' }}</h2>
            @if(!($isFavorites ?? false))
                <a href="{{ route('attendee.favorites') }}" class="btn btn-glass">
                    My Favorites
                    @php $favCount = auth()->user()->favoriteEvents()->count(); @endphp
                    @if($favCount > 0)
                        <span class="badge badge-primary ml-2">{{ $favCount }}</span>
                    @endif
                </a>
            @endif
        </div>
    </x-slot>

    @if(!($isFavorites ?? false))
    <div class="filter-card">
        <form action="{{ route('attendee.events') }}" method="GET" class="flex gap-2 items-center">
            <input type="text" name="search" value="{{ request('search') }}" class="input" style="width: 220px;" placeholder="Search events...">
            
            <select name="category" class="input" style="width: 140px;">
                <option value="">Category: All</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>

            <select name="status" class="input" style="width: 130px;">
                <option value="">Status: All</option>
                <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
            </select>

            <select name="date_range" class="input" style="width: 130px;">
                <option value="">Date: Any</option>
                <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>Today</option>
                <option value="this_weekend" {{ request('date_range') == 'this_weekend' ? 'selected' : '' }}>This Weekend</option>
                <option value="next_week" {{ request('date_range') == 'next_week' ? 'selected' : '' }}>Next Week</option>
                <option value="this_month" {{ request('date_range') == 'this_month' ? 'selected' : '' }}>This Month</option>
            </select>

            <button type="submit" class="btn btn-primary btn-filter">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width: 14px; height: 14px; flex-shrink: 0;">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                </svg>
                Filter
            </button>
            
            @if(request('search') || request('category') || request('status') || request('date_range'))
                <a href="{{ route('attendee.events') }}" class="btn btn-glass btn-clear">Clear</a>
            @endif
        </form>
    </div>
    @endif

    <div class="flex justify-end mb-4">
        <div class="flex gap-2">
            <button type="button" onclick="setView('grid')" id="btn-grid" class="btn btn-primary" style="padding: 0.4rem 1rem; font-size: 0.85rem;">Grid</button>
            <button type="button" onclick="setView('list')" id="btn-list" class="btn btn-glass" style="padding: 0.4rem 1rem; font-size: 0.85rem;">List</button>
        </div>
    </div>

    @if($events->count() > 0)
    <style>
        .view-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        .view-list {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        .event-card {
            display: flex;
            flex-direction: column;
        }
        .view-list .event-card {
            flex-direction: row;
            align-items: stretch;
        }
        .event-img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 12px;
        }
        .view-list .event-img {
            width: 250px;
            height: 100%;
            min-height: 150px;
            margin-bottom: 0;
            margin-right: 1.5rem;
        }
        .event-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
    </style>

    <div id="events-container" class="view-grid">
        @foreach($events as $event)
        <div class="glass-panel hover-scale event-card relative">
            @php $isRegistered = auth()->user()->registrations->contains('event_id', $event->id); @endphp
            @if($isRegistered)
                <div style="position: absolute; top: 10px; left: 10px; background: var(--primary); color: white; padding: 2px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; z-index: 10; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
                    ✓ Registered
                </div>
            @endif
            <div style="position: relative;">
                @if($event->image_url)
                    <img class="event-img" src="{{ $event->image_url }}" alt="{{ $event->title }}">
                @else
                    <div class="event-img" style="background: linear-gradient(135deg, rgba(139,92,246,0.2), rgba(236,72,153,0.2)); display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                        📅
                    </div>
                @endif
                <form action="{{ route('attendee.events.favorite', $event) }}" method="POST" style="position: absolute; top: 10px; right: 10px; z-index: 10; background: rgba(255,255,255,0.8); border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    @csrf
                    <button type="submit" class="btn-favorite {{ auth()->user()->favoriteEvents()->where('event_id', $event->id)->exists() ? 'active' : '' }}" title="Toggle favorite" style="margin: 0; font-size: 1.2rem; background: transparent; border: none; cursor: pointer;">
                        {{ auth()->user()->favoriteEvents()->where('event_id', $event->id)->exists() ? '❤️' : '🤍' }}
                    </button>
                </form>
            </div>
            <div class="event-content">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="h3" style="margin-bottom: 0;">{{ $event->title }}</h3>
                </div>
                <p class="text-sm text-muted mb-2">{{ $event->category->name ?? 'General' }}</p>
                <p class="text-sm mb-1"><strong>Date:</strong> {{ $event->start_date->format('M d, Y H:i') }}</p>
                <p class="text-sm mb-3"><strong>Location:</strong> {{ $event->location }}</p>
                
                <div style="margin-top: auto;">
                    @if($event->tickets->count() > 0)
                        @php $minPrice = $event->tickets->min('price'); @endphp
                        <p class="text-sm mb-3 font-semibold text-primary">
                            {{ $minPrice > 0 ? 'Tickets from ₱' . number_format($minPrice, 2) : 'Free' }}
                        </p>
                    @endif
                    <a href="{{ route('attendee.events.show', $event) }}" class="btn btn-primary" style="width: 100%;">View Details</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if(method_exists($events, 'links'))
        <div class="mt-6">
            {{ $events->links() }}
        </div>
    @endif
    @else
    <div class="glass-panel">
        <div class="empty-state">
            <h3 class="h3 mb-4">No events found</h3>
            @if($isFavorites ?? false)
                <p>You haven't saved any events as favorites yet.</p>
                <a href="{{ route('attendee.events') }}" class="btn btn-primary mt-4">Browse Events</a>
            @else
                <p>No events match your search criteria.</p>
                <a href="{{ route('attendee.events') }}" class="btn btn-primary mt-4">Clear Filters</a>
            @endif
        </div>
    </div>
    @endif

    <script>
        function setView(view) {
            const container = document.getElementById('events-container');
            const btnGrid = document.getElementById('btn-grid');
            const btnList = document.getElementById('btn-list');
            
            if (!container) return;

            if (view === 'list') {
                container.className = 'view-list';
                btnList.className = 'btn btn-primary';
                btnGrid.className = 'btn btn-glass';
                localStorage.setItem('events_view', 'list');
            } else {
                container.className = 'view-grid';
                btnGrid.className = 'btn btn-primary';
                btnList.className = 'btn btn-glass';
                localStorage.setItem('events_view', 'grid');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const savedView = localStorage.getItem('events_view') || 'grid';
            setView(savedView);
        });
    </script>
</x-app-layout>
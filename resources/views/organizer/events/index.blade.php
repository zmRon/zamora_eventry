<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="h2 text-gradient">My Events</h2>
            <a href="{{ route('organizer.events.create') }}" class="btn btn-primary">+ Create Event</a>
        </div>
    </x-slot>

    <div class="filter-card">
        <form action="{{ route('organizer.events') }}" method="GET" class="flex gap-2 items-center">
            <input type="text" name="search" value="{{ request('search') }}" class="input" style="width: 220px;" placeholder="Search events...">
            
            <select name="status" class="input" style="width: 130px;">
                <option value="">Status: All</option>
                <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>

            <select name="category" class="input" style="width: 140px;">
                <option value="">Category: All</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-primary btn-filter">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width: 14px; height: 14px; flex-shrink: 0;">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                </svg>
                Filter
            </button>
            
            @if(request('search') || request('status') || request('category'))
                <a href="{{ route('organizer.events') }}" class="btn btn-glass btn-clear">Clear</a>
            @endif
        </form>
    </div>

    <div class="table-cards-container">
        <table class="table-cards">
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Category</th>
                    <th style="white-space: nowrap;">Date</th>
                    <th style="white-space: nowrap;">Capacity</th>
                    <th style="white-space: nowrap;">Status</th>
                    <th style="white-space: nowrap;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($events as $event)
                <tr class="event-row">
                    <td>
                        <div class="flex items-center gap-4" style="min-width: max-content;">
                            @if($event->image_url)
                                <img src="{{ $event->image_url }}" alt="" class="event-thumbnail">
                            @else
                                <div class="event-thumbnail-fallback">
                                    {{ strtoupper(substr($event->title, 0, 1)) }}
                                </div>
                            @endif
                            <span style="font-weight: 600; color: var(--text-high);">{{ Str::limit($event->title, 40) }}</span>
                        </div>
                    </td>
                    <td>
                        <span class="category-pill">{{ $event->category->name }}</span>
                    </td>
                    <td style="white-space: nowrap; font-weight: 500; color: var(--text-med);">{{ $event->start_date->format('M d, Y') }}</td>
                    <td>
                        @php 
                            $regCount = $event->registrationsCount(); 
                            $percent = $event->capacity > 0 ? min(100, round(($regCount / $event->capacity) * 100)) : 0;
                        @endphp
                        <div style="min-width: 160px; display: flex; flex-direction: column; gap: 0.35rem;">
                            <span style="font-size: 0.75rem; font-weight: 600; color: var(--text-med);">
                                {{ $regCount }} / {{ $event->capacity }} Joined
                            </span>
                            <div class="flex items-center gap-2">
                                <div style="flex-grow: 1; height: 10px; background: #E2E8F0; border-radius: 5px; overflow: hidden; position: relative;">
                                    <div style="width: {{ $percent }}%; height: 100%; background: {{ $percent >= 100 ? 'var(--success, #22c55e)' : 'var(--primary, #0047cc)' }}; border-radius: 5px; transition: width 0.3s ease;"></div>
                                </div>
                                <span style="font-size: 0.75rem; font-weight: 700; color: var(--text-high); min-width: 32px; text-align: right;">{{ $percent }}%</span>
                            </div>
                        </div>
                    </td>
                    <td style="white-space: nowrap;">
                        @php
                            $statusClass = 'badge-status-' . ($event->status === 'completed' ? 'ended' : $event->status);
                            $statusLabel = $event->status === 'completed' ? 'Ended' : ucfirst($event->status);
                        @endphp
                        <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                    </td>
                    <td style="white-space: nowrap;">
                        <div class="flex gap-2 items-center">
                            <a href="{{ route('organizer.events.tickets', $event) }}" class="btn btn-primary" style="padding: 4px 10px; font-size: 0.8rem; height: 32px;">Tickets</a>
                            <a href="{{ route('organizer.events.attendees', $event) }}" class="btn btn-primary" style="padding: 4px 10px; font-size: 0.8rem; height: 32px;">Attendees</a>
                            
                            <div class="dropdown">
                                <button class="btn btn-glass" style="padding: 4px 8px; font-size: 1rem; width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">&#8942;</button>
                                <div class="dropdown-content">
                                    <a href="{{ route('organizer.events.edit', $event) }}">Edit</a>
                                    <form action="{{ route('organizer.events.clone', $event) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" title="Clone this event" onclick="return confirm('Are you sure you want to clone this event?')">Clone</button>
                                    </form>
                                    <form action="{{ route('organizer.events.destroy', $event) }}" method="POST" style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" style="color: var(--danger);" onclick="return confirm('Delete this event?')">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted" style="padding: 4rem; background: #FFFFFF; border-radius: 12px; border: 1px dashed var(--border-highlight);">
                        <div style="font-size: 2.5rem; margin-bottom: 1rem;">📅</div>
                        You haven't created any events yet.<br><br>
                        <a href="{{ route('organizer.events.create') }}" class="btn btn-primary">Create Your First Event</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(session('success'))
    <!-- Simple JS Toast for Clone / Success -->
    <div id="toast" style="position: fixed; bottom: 20px; right: 20px; background: var(--success); color: white; padding: 1rem 1.5rem; border-radius: var(--radius-md); box-shadow: var(--shadow-md); z-index: 9999; display: flex; align-items: center; gap: 0.5rem; transform: translateY(100px); opacity: 0; transition: all 0.3s ease;">
        <span>✓ {{ session('success') }}</span>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toast = document.getElementById('toast');
            if(toast) {
                setTimeout(() => {
                    toast.style.transform = 'translateY(0)';
                    toast.style.opacity = '1';
                }, 100);
                setTimeout(() => {
                    toast.style.transform = 'translateY(100px)';
                    toast.style.opacity = '0';
                }, 4000);
            }
        });
    </script>
    @endif
</x-app-layout>
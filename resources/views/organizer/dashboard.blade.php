<x-app-layout>
    <x-slot name="header">
        <h2 class="h2 text-gradient">Organizer Dashboard</h2>
    </x-slot>

    <div class="grid grid-cols-4 gap-4 mb-8">
        <div class="glass-panel text-center p-4 hover-scale">
            <div class="text-2xl font-bold">{{ $eventsCount }}</div>
            <div class="text-muted text-sm">Total Events</div>
        </div>
        <div class="glass-panel text-center p-4 hover-scale">
            <div class="text-2xl font-bold text-green-400">{{ $activeEvents }}</div>
            <div class="text-muted text-sm">Active Events</div>
        </div>
        <div class="glass-panel text-center p-4 hover-scale">
            <div class="text-2xl font-bold text-blue-400">{{ $totalAttendees }}</div>
            <div class="text-muted text-sm">Total Attendees</div>
        </div>
        <div class="glass-panel text-center p-4 hover-scale">
            <div class="text-2xl font-bold text-yellow-400">₱{{ number_format($totalRevenue, 2) }}</div>
            <div class="text-muted text-sm">Total Revenue</div>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2">
            <div class="glass-panel">
                <h3 class="h3 text-gradient mb-4">Recent Events</h3>
                @if($recentEvents->count() > 0)
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Capacity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentEvents as $event)
                                <tr>
                                    <td><a href="{{ route('organizer.events.edit', $event) }}" class="text-blue-400">{{ $event->title }}</a></td>
                                    <td>{{ $event->start_date->format('M d, Y') }}</td>
                                    <td><span class="badge badge-primary">{{ ucfirst($event->status) }}</span></td>
                                    <td>
                                        @php $regCount = $event->registrationsCount(); @endphp
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <div class="progress-bar" style="flex: 1;">
                                                <div class="progress-fill" style="width: {{ $event->capacity > 0 ? min(100, ($regCount / $event->capacity) * 100) : 0 }}%;"></div>
                                            </div>
                                            <span class="text-sm text-muted">{{ $regCount }}/{{ $event->capacity }}</span>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center py-4">No events yet.</p>
                @endif
            </div>
        </div>
        <div class="col-span-1 flex flex-col gap-6">
            <div class="glass-panel text-center p-6 hover-scale">
                <h3 class="h3 mb-2">Create Event</h3>
                <p class="text-muted mb-4">Launch a new event</p>
                <a href="{{ route('organizer.events.create') }}" class="btn btn-primary">+ New Event</a>
            </div>
            <div class="glass-panel text-center p-6 hover-scale">
                <h3 class="h3 mb-2">Manage Events</h3>
                <p class="text-muted mb-4">View all your events</p>
                <a href="{{ route('organizer.events') }}" class="btn btn-glass">View All</a>
            </div>
        </div>
    </div>
</x-app-layout>
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="h2 text-gradient">Tickets for: {{ $event->title }}</h2>
            <div class="flex gap-4">
                <a href="{{ route('organizer.events') }}" class="btn btn-glass">Back to Events</a>
                <a href="{{ route('organizer.events.tickets.create', $event) }}" class="btn btn-primary">+ Create Ticket</a>
            </div>
        </div>
    </x-slot>

    <div class="glass-panel">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Ticket Name</th>
                        <th>Price (₱)</th>
                        <th>Quantity</th>
                        <th>Sold</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($event->tickets as $ticket)
                    <tr>
                        <td>{{ $ticket->name }}</td>
                        <td>₱{{ number_format($ticket->price, 2) }}</td>
                        <td>{{ $ticket->quantity }}</td>
                        <td>{{ $ticket->registrations()->count() }}</td>
                        <td>
                            <form action="{{ route('organizer.tickets.destroy', $ticket) }}" method="POST" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger" style="padding: 4px 10px; font-size: 0.8rem;">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted" style="padding: 3rem;">
                            No tickets created yet. <br><br>
                            <a href="{{ route('organizer.events.tickets.create', $event) }}" class="btn btn-glass">Create First Ticket</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>

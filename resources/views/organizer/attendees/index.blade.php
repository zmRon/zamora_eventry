<x-app-layout>
    <x-slot name="header">
        <h2 class="h2 text-gradient">Attendees for: {{ $event->title }}</h2>
    </x-slot>

    <div class="glass-panel">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Attendee Name</th>
                        <th>Email</th>
                        <th>Ticket Type</th>
                        <th>Registered On</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registrations as $registration)
                    <tr>
                        <td>{{ $registration->attendee->name }}</td>
                        <td>{{ $registration->attendee->email }}</td>
                        <td>{{ $registration->ticket->name ?? 'General' }}</td>
                        <td>{{ $registration->created_at->format('M d, Y H:i') }}</td>
                        <td>
                            <form action="{{ route('organizer.registrations.status', $registration) }}" method="POST" style="display:inline;">
                                @csrf @method('PUT')
                                <select name="status" class="form-control" onchange="this.form.submit()" style="padding: 2px 10px; font-size: 0.85rem; width: auto; display: inline-block;">
                                    <option value="pending" {{ $registration->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ $registration->status == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="checked-in" {{ $registration->status == 'checked-in' ? 'selected' : '' }}>Checked-In</option>
                                    <option value="cancelled" {{ $registration->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted" style="padding: 3rem;">
                            No attendees have registered for this event yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>

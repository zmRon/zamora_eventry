<x-app-layout>
    <x-slot name="header">
        <h2 class="h2 text-gradient">Create Ticket for: {{ $event->title }}</h2>
    </x-slot>

    <div class="glass-panel" style="max-width: 600px; margin: 0 auto;">
        <form action="{{ route('organizer.events.tickets.store', $event) }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Ticket Name</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. VIP Pass, General Admission" required>
            </div>
            <div class="form-group">
                <label class="form-label">Price (₱)</label>
                <input type="number" name="price" class="form-control" step="0.01" min="0" placeholder="0.00 for free" required>
            </div>
            <div class="form-group">
                <label class="form-label">Quantity Available</label>
                <input type="number" name="quantity" class="form-control" min="1" required>
            </div>
            
            <div class="mt-8 flex justify-between">
                <a href="{{ route('organizer.events.tickets', $event) }}" class="btn btn-glass">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Ticket</button>
            </div>
        </form>
    </div>
</x-app-layout>

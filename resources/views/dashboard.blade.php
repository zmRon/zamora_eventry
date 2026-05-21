<x-app-layout>
    <x-slot name="header">
        <h2 class="h2 text-gradient">Redirecting...</h2>
    </x-slot>

    <div class="glass-panel text-center">
        <p class="mb-4">You are being redirected to your dashboard.</p>
        <script>
            window.location.href = "{{ match(auth()->user()->role) {
                'admin' => route('admin.dashboard'),
                'organizer' => route('organizer.dashboard'),
                'attendee' => route('attendee.dashboard'),
                default => '/'
            } }}";
        </script>
        
        @if(auth()->user()->role === 'admin')
            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">Click here if not redirected</a>
        @elseif(auth()->user()->role === 'organizer')
            <a href="{{ route('organizer.dashboard') }}" class="btn btn-primary">Click here if not redirected</a>
        @else
            <a href="{{ route('attendee.dashboard') }}" class="btn btn-primary">Click here if not redirected</a>
        @endif
    </div>
</x-app-layout>

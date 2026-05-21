<x-app-layout>
    <x-slot name="header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2 class="h2 text-gradient">Notifications</h2>
            @if(auth()->user()->unreadNotifications->count() > 0)
                <form action="{{ route('notifications.markAsRead') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.85rem;">Mark All as Read</button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="glass-panel">
        @forelse($notifications as $notification)
            <div style="padding: 1.5rem; margin-bottom: 1rem; border: 1px solid var(--border); border-radius: var(--radius-md); background: {{ $notification->read_at ? 'var(--bg-surface)' : 'rgba(var(--primary-rgb), 0.05)' }}; {{ $notification->read_at ? '' : 'border-left: 4px solid var(--primary);' }} transition: all 0.2s;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <p style="margin: 0; font-size: 1.05rem; font-weight: {{ $notification->read_at ? '500' : '600' }}; color: var(--text-high);">
                            {{ collect($notification->data)->get('message', 'You have a new notification.') }}
                        </p>
                        <p style="margin: 0.5rem 0 0 0; font-size: 0.85rem; color: var(--text-muted);">
                            {{ $notification->created_at->format('M d, Y h:i A') }} ({{ $notification->created_at->diffForHumans() }})
                        </p>
                    </div>
                    @if(!$notification->read_at)
                        <form action="{{ route('notifications.markAsRead') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id" value="{{ $notification->id }}">
                            <button type="submit" class="btn btn-glass" style="padding: 0.3rem 0.8rem; font-size: 0.75rem;">Mark as read</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div style="text-align: center; padding: 4rem 2rem; color: var(--text-muted);">
                <span style="font-size: 3rem; display: block; margin-bottom: 1rem;">📭</span>
                <p style="font-size: 1.1rem;">You don't have any notifications yet.</p>
            </div>
        @endforelse

        <div style="margin-top: 2rem;">
            {{ $notifications->links() }}
        </div>
    </div>
</x-app-layout>

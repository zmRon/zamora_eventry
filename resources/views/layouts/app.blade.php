<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

        <!-- Styles -->
        <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ time() }}">
    </head>
    <body>
        <nav class="navbar">
            <div class="container flex justify-between items-center">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2" style="text-decoration: none; display: inline-flex; align-items: center; gap: 0.6rem; font-family: 'Inter', sans-serif;">
                    <div style="position: relative; display: flex; align-items: center; justify-content: center; width: 34px; height: 34px; background: linear-gradient(135deg, var(--primary) 0%, #002D88 100%); border-radius: 10px; box-shadow: 0 4px 12px rgba(0, 71, 204, 0.2); flex-shrink: 0;">
                        <!-- Stylized Ticket Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 18px; height: 18px; color: white; transform: rotate(-15deg);">
                            <path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/>
                            <path d="M13 5v14"/>
                        </svg>
                        <div style="position: absolute; bottom: -2px; right: -2px; width: 10px; height: 10px; background: var(--accent); border-radius: 50%; border: 2px solid var(--bg-surface);"></div>
                    </div>
                    <span style="font-weight: 800; font-size: 1.4rem; letter-spacing: -0.04em; color: var(--text-high);">
                        Event<span style="color: var(--accent);">ry</span>
                    </span>
                </a>
                <div class="nav-links">
                    @auth
                        @if(auth()->user()->role === 'admin')
                            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a>
                            <a href="{{ route('admin.transactions.index') }}" class="nav-link {{ request()->routeIs('admin.transactions*') ? 'active' : '' }}">Transactions</a>
                            <a href="{{ route('admin.users') }}" class="nav-link {{ request()->routeIs('admin.users*') || request()->routeIs('admin.categories*') ? 'active' : '' }}">Admin Panel</a>
                        @elseif(auth()->user()->role === 'organizer')
                            <a href="{{ route('organizer.dashboard') }}" class="nav-link {{ request()->routeIs('organizer.dashboard') ? 'active' : '' }}">Dashboard</a>
                            <a href="{{ route('organizer.events') }}" class="nav-link {{ request()->routeIs('organizer.events*') || request()->routeIs('organizer.tickets*') ? 'active' : '' }}">My Events</a>
                            <a href="{{ route('organizer.wallet.index') }}" class="nav-link {{ request()->routeIs('organizer.wallet*') ? 'active' : '' }}">Earnings Wallet</a>
                        @elseif(auth()->user()->role === 'attendee')
                            <a href="{{ route('attendee.dashboard') }}" class="nav-link {{ request()->routeIs('attendee.dashboard') ? 'active' : '' }}">Dashboard</a>
                            <a href="{{ route('attendee.events') }}" class="nav-link {{ request()->routeIs('attendee.events*') ? 'active' : '' }}">Events</a>
                            <a href="{{ route('attendee.tickets') }}" class="nav-link {{ request()->routeIs('attendee.tickets*') ? 'active' : '' }}">My Bookings</a>
                            <a href="{{ route('attendee.calendar') }}" class="nav-link {{ request()->routeIs('attendee.calendar') ? 'active' : '' }}">Calendar</a>
                            <a href="{{ route('attendee.wallet.index') }}" class="nav-link {{ request()->routeIs('attendee.wallet*') ? 'active' : '' }}">My Wallet</a>
                        @endif
                    @endauth

                    <div style="width: 1px; height: 24px; background: var(--border); margin: 0 0.5rem;"></div>

                    @auth
                        @if(auth()->user()->role !== 'admin')
                            <a href="{{ auth()->user()->role === 'organizer' ? route('organizer.wallet.index') : route('attendee.wallet.index') }}" style="text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem; padding: 4px 10px; background: rgba(var(--primary-rgb), 0.08); border-radius: 6px; font-weight: 700; font-size: 0.85rem; color: var(--primary); transition: all 0.2s; margin-right: 0.5rem;" class="wallet-nav-indicator hover:scale-105">
                                <span>💳</span>
                                <span>₱{{ number_format(auth()->user()->credits, 2) }}</span>
                            </a>
                        @endif
                    @endauth

                    <div style="position: relative;" class="nav-dropdown-container">
                        <div class="notification-bell" onclick="toggleDropdown('notification-dropdown')" title="Notifications">
                            🔔
                            @if(auth()->user()->unreadNotifications->count() > 0)
                                <span class="notification-badge" id="notif-badge">{{ auth()->user()->unreadNotifications->count() }}</span>
                            @endif
                        </div>
                        
                        <!-- Dropdown -->
                        <div id="notification-dropdown" class="glass-panel" style="display: none; position: absolute; top: 100%; right: -50px; width: 320px; z-index: 100; margin-top: 10px; padding: 0;">
                            <div style="padding: 1rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                                <h4 style="margin: 0; font-weight: 600; font-size: 0.95rem;">Notifications</h4>
                                @if(auth()->user()->unreadNotifications->count() > 0)
                                    <button onclick="markAllAsRead()" style="background: none; border: none; color: var(--primary); font-size: 0.75rem; cursor: pointer;">Mark all as read</button>
                                @endif
                            </div>
                            <div style="max-height: 300px; overflow-y: auto;">
                                @forelse(auth()->user()->unreadNotifications->take(5) as $notification)
                                    <div style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--border); background: rgba(var(--primary-rgb), 0.05);">
                                        <p style="margin: 0; font-size: 0.85rem; color: var(--text-high);">{{ collect($notification->data)->get('message', 'You have a new notification.') }}</p>
                                        <span style="font-size: 0.7rem; color: var(--text-muted);">{{ $notification->created_at->diffForHumans() }}</span>
                                    </div>
                                @empty
                                    <div style="padding: 1.5rem 1rem; text-align: center; color: var(--text-muted); font-size: 0.85rem;">
                                        No new notifications
                                    </div>
                                @endforelse
                            </div>
                            <div style="padding: 0.75rem; text-align: center; border-top: 1px solid var(--border); background: rgba(0,0,0,0.02);">
                                <a href="{{ route('notifications.index') }}" style="font-size: 0.8rem; color: var(--primary); text-decoration: none; font-weight: 600;">View all notifications</a>
                            </div>
                        </div>
                    </div>
                    
                    <span style="margin: 0 0.5rem; font-weight: 600; font-size: 0.95rem; color: var(--text-med);">
                        {{ auth()->user()->name }}
                    </span>

                    <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}">Profile</a>
                    <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-glass" style="padding: 5px 15px; font-size: 0.8rem;">Log Out</button>
                    </form>
                </div>
            </div>
        </nav>

        <div class="container mt-8 pb-12 animate-fade-in">
            <!-- Page Heading -->
            @isset($header)
                <header style="margin-bottom: 2rem;">
                    {{ $header }}
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        <!-- Global Toast Container -->
        <div class="toast-container">
            @if(session('success'))
                <div class="toast success">
                    <span>✅</span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="toast error">
                    <span>❌</span>
                    <span>{{ session('error') }}</span>
                </div>
            @endif
            @if($errors->any())
                <div class="toast error">
                    <span>❌</span>
                    <span>Please check the form for errors.</span>
                </div>
            @endif
        </div>

        <script>
            // Toast auto-hide
            setTimeout(() => {
                document.querySelectorAll('.toast').forEach(toast => {
                    toast.style.opacity = '0';
                    setTimeout(() => toast.remove(), 300);
                });
            }, 4000);

            // Notification Dropdown Logic
            function toggleDropdown(id) {
                const el = document.getElementById(id);
                if (el) el.style.display = el.style.display === 'none' ? 'block' : 'none';
            }

            window.addEventListener('click', function(e) {
                if (!e.target.closest('.nav-dropdown-container')) {
                    const dropdown = document.getElementById('notification-dropdown');
                    if (dropdown) dropdown.style.display = 'none';
                }
            });

            function markAllAsRead() {
                fetch('{{ route('notifications.markAsRead') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                }).then(response => response.json()).then(data => {
                    if (data.success) {
                        const badge = document.getElementById('notif-badge');
                        if (badge) badge.style.display = 'none';
                        location.reload();
                    }
                });
            }
        </script>
    </body>
</html>

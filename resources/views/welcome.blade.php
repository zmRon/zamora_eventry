<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Eventry | Premium Events</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ time() }}">
        <style>
            .hero-section {
                min-height: calc(100vh - 80px);
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                text-align: center;
                position: relative;
                background: linear-gradient(135deg, #F0F4FF 0%, #FFFFFF 100%);
                padding-top: 5rem;
                padding-bottom: 5rem;
            }
        </style>
    </head>
    <body>
        <nav class="navbar">
            <div class="container flex justify-between items-center">
                <a href="/" class="flex items-center gap-2" style="text-decoration: none; display: inline-flex; align-items: center; gap: 0.6rem; font-family: 'Inter', sans-serif;">
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
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ route('dashboard') }}" class="btn btn-primary">Go to Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="nav-link">Log in</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn btn-primary">Sign up</a>
                            @endif
                        @endauth
                    @endif
                </div>
            </div>
        </nav>

        <main class="hero-section animate-fade-in">
            <div class="container">
                <h1 class="h1 mb-6" style="color: #0F172A;">Elevate your <br/><span style="color: var(--primary);">Event Experience.</span></h1>
                <p class="text-muted mb-8" style="max-width: 600px; font-size: 1.25rem; margin: 0 auto;">
                    The most innovative platform to host, manage, and attend premium events seamlessly. Built for organizers and attendees who demand perfection.
                </p>
                <div class="flex gap-4 justify-center">
                    <a href="{{ route('register') }}" class="btn btn-primary" style="padding: 1rem 2.5rem; font-size: 1.1rem;">Get Started</a>
                    <a href="{{ route('login') }}" class="btn btn-glass" style="padding: 1rem 2.5rem; font-size: 1.1rem; border: 2px solid var(--border);">Sign In</a>
                </div>
            </div>
        </main>
    </body>
</html>

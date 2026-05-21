<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ time() }}">
    </head>
    <body>
        <div class="min-h-screen flex items-center justify-center">
            <div class="glass-panel" style="width: 100%; max-width: 400px;">
                <div style="text-align: center; margin-bottom: 2rem;">
                    <a href="/" class="flex items-center gap-2 justify-center" style="text-decoration: none; display: inline-flex; align-items: center; gap: 0.6rem; font-family: 'Inter', sans-serif;">
                        <div style="position: relative; display: flex; align-items: center; justify-content: center; width: 42px; height: 42px; background: linear-gradient(135deg, var(--primary) 0%, #002D88 100%); border-radius: 12px; box-shadow: 0 4px 12px rgba(0, 71, 204, 0.2); flex-shrink: 0;">
                            <!-- Stylized Ticket Icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 22px; height: 22px; color: white; transform: rotate(-15deg);">
                                <path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/>
                                <path d="M13 5v14"/>
                            </svg>
                            <div style="position: absolute; bottom: -2px; right: -2px; width: 12px; height: 12px; background: var(--accent); border-radius: 50%; border: 2.5px solid var(--bg-surface);"></div>
                        </div>
                        <span style="font-weight: 800; font-size: 1.8rem; letter-spacing: -0.04em; color: var(--text-high);">
                            Event<span style="color: var(--accent);">ry</span>
                        </span>
                    </a>
                </div>

                {{ $slot }}
            </div>
        </div>
    </body>
</html>

<x-app-layout>
    <x-slot name="header">
        <h2 class="h2 text-gradient">
            Admin Dashboard
        </h2>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="glass-panel text-center hover-scale">
            <h3 class="h3">{{ $usersCount }}</h3>
            <p class="text-muted">Total Users</p>
            <div class="mt-2 text-sm {{ $usersTrend > 0 ? 'text-success' : ($usersTrend < 0 ? 'text-danger' : 'text-muted') }}">
                {{ $usersTrend > 0 ? '↑' : ($usersTrend < 0 ? '↓' : '—') }} {{ abs(round($usersTrend)) }}% vs last week
            </div>
            <a href="{{ route('admin.users') }}" class="btn btn-glass mt-4 w-full">Manage Users</a>
        </div>
        <div class="glass-panel text-center hover-scale">
            <h3 class="h3">{{ $eventsCount }}</h3>
            <p class="text-muted">Total Events</p>
            <div class="mt-2 text-sm {{ $eventsTrend > 0 ? 'text-success' : ($eventsTrend < 0 ? 'text-danger' : 'text-muted') }}">
                {{ $eventsTrend > 0 ? '↑' : ($eventsTrend < 0 ? '↓' : '—') }} {{ abs(round($eventsTrend)) }}% vs last week
            </div>
        </div>
        <div class="glass-panel text-center hover-scale">
            <h3 class="h3">{{ $categoriesCount }}</h3>
            <p class="text-muted">Total Categories</p>
            <a href="{{ route('admin.categories') }}" class="btn btn-glass mt-8 w-full">Manage Categories</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-8">
        <div>
            <h3 class="h3 text-gradient mb-4">Category Usage</h3>
            <div class="glass-panel p-4" style="display: flex; flex-direction: column; gap: 1rem;">
                @php
                    $maxEvents = $categoryUsage->max('events_count') ?: 1;
                @endphp
                @foreach($categoryUsage as $cat)
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <span class="font-semibold text-sm">{{ $cat->name }}</span>
                            <span class="text-xs text-muted">{{ $cat->events_count }} Events</span>
                        </div>
                        <div style="width: 100%; height: 8px; background-color: var(--border); border-radius: var(--radius-full); overflow: hidden;">
                            <div style="height: 100%; width: {{ ($cat->events_count / $maxEvents) * 100 }}%; background-color: var(--primary); border-radius: var(--radius-full); transition: width 0.3s ease;"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        
        <div>
            <h3 class="h3 text-gradient mb-4">Recent Activity</h3>
            <div class="glass-panel p-4" style="display: flex; flex-direction: column; gap: 1rem;">
                @forelse($activityFeed as $activity)
                    <div style="display: flex; align-items: start; gap: 1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">
                        <div style="font-size: 1.5rem;">
                            {{ $activity['type'] === 'user' ? '👤' : ($activity['type'] === 'event' ? '📅' : '🎟️') }}
                        </div>
                        <div>
                            <div class="font-bold text-sm" style="color: var(--text-high);">{{ $activity['desc'] }}</div>
                            <div class="text-xs text-muted">{{ $activity['time']->diffForHumans() }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-muted text-center py-4">No recent activity.</div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>

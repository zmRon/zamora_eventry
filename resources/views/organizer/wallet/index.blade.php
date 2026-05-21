<x-app-layout>
    <x-slot name="header">
        <h2 class="h2 text-gradient">Organizer Wallet</h2>
    </x-slot>

    <div class="grid grid-cols-1 gap-8">
        <!-- Dashboard Summary & Per-Event Breakdown -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Balance Card -->
            <div>
                <div class="balance-card" style="height: 100%; display: flex; flex-direction: column; justify-content: center;">
                    <div class="card-watermark">₱</div>
                    <span class="card-label">Total Wallet Balance</span>
                    <h1 class="card-amount">
                        ₱{{ number_format($user->credits, 2) }}
                    </h1>
                    <p class="card-desc">Includes active ticket sales net of cancellations & refunds.</p>
                </div>
            </div>

            <!-- Per Event Breakdown -->
            <div class="lg:col-span-2">
                <div class="glass-panel" style="background: #fff; padding: 1.5rem; border-radius: 12px; box-shadow: var(--shadow-md); height: 100%;">
                    <h3 class="h3 mb-3" style="color: var(--text-high);">Per-Event Revenue Breakdown</h3>
                    <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                        <table class="table" style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                            <thead>
                                <tr style="border-bottom: 1px solid var(--border); text-align: left; color: var(--text-med); font-weight: 600;">
                                    <th style="padding: 0.5rem;">Event Title</th>
                                    <th style="padding: 0.5rem; text-align: center;">Tickets Sold</th>
                                    <th style="padding: 0.5rem; text-align: right;">Gross Earnings</th>
                                    <th style="padding: 0.5rem; text-align: right;">Refund Deductions</th>
                                    <th style="padding: 0.5rem; text-align: right;">Net Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($eventBreakdown as $row)
                                <tr style="border-bottom: 1px solid var(--border); color: var(--text-high);">
                                    <td style="padding: 0.5rem;"><strong>{{ $row->title }}</strong></td>
                                    <td style="padding: 0.5rem; text-align: center;">{{ $row->tickets_sold }}</td>
                                    <td style="padding: 0.5rem; text-align: right; color: #22c55e; font-weight: 600;">₱{{ number_format($row->earnings, 2) }}</td>
                                    <td style="padding: 0.5rem; text-align: right; color: #ef4444;">-₱{{ number_format($row->refunds, 2) }}</td>
                                    <td style="padding: 0.5rem; text-align: right; font-weight: 700; color: var(--primary);">₱{{ number_format($row->net, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 1.5rem; color: var(--text-muted);">No events found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Card & Earnings History -->
        <div class="glass-panel" style="background: #fff; padding: 2rem; border-radius: 12px; box-shadow: var(--shadow-md);">
            <div class="flex justify-between items-center mb-4">
                <h3 class="h3" style="color: var(--text-high); margin-bottom: 0;">Earnings Transaction Log</h3>
            </div>

            <!-- Filters -->
            <form method="GET" action="{{ route('organizer.wallet.index') }}" class="filter-card mb-4" style="display: flex; flex-wrap: wrap; gap: 1rem; width: 100%; box-sizing: border-box;">
                <div style="flex: 1; min-width: 200px;">
                    <label>Filter by Event</label>
                    <select name="event_id" class="input w-full" style="height: 34px; padding: 0 0.5rem;">
                        <option value="">-- All Events --</option>
                        @foreach($events as $e)
                            <option value="{{ $e->id }}" {{ request('event_id') == $e->id ? 'selected' : '' }}>{{ $e->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="flex: 1; min-width: 150px;">
                    <label>From Date</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="input w-full">
                </div>
                <div style="flex: 1; min-width: 150px;">
                    <label>To Date</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="input w-full">
                </div>
                <div style="display: flex; align-items: flex-end; gap: 0.5rem; min-width: 150px;">
                    <button type="submit" class="btn btn-primary btn-filter" style="flex: 1; background: var(--primary);">Filter</button>
                    <a href="{{ route('organizer.wallet.index') }}" class="btn btn-glass btn-clear" style="flex: 1; text-decoration: none;">Clear</a>
                </div>
            </form>

            @if($transactions->count() > 0)
            <div class="table-responsive">
                <table class="table" style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--border); text-align: left; color: var(--text-med); font-weight: 600;">
                            <th style="padding: 0.75rem;">Date</th>
                            <th style="padding: 0.75rem;">Type</th>
                            <th style="padding: 0.75rem;">Event</th>
                            <th style="padding: 0.75rem;">Details</th>
                            <th style="padding: 0.75rem; text-align: right;">Amount</th>
                            <th style="padding: 0.75rem; text-align: right;">Running Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $tx)
                        <tr style="border-bottom: 1px solid var(--border); color: var(--text-high);">
                            <td style="padding: 0.75rem;" class="text-nowrap">{{ $tx->created_at->format('M d, Y h:i A') }}</td>
                            <td style="padding: 0.75rem;">
                                <span class="badge {{ $tx->type === 'earning' ? 'badge-success' : 'badge-warning' }}" style="font-size: 0.75rem;">
                                    {{ $tx->type === 'earning' ? 'Sale' : 'Refund' }}
                                </span>
                            </td>
                            <td style="padding: 0.75rem; font-weight: 600;">
                                {{ $tx->event->title ?? 'N/A' }}
                            </td>
                            <td style="padding: 0.75rem;" class="text-sm">
                                {{ $tx->description }}
                            </td>
                            <td style="padding: 0.75rem; text-align: right; font-weight: 600; color: {{ $tx->amount > 0 ? '#22c55e' : '#ef4444' }}">
                                {{ $tx->amount > 0 ? '+' : '' }}₱{{ number_format($tx->amount, 2) }}
                            </td>
                            <td style="padding: 0.75rem; text-align: right; color: var(--text-med);">
                                ₱{{ number_format($tx->running_balance, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $transactions->links() }}
            </div>
            @else
            <div class="empty-state" style="padding: 3rem; text-align: center;">
                <p class="text-muted">No earnings transactions found matching your criteria.</p>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>

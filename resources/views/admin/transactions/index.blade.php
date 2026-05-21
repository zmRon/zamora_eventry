<x-app-layout>
    <x-slot name="header">
        <h2 class="h2 text-gradient">System Transactions & Revenue</h2>
    </x-slot>

    <div class="grid grid-cols-1 gap-8">
        <!-- Platform Stat Card -->
        <div style="max-width: 400px;">
            <div class="balance-card">
                <div class="card-watermark">₱</div>
                <span class="card-label">Total Platform Revenue</span>
                <h1 class="card-amount" style="font-size: 2.5rem !important;">
                    ₱{{ number_format($totalPlatformRevenue, 2) }}
                </h1>
                <p class="card-desc">Calculated as the sum of all successful wallet top-up transactions.</p>
            </div>
        </div>

        <!-- Transactions Filter & List -->
        <div class="glass-panel" style="background: #fff; padding: 2rem; border-radius: 12px; box-shadow: var(--shadow-md);">
            <h3 class="h3 mb-4" style="color: var(--text-high);">All Platform Transactions</h3>

            <!-- Filters -->
            <form method="GET" action="{{ route('admin.transactions.index') }}" class="filter-card mb-4" style="display: flex; flex-wrap: wrap; gap: 1rem; width: 100%; box-sizing: border-box;">
                <div style="flex: 1.5; min-width: 220px;">
                    <label>Search User</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="input w-full" placeholder="Name or email...">
                </div>
                <div style="flex: 1; min-width: 150px;">
                    <label>Transaction Type</label>
                    <select name="type" class="input w-full" style="height: 34px; padding: 0 0.5rem;">
                        <option value="">-- All Types --</option>
                        <option value="topup" {{ request('type') === 'topup' ? 'selected' : '' }}>Topup</option>
                        <option value="purchase" {{ request('type') === 'purchase' ? 'selected' : '' }}>Purchase</option>
                        <option value="earning" {{ request('type') === 'earning' ? 'selected' : '' }}>Earning (Organizer)</option>
                        <option value="refund" {{ request('type') === 'refund' ? 'selected' : '' }}>Refund</option>
                        <option value="refund_deduction" {{ request('type') === 'refund_deduction' ? 'selected' : '' }}>Refund Deduction</option>
                        <option value="adjustment" {{ request('type') === 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                    </select>
                </div>
                <div style="flex: 1; min-width: 130px;">
                    <label>From Date</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="input w-full">
                </div>
                <div style="flex: 1; min-width: 130px;">
                    <label>To Date</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="input w-full">
                </div>
                <div style="display: flex; align-items: flex-end; gap: 0.5rem; min-width: 150px;">
                    <button type="submit" class="btn btn-primary btn-filter" style="flex: 1; background: var(--primary);">Filter</button>
                    <a href="{{ route('admin.transactions.index') }}" class="btn btn-glass btn-clear" style="flex: 1; text-decoration: none;">Clear</a>
                </div>
            </form>

            @if($transactions->count() > 0)
            <div class="table-responsive">
                <table class="table" style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--border); text-align: left; color: var(--text-med); font-weight: 600;">
                            <th style="padding: 0.75rem;">Date</th>
                            <th style="padding: 0.75rem;">User</th>
                            <th style="padding: 0.75rem;">Role</th>
                            <th style="padding: 0.75rem;">Type</th>
                            <th style="padding: 0.75rem;">Description</th>
                            <th style="padding: 0.75rem; text-align: right;">Amount</th>
                            <th style="padding: 0.75rem; text-align: right;">Running Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $tx)
                        <tr style="border-bottom: 1px solid var(--border); color: var(--text-high);">
                            <td style="padding: 0.75rem;" class="text-nowrap">{{ $tx->created_at->format('M d, Y h:i A') }}</td>
                            <td style="padding: 0.75rem;">
                                @if($tx->user)
                                    <strong>{{ $tx->user->name }}</strong>
                                    <span style="display: block; font-size: 0.75rem; color: var(--text-muted);">{{ $tx->user->email }}</span>
                                @else
                                    <span class="text-muted">Deleted User</span>
                                @endif
                            </td>
                            <td style="padding: 0.75rem;">
                                @if($tx->user)
                                    <span class="badge {{ $tx->user->role === 'admin' ? 'badge-warning' : ($tx->user->role === 'organizer' ? 'badge-success' : 'badge-primary') }}" style="font-size: 0.7rem; padding: 2px 6px;">
                                        {{ ucfirst($tx->user->role) }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td style="padding: 0.75rem;">
                                <span class="badge {{ in_array($tx->type, ['topup', 'earning', 'refund']) ? 'badge-success' : 'badge-warning' }}" style="font-size: 0.75rem;">
                                    {{ ucfirst($tx->type) }}
                                </span>
                            </td>
                            <td style="padding: 0.75rem; font-size: 0.85rem; max-width: 250px;">{{ $tx->description }}</td>
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
                <p class="text-muted">No transactions found matching your criteria.</p>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>

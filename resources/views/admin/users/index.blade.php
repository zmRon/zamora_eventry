<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="h2 text-gradient">Manage Users</h2>
            <a href="{{ route('admin.users.export') }}" class="btn btn-glass">Export CSV</a>
        </div>
    </x-slot>

    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="glass-panel text-center p-4">
            <div class="text-2xl font-bold">{{ $totalUsers }}</div>
            <div class="text-muted text-sm">Total Users</div>
        </div>
        <div class="glass-panel text-center p-4">
            <div class="text-2xl font-bold text-green-400">{{ $activeUsers }}</div>
            <div class="text-muted text-sm">Active</div>
        </div>
        <div class="glass-panel text-center p-4">
            <div class="text-2xl font-bold text-red-400">{{ $suspendedUsers }}</div>
            <div class="text-muted text-sm">Suspended</div>
        </div>
    </div>

    <div class="w-full">
        <div>
            <div class="glass-panel">
                <div class="filter-card" style="margin-bottom: 1rem; box-shadow: none; border-color: var(--border);">
                    <form action="{{ route('admin.users') }}" method="GET" class="flex gap-2 items-center">
                        <input type="text" name="search" value="{{ request('search') }}" class="input" style="width: 220px;" placeholder="Search users...">

                        <select name="role" class="input" style="width: 130px;">
                            <option value="">Role: All</option>
                            <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="organizer" {{ request('role') == 'organizer' ? 'selected' : '' }}>Organizer</option>
                            <option value="attendee" {{ request('role') == 'attendee' ? 'selected' : '' }}>Attendee</option>
                        </select>

                        <select name="status" class="input" style="width: 130px;">
                            <option value="">Status: All</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>

                        <button type="submit" class="btn btn-primary btn-filter">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width: 14px; height: 14px; flex-shrink: 0;">
                                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                            </svg>
                            Filter
                        </button>
                        
                        @if(request('search') || request('role') || request('status'))
                            <a href="{{ route('admin.users') }}" class="btn btn-glass btn-clear">Clear</a>
                        @endif
                    </form>
                </div>

                <form action="{{ route('admin.users.bulk') }}" method="POST" id="bulk-form">
                    @csrf
                    <div class="mb-4 flex gap-2">
                        <select name="action" class="input text-sm py-1" required>
                            <option value="">-- Bulk Actions --</option>
                            <option value="suspend">Suspend</option>
                            <option value="activate">Activate</option>
                            <option value="delete">Delete</option>
                        </select>
                        <button type="submit" class="btn btn-glass text-sm" onclick="return confirm('Execute bulk action?')">Apply</button>
                    </div>
                </form>

                <div class="table-container">
                    <table>
                            <thead>
                                <tr>
                                    <th><input type="checkbox" onchange="document.querySelectorAll('.user-cb').forEach(cb => cb.checked = this.checked)"></th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th style="white-space: nowrap;">
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'role', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}">
                                            Role {!! request('sort') === 'role' ? (request('direction') === 'asc' ? '↑' : '↓') : '' !!}
                                        </a>
                                    </th>
                                    <th style="white-space: nowrap;">
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'status', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}">
                                            Status {!! request('sort') === 'status' ? (request('direction') === 'asc' ? '↑' : '↓') : '' !!}
                                        </a>
                                    </th>
                                    <th style="white-space: nowrap;">Wallet Balance</th>
                                    <th style="white-space: nowrap;">Last Login</th>
                                    <th style="white-space: nowrap;">
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}">
                                            Joined {!! request('sort') === 'created_at' ? (request('direction') === 'asc' ? '↑' : '↓') : '' !!}
                                        </a>
                                    </th>
                                    <th style="white-space: nowrap;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                <tr>
                                    <td>
                                        @if($user->id !== auth()->id())
                                            <input type="checkbox" name="users[]" value="{{ $user->id }}" class="user-cb" form="bulk-form">
                                        @endif
                                    </td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td style="white-space: nowrap;"><span class="badge badge-primary">{{ ucfirst($user->role) }}</span></td>
                                    <td style="white-space: nowrap;">
                                        <span class="badge {{ $user->status === 'active' ? 'badge-primary' : 'badge-danger' }}">{{ ucfirst($user->status) }}</span>
                                    </td>
                                    <td style="white-space: nowrap;"><strong>₱{{ number_format($user->credits, 2) }}</strong></td>
                                    <td style="white-space: nowrap;">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</td>
                                    <td style="white-space: nowrap;">{{ $user->created_at->format('M d, Y') }}</td>
                                    <td style="white-space: nowrap;">
                                        <div class="flex gap-2 items-center">
                                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-glass" style="padding: 4px 10px; font-size: 0.8rem;">Edit</a>
                                            <button type="button" class="btn btn-glass" style="padding: 4px 10px; font-size: 0.8rem; background: rgba(0, 71, 204, 0.1);" onclick="openAdjustModal({{ $user->id }}, '{{ addslashes($user->name) }}', {{ $user->credits }})">Adjust</button>

                                            @if($user->id !== auth()->id())
                                                <form action="{{ route('admin.users.suspend', $user) }}" method="POST" style="display:inline;">
                                                    @csrf @method('PUT')
                                                    @if($user->status === 'active')
                                                        <input type="hidden" name="duration" value="7">
                                                        <button type="submit" class="btn btn-glass" style="padding: 4px 10px; font-size: 0.8rem; background: rgba(239, 68, 68, 0.15);" onclick="return confirm('Suspend user for 7 days?')">Suspend</button>
                                                    @else
                                                        <button type="submit" class="btn btn-glass" style="padding: 4px 10px; font-size: 0.8rem; background: rgba(34, 197, 94, 0.15);">Activate</button>
                                                    @endif
                                                </form>

                                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" style="display:inline;">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-danger" style="padding: 4px 10px; font-size: 0.8rem;" onclick="return confirm('Delete user?')">Delete</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                <div class="mt-4">
                    {{ $users->links() }}
                </div>
            </div>
        </div>

    </div>

    <!-- Adjust Balance Modal -->
    <div id="adjustBalanceModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
        <div class="glass-panel" style="width: 100%; max-width: 480px; padding: 2rem; background: #fff; border-radius: 12px; box-shadow: var(--shadow-lg); text-align: left;">
            <h3 class="h3 mb-4" style="color: var(--primary); display: flex; align-items: center; gap: 0.5rem; font-family: 'Inter', sans-serif;">
                <span>💸</span> Adjust User Balance
            </h3>
            <p class="mb-4 text-sm text-muted">
                Adjusting balance for <strong id="adjustUserName" style="color: var(--text-high);">-</strong> (Current: <strong id="adjustCurrentBalance" style="color: var(--primary);">-</strong>)
            </p>
            <form id="adjustBalanceForm" method="POST" action="">
                @csrf
                <div class="form-group mb-3">
                    <label class="form-label" style="font-weight: 600;">Adjustment Type</label>
                    <select name="type" class="input w-full" style="height: 38px; padding: 0 0.5rem;" required>
                        <option value="add">➕ Add Credits</option>
                        <option value="subtract">➖ Subtract Credits</option>
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label" style="font-weight: 600;">Amount (₱)</label>
                    <input type="number" name="amount" class="input w-full" min="0.01" step="0.01" required placeholder="0.00">
                </div>
                <div class="form-group mb-4">
                    <label class="form-label" style="font-weight: 600;">Reason / Description</label>
                    <input type="text" name="description" class="input w-full" required placeholder="e.g. Special promotional bonus, Goodwill refund">
                </div>
                <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                    <button type="button" onclick="closeAdjustModal()" class="btn btn-glass" style="height: 38px; font-size: 0.85rem; padding: 0 1.25rem;">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="height: 38px; font-size: 0.85rem; padding: 0 1.25rem;">Submit Adjustment</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAdjustModal(userId, name, credits) {
            document.getElementById('adjustUserName').innerText = name;
            document.getElementById('adjustCurrentBalance').innerText = '₱' + credits.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            
            // Set form action dynamically
            const form = document.getElementById('adjustBalanceForm');
            form.action = `/admin/users/${userId}/adjust-balance`;
            
            document.getElementById('adjustBalanceModal').style.display = 'flex';
        }

        function closeAdjustModal() {
            document.getElementById('adjustBalanceModal').style.display = 'none';
        }
    </script>
</x-app-layout>
<x-app-layout>
    <x-slot name="header">
        <h2 class="h2 text-gradient">My Wallet</h2>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Wallet Actions -->
        <div>
            <!-- Balance Card -->
            <div class="balance-card" style="margin-bottom: 2rem;">
                <div class="card-watermark">₱</div>
                <span class="card-label">Available Balance</span>
                <h1 class="card-amount">
                    ₱{{ number_format($user->credits, 2) }}
                </h1>
                <p class="card-desc">Max wallet capacity: ₱50,000.00</p>
            </div>

            <!-- Top Up Form Card -->
            <div class="glass-panel" style="background: #fff; padding: 2rem; border-radius: 12px; box-shadow: var(--shadow-md);">
                <h3 class="h3 mb-4" style="color: var(--text-high); display: flex; align-items: center; gap: 0.5rem;">
                    <span>⚡</span> Top Up Credits
                </h3>
                
                <form action="{{ route('attendee.wallet.topup') }}" method="POST" id="topupForm">
                    @csrf
                    <div class="form-group mb-4">
                        <label class="form-label" style="font-weight: 600;">Amount (₱)</label>
                        <input type="number" name="amount" id="topupAmount" class="input w-full" placeholder="Enter amount" min="50" max="10000" step="0.01" value="{{ old('amount') }}" required>
                        <span class="text-xs text-muted mt-1 block">Min: ₱50.00 | Max: ₱10,000.00 per top-up</span>
                    </div>

                    <!-- Quick buttons -->
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; margin-bottom: 1.5rem;">
                        <button type="button" onclick="setAmount(100)" class="btn btn-glass" style="font-size: 0.8rem; padding: 6px;">+₱100</button>
                        <button type="button" onclick="setAmount(500)" class="btn btn-glass" style="font-size: 0.8rem; padding: 6px;">+₱500</button>
                        <button type="button" onclick="setAmount(1000)" class="btn btn-glass" style="font-size: 0.8rem; padding: 6px;">+₱1,000</button>
                        <button type="button" onclick="setAmount(2000)" class="btn btn-glass" style="font-size: 0.8rem; padding: 6px;">+₱2,000</button>
                        <button type="button" onclick="setAmount(5000)" class="btn btn-glass" style="font-size: 0.8rem; padding: 6px;">+₱5,000</button>
                        <button type="button" onclick="setAmount(10000)" class="btn btn-glass" style="font-size: 0.8rem; padding: 6px;">+₱10,000</button>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label" style="font-weight: 600;">Payment Method</label>
                        <select name="payment_method" id="paymentMethod" class="input w-full" style="height: 38px; padding: 0 0.5rem;" onchange="togglePaymentFields()" required>
                            <option value="gcash">GCash</option>
                            <option value="maya">Maya</option>
                            <option value="card">Credit/Debit Card</option>
                        </select>
                    </div>

                    <!-- GCash / Maya Account Number Field -->
                    <div class="form-group mb-4" id="accountNumberGroup">
                        <label class="form-label" style="font-weight: 600;">Account Number / Mobile Number</label>
                        <input type="text" name="account_number" id="accountNumber" class="input w-full" placeholder="e.g. 09171234567" value="{{ old('account_number') }}" required>
                        @error('account_number')
                            <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Card Number Field (initially hidden) -->
                    <div class="form-group mb-4" id="cardNumberGroup" style="display: none;">
                        <label class="form-label" style="font-weight: 600;">Card Number</label>
                        <input type="text" name="card_number" id="cardNumber" class="input w-full" placeholder="e.g. 4111 2222 3333 4444" value="{{ old('card_number') }}">
                        @error('card_number')
                            <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-full" style="justify-content: center; height: 42px; background: var(--accent); border-color: var(--accent);">
                        Confirm Top Up
                    </button>
                </form>

                <script>
                    document.getElementById('topupForm').addEventListener('submit', function(e) {
                        const amount = parseFloat(document.getElementById('topupAmount').value);
                        if (amount < 0) {
                            e.preventDefault();
                            alert('Amount cannot be negative.');
                            return false;
                        }
                        const method = document.getElementById('paymentMethod').value;
                        if (method === 'card') {
                            const cardNumber = document.getElementById('cardNumber').value.trim();
                            if (cardNumber.includes('-') && !/^\d[0-9\s-]*$/.test(cardNumber)) {
                                e.preventDefault();
                                alert('Card number must be positive.');
                                return false;
                            }
                        } else {
                            const accountNumber = document.getElementById('accountNumber').value.trim();
                            if (accountNumber.includes('-') && !/^\d[0-9\s-]*$/.test(accountNumber)) {
                                e.preventDefault();
                                alert('Account/Phone number must be positive.');
                                return false;
                            }
                        }
                    });
                </script>

                <script>
                    function togglePaymentFields() {
                        const method = document.getElementById('paymentMethod').value;
                        const accountGroup = document.getElementById('accountNumberGroup');
                        const cardGroup = document.getElementById('cardNumberGroup');
                        const accountInput = document.getElementById('accountNumber');
                        const cardInput = document.getElementById('cardNumber');

                        if (method === 'card') {
                            accountGroup.style.display = 'none';
                            accountInput.removeAttribute('required');
                            cardGroup.style.display = 'block';
                            cardInput.setAttribute('required', 'required');
                        } else {
                            accountGroup.style.display = 'block';
                            accountInput.setAttribute('required', 'required');
                            cardGroup.style.display = 'none';
                            cardInput.removeAttribute('required');
                        }
                    }
                    // Trigger initial toggle
                    document.addEventListener('DOMContentLoaded', togglePaymentFields);
                </script>
            </div>
        </div>

        <!-- Transaction Logs -->
        <div class="lg:col-span-2" id="history">
            <div class="glass-panel" style="background: #fff; padding: 2rem; border-radius: 12px; box-shadow: var(--shadow-md);">
                <h3 class="h3 mb-4" style="color: var(--text-high);">Transaction History</h3>

                @if($transactions->count() > 0)
                <div class="table-responsive">
                    <table class="table" style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--border); text-align: left; color: var(--text-med); font-weight: 600;">
                                <th style="padding: 0.75rem;">Date</th>
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
                                    <span class="badge {{ $tx->type === 'topup' || $tx->type === 'refund' ? 'badge-success' : ($tx->type === 'purchase' ? 'badge-primary' : 'badge-warning') }}" style="font-size: 0.75rem;">
                                        {{ ucfirst($tx->type) }}
                                    </span>
                                </td>
                                <td style="padding: 0.75rem; max-width: 250px;" class="text-sm">{{ $tx->description }}</td>
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
                    <p class="text-muted">No transactions found for this account.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function setAmount(val) {
            document.getElementById('topupAmount').value = val;
        }
    </script>
</x-app-layout>

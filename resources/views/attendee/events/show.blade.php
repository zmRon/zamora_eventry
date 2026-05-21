<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="h2 text-gradient">{{ $event->title }}</h2>
            <form action="{{ route('attendee.events.favorite', $event) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-glass">
                    {{ auth()->user()->favoriteEvents()->where('event_id', $event->id)->exists() ? '❤️ Remove from Favorites' : '🤍 Add to Favorites' }}
                </button>
            </form>
        </div>
    </x-slot>

    <div class="grid grid-cols-2 gap-6">
        <div>
            <div class="glass-panel mb-6">
                @if($event->image_url)
                    <img src="{{ $event->image_url }}" alt="{{ $event->title }}" style="width: 100%; max-height: 300px; object-fit: cover; border-radius: 8px; margin-bottom: 16px;">
                @endif
                <h3 class="h3 mb-4">Event Details</h3>
                <p class="mb-4">{{ $event->description }}</p>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-muted">Organizer</p>
                        <p class="mb-2"><strong>{{ $event->organizer->name ?? 'N/A' }}</strong></p>
                    </div>
                    <div>
                        <p class="text-sm text-muted">Category</p>
                        <p class="mb-2"><strong>{{ $event->category->name ?? 'N/A' }}</strong></p>
                    </div>
                    <div>
                        <p class="text-sm text-muted">Status</p>
                        <p><span class="badge {{ $event->status === 'upcoming' ? 'badge-primary' : ($event->status === 'ongoing' ? 'badge-success' : 'badge-warning') }}">{{ ucfirst($event->status) }}</span></p>
                    </div>
                    <div>
                        <p class="text-sm text-muted">Location</p>
                        <p><strong>{{ $event->location }}</strong></p>
                    </div>
                    <div>
                        <p class="text-sm text-muted">Capacity</p>
                        @php $regCount = $event->registrationsCount(); @endphp
                        <div>
                            <strong>{{ $regCount }}/{{ $event->capacity }}</strong> registered
                            <div class="progress-bar mt-1">
                                <div class="progress-fill" style="width: {{ $event->capacity > 0 ? min(100, ($regCount / $event->capacity) * 100) : 0 }}%;"></div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm text-muted">Starts</p>
                        <p><strong>{{ $event->start_date->format('F d, Y h:i A') }}</strong></p>
                    </div>
                    <div>
                        <p class="text-sm text-muted">Ends</p>
                        <p><strong>{{ $event->end_date->format('F d, Y h:i A') }}</strong></p>
                    </div>
                </div>
            </div>

            @if($event->feedbacks->count() > 0)
            <div class="glass-panel">
                <h3 class="h3 mb-4">
                    Reviews
                    <span class="text-sm text-muted ml-2">
                        @for($i = 1; $i <= 5; $i++)
                            <span style="color: {{ $i <= round($avgRating) ? '#f59e0b' : '#3f3f46' }};">★</span>
                        @endfor
                        ({{ number_format($avgRating, 1) }} / {{ $ratingCount }} reviews)
                    </span>
                </h3>
                @foreach($event->feedbacks as $feedback)
                <div class="review-card">
                    <div class="review-header">
                        <strong>{{ $feedback->attendee->name }}</strong>
                        <span>
                            @for($i = 1; $i <= 5; $i++)
                                <span style="color: {{ $i <= $feedback->rating ? '#f59e0b' : '#3f3f46' }};">★</span>
                            @endfor
                        </span>
                    </div>
                    <p class="text-sm">{{ $feedback->comment }}</p>
                    <p class="text-xs text-muted mt-1">{{ $feedback->created_at->diffForHumans() }}</p>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <div>
            <div class="glass-panel">
                <h3 class="h3 mb-4">Register for Event</h3>
                @if(in_array($event->status, ['upcoming', 'ongoing']))
                    @if($event->tickets->count() > 0)
                        <form id="registrationForm" action="{{ route('attendee.events.register', $event) }}" method="POST">
                            @csrf
                            <input type="hidden" name="checkout" value="1">
                            <div class="form-group">
                                <label class="form-label">Select Ticket</label>
                                @foreach($event->tickets as $ticket)
                                <label class="glass-panel p-3 mb-2 block" style="cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <input type="radio" name="ticket_id" value="{{ $ticket->id }}" data-price="{{ $ticket->price }}" data-name="{{ $ticket->name }}" required>
                                        <div>
                                            <strong>{{ $ticket->name }}</strong>
                                            @if($ticket->registrations()->count() >= $ticket->quantity)
                                                <span class="badge badge-warning ml-2">Sold Out</span>
                                            @endif
                                        </div>
                                    </div>
                                    <span class="text-lg font-bold {{ $ticket->price == 0 ? 'text-primary' : '' }}">{{ $ticket->price > 0 ? '₱' . number_format($ticket->price, 2) : 'Free' }}</span>
                                </label>
                                @endforeach
                            </div>
                             @if($event->registrations()->where('attendee_id', auth()->id())->exists())
                                <div class="glass-panel" style="padding: 1.5rem; text-align: center; border-radius: 12px; border: 1px solid #A7F3D0; background: #ECFDF5; margin-top: 1rem; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.08); transform: none !important;">
                                    <div style="font-size: 1.75rem; margin-bottom: 0.5rem;">🎉</div>
                                    <h4 style="font-weight: 700; color: #065F46; font-size: 1rem; margin-bottom: 0.25rem;">You're Registered!</h4>
                                    <p style="color: #047857; font-size: 0.85rem; margin-bottom: 1.25rem;">Your spot is secured for this event. We look forward to seeing you!</p>
                                    
                                    <a href="{{ route('attendee.events.calendar.download', $event) }}" class="btn btn-primary" style="width: 100%; justify-content: center; gap: 0.5rem; background: linear-gradient(135deg, #059669 0%, #047857 100%); box-shadow: 0 4px 12px rgba(4, 120, 87, 0.2); border: none; font-size: 0.85rem; height: 38px; padding: 0 1rem; border-radius: 30px; text-decoration: none; color: #FFFFFF; font-weight: 700;">
                                        <span>📅</span> Add to Calendar (.ics)
                                    </a>
                                </div>
                            @else
                                <button type="button" onclick="handleRegistration()" class="btn btn-primary mt-4" style="width: 100%;">Register Now</button>
                            @endif
                        </form>


                    @else
                        <p class="text-muted">No tickets available for this event yet.</p>
                    @endif
                @else
                    <p class="text-muted">Registration is not available for {{ $event->status }} events.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Order Summary Modal -->
    <div id="orderSummaryModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
        <div class="modal-card" style="width: 90%; max-width: 480px; padding: 2.25rem; background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); text-align: left; position: relative; display: flex; flex-direction: column; box-sizing: border-box;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem;">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(0, 71, 204, 0.08); display: flex; align-items: center; justify-content: center; font-size: 1.25rem; color: #0047cc;">🛒</div>
                <div>
                    <h3 class="h3" style="color: #0f172a; font-family: 'Inter', sans-serif; font-weight: 800; margin: 0; font-size: 1.25rem; line-height: 1.2;">Order Summary</h3>
                    <p style="color: #64748b; font-size: 0.75rem; margin: 0; margin-top: 2px;">Review and confirm your ticket selection</p>
                </div>
            </div>

            <div style="background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 10px; padding: 1.25rem; margin-bottom: 1.25rem; box-sizing: border-box;">
                <div class="flex justify-between mb-3" style="align-items: center; display: flex; justify-content: space-between;">
                    <span style="color: #64748b; font-size: 0.85rem; font-weight: 500;">Event</span>
                    <strong style="color: #0f172a; font-size: 0.9rem; font-weight: 700; text-align: right; max-width: 60%;">{{ $event->title }}</strong>
                </div>
                <div class="flex justify-between mb-3" style="align-items: center; display: flex; justify-content: space-between;">
                    <span style="color: #64748b; font-size: 0.85rem; font-weight: 500;">Ticket Type</span>
                    <strong id="modalTicketName" style="color: #0047cc; font-size: 0.85rem; font-weight: 700; background: rgba(0, 71, 204, 0.06); padding: 2px 10px; border-radius: 6px; border: 1px solid rgba(0, 71, 204, 0.12);">-</strong>
                </div>
                <div class="flex justify-between" style="align-items: center; border-top: 1px dashed #e2e8f0; padding-top: 10px; margin-top: 10px; display: flex; justify-content: space-between;">
                    <span style="color: #64748b; font-size: 0.85rem; font-weight: 500;">Price</span>
                    <strong id="modalTicketPrice" style="color: #0f172a; font-size: 1.1rem; font-weight: 800;">-</strong>
                </div>
            </div>

            <div style="background: linear-gradient(to right, #f8fafc, #f1f5f9); border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.25rem; margin-bottom: 1.5rem; box-sizing: border-box;">
                <div class="flex justify-between mb-3" style="align-items: center; display: flex; justify-content: space-between;">
                    <span style="color: #64748b; font-size: 0.85rem; font-weight: 500; display: flex; align-items: center; gap: 4px;">
                        <span>💳</span> Wallet Balance
                    </span>
                    <strong style="color: #334155; font-size: 0.95rem; font-weight: 700;">₱{{ number_format(auth()->user()->credits, 2) }}</strong>
                </div>
                <div class="flex justify-between" id="modalRemainingRow" style="align-items: center; border-top: 1px solid #e2e8f0; padding-top: 10px; margin-top: 10px; display: flex; justify-content: space-between;">
                    <span style="color: #64748b; font-size: 0.85rem; font-weight: 500;">Remaining Balance</span>
                    <strong id="modalRemainingBalance" style="font-size: 1.05rem; font-weight: 800;">-</strong>
                </div>
            </div>

            <div id="insufficientBalanceAlert" class="p-3 mb-4 rounded" style="background: #fff5f5; border: 1px solid #fee2e2; color: #e53e3e; display: none; font-size: 0.825rem; line-height: 1.4; border-radius: 8px; box-sizing: border-box;">
                ⚠️ <strong>Insufficient Credits!</strong> You do not have enough credits in your wallet to purchase this ticket. Please top up your wallet to continue.
            </div>

            <div style="display: flex; gap: 0.75rem; justify-content: flex-end; align-items: center; height: 42px; box-sizing: border-box;">
                <button type="button" onclick="closeModal('orderSummaryModal')" class="btn" style="height: 42px; min-width: 100px; font-size: 0.875rem; font-weight: 600; padding: 0 1.25rem; background: #f1f5f9; border: 1px solid #cbd5e1; color: #475569; border-radius: 8px; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; box-sizing: border-box; margin: 0; outline: none; line-height: 1;">Cancel</button>
                <a href="{{ route('attendee.wallet.index') }}" id="topUpBtn" class="btn" style="display: none; height: 42px; min-width: 140px; font-size: 0.875rem; font-weight: 700; padding: 0 1.25rem; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); border: none; color: white; border-radius: 8px; text-decoration: none; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(234, 88, 12, 0.2); cursor: pointer; box-sizing: border-box; margin: 0; text-align: center; font-family: 'Inter', sans-serif; line-height: 1;">Top Up Wallet</a>
                <button type="button" id="confirmPurchaseBtn" onclick="submitRegistration()" class="btn" style="height: 42px; min-width: 150px; font-size: 0.875rem; font-weight: 700; padding: 0 1.25rem; background: linear-gradient(135deg, #0047cc 0%, #003bb3 100%); border: none; color: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 71, 204, 0.2); cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; box-sizing: border-box; margin: 0; outline: none; line-height: 1;">Confirm Purchase</button>
            </div>
        </div>
    </div>

    <script>
        let selectedTicketId = null;
        let selectedTicketPrice = 0;
        const walletBalance = {{ auth()->user()->credits }};

        function handleRegistration() {
            const selectedRadio = document.querySelector('input[name="ticket_id"]:checked');
            if (!selectedRadio) {
                alert('Please select a ticket type.');
                return;
            }

            selectedTicketId = selectedRadio.value;
            selectedTicketPrice = parseFloat(selectedRadio.getAttribute('data-price'));
            const ticketName = selectedRadio.getAttribute('data-name');

            // Update modal content
            document.getElementById('modalTicketName').innerText = ticketName;
            document.getElementById('modalTicketPrice').innerText = '₱' + selectedTicketPrice.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            const remaining = walletBalance - selectedTicketPrice;
            document.getElementById('modalRemainingBalance').innerText = '₱' + remaining.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            const confirmBtn = document.getElementById('confirmPurchaseBtn');
            const topUpBtn = document.getElementById('topUpBtn');
            const alertBox = document.getElementById('insufficientBalanceAlert');
            const remainingRow = document.getElementById('modalRemainingRow');

            if (remaining < 0) {
                // Insufficient
                alertBox.style.display = 'block';
                confirmBtn.style.display = 'none';
                topUpBtn.style.display = 'inline-flex';
                remainingRow.style.color = '#ef4444';
            } else {
                // Sufficient
                alertBox.style.display = 'none';
                confirmBtn.style.display = 'inline-flex';
                topUpBtn.style.display = 'none';
                remainingRow.style.color = 'inherit';
            }

            // Open Modal
            document.getElementById('orderSummaryModal').style.display = 'flex';
        }

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        function submitRegistration() {
            const form = document.getElementById('registrationForm');
            form.action = "{{ route('attendee.confirm', $event) }}";
            form.submit();
        }
    </script>
</x-app-layout>
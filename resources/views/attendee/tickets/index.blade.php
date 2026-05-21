<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="h2 text-gradient">My Tickets</h2>
            <a href="{{ route('attendee.events') }}" class="btn btn-glass">Browse Events</a>
        </div>
    </x-slot>

    @if($registrations->count() > 0)
    <div class="grid grid-cols-2 gap-6">
        @foreach($registrations as $registration)
        <div class="glass-panel ticket-card">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="h3 mb-1">{{ $registration->ticket->event->title ?? 'N/A' }}</h3>
                    <p class="text-sm text-muted">{{ $registration->ticket->event->category->name ?? 'General' }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-4">
                <div>
                    <p class="text-xs text-muted">Date</p>
                    <p class="text-sm"><strong>{{ $registration->ticket->event->start_date->format('M d, Y h:i A') ?? 'N/A' }}</strong></p>
                </div>
                <div>
                    <p class="text-xs text-muted">Location</p>
                    <p class="text-sm"><strong>{{ $registration->ticket->event->location ?? 'N/A' }}</strong></p>
                </div>
                <div>
                    <p class="text-xs text-muted">Ticket Type</p>
                    <p class="text-sm"><strong>{{ $registration->ticket->name ?? 'N/A' }}</strong></p>
                </div>
                <div>
                    <p class="text-xs text-muted">Status</p>
                    <span class="badge {{ $registration->status === 'approved' ? 'badge-success' : 'badge-primary' }}" title="{{ $registration->status === 'pending' ? 'Awaiting organizer approval' : '' }}">{{ ucfirst($registration->status) }}</span>
                </div>
            </div>

            <p class="text-xs text-muted mb-3">Booking #{{ str_pad($registration->id, 6, '0', STR_PAD_LEFT) }} | Serial: <span style="font-family: monospace; font-weight: bold; color: var(--text-high);">EVT-{{ str_pad($registration->id, 5, '0', STR_PAD_LEFT) }}-{{ strtoupper(substr(md5($registration->id . '-' . $registration->attendee_id), 0, 6)) }}</span> | {{ $registration->created_at->format('M d, Y') }}</p>

            @if($registration->ticket->event && $registration->ticket->event->status === 'completed')
                @php $existingFeedback = $registration->ticket->event->feedbacks()->where('attendee_id', auth()->id())->first(); @endphp
                @if(!$existingFeedback)
                    <div style="border-top: 1px solid var(--border); padding-top: 12px; margin-top: 12px;">
                        <p class="text-sm font-bold mb-2">Leave a Review</p>
                        <form action="{{ route('attendee.events.feedback', $registration->ticket->event) }}" method="POST">
                            @csrf
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-sm text-muted">Rating:</span>
                                <div class="star-rating">
                                    @for($i = 5; $i >= 1; $i--)
                                        <input type="radio" name="rating" value="{{ $i }}" id="star{{ $registration->id }}-{{ $i }}" style="display:none;" required>
                                        <label for="star{{ $registration->id }}-{{ $i }}" class="star" onclick="this.parentElement.querySelectorAll('.star').forEach((s, idx) => { s.classList.toggle('filled', idx >= (5-{{ $i }})); });">★</label>
                                    @endfor
                                </div>
                            </div>
                            <textarea name="comment" class="form-control mb-2" rows="2" placeholder="Write your review..." required></textarea>
                            <button type="submit" class="btn btn-primary" style="padding: 4px 12px; font-size: 0.8rem;">Submit Review</button>
                        </form>
                    </div>
                @else
                    <p class="text-sm text-muted">✓ You've already reviewed this event</p>
                @endif
            @endif

            <div class="mt-4 flex justify-between items-center" style="border-top: 1px dashed var(--border-highlight); padding-top: 16px; margin-top: auto;">
                <div>
                    @if(in_array($registration->ticket->event->status ?? '', ['upcoming', 'ongoing']) && !$registration->ticket->event->start_date->isPast())
                        <button type="button" class="btn btn-sm" style="color: var(--danger); border: 1px solid var(--danger); background: transparent; padding: 4px 10px; font-size: 0.8rem;" onclick="confirmCancellation('{{ $registration->id }}', '{{ $registration->ticket->event->title }}', {{ $registration->ticket->price }})">Cancel Registration</button>
                        <form id="cancel-form-{{ $registration->id }}" action="{{ route('attendee.registrations.destroy', $registration) }}" method="POST" style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endif
                </div>
                <a href="{{ route('attendee.bookings.download', $registration) }}" class="btn btn-primary" style="padding: 6px 16px; font-size: 0.85rem; text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">
                    Download Ticket
                </a>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="glass-panel">
        <div class="empty-state">
            <h3 class="h3 mb-4">No Tickets Yet</h3>
            <p>You haven't registered for any events yet.</p>
            <a href="{{ route('attendee.events') }}" class="btn btn-primary mt-4">Browse Events</a>
        </div>
    </div>
    @endif



    <!-- Cancellation Confirmation Modal -->
    <div id="cancelConfirmationModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
        <div class="glass-panel" style="width: 100%; max-width: 480px; padding: 2rem; background: #fff; border-radius: 12px; box-shadow: var(--shadow-lg); text-align: left;">
            <h3 class="h3 mb-4" style="color: var(--danger); display: flex; align-items: center; gap: 0.5rem; font-family: 'Inter', sans-serif;">
                <span>⚠️</span> Cancel Booking
            </h3>
            <p class="mb-4 text-sm" style="color: var(--text-med);">
                Are you sure you want to cancel your booking for <strong id="cancelEventTitle" style="color: var(--text-high);">-</strong>?
            </p>
            <div class="glass-panel p-4 mb-4" style="background: rgba(239, 68, 68, 0.05); border-color: rgba(239, 68, 68, 0.15);">
                <div class="flex justify-between">
                    <span class="text-sm text-muted">Refund Amount:</span>
                    <strong id="cancelRefundAmount" style="color: var(--success); font-size: 1.1rem;">₱0.00</strong>
                </div>
                <p class="text-xs text-muted mt-2" style="margin-bottom: 0;">
                    Credits will be refunded immediately to your wallet.
                </p>
            </div>
            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" onclick="closeCancelModal()" class="btn btn-glass" style="height: 38px; font-size: 0.85rem; padding: 0 1.25rem;">Keep Booking</button>
                <button type="button" id="confirmCancelSubmitBtn" class="btn" style="height: 38px; font-size: 0.85rem; padding: 0 1.25rem; background: var(--danger); border-color: var(--danger); color: white;">Confirm Cancellation</button>
            </div>
        </div>
    </div>

    <script>
        let activeCancelId = null;

        function confirmCancellation(id, eventTitle, price) {
            activeCancelId = id;
            document.getElementById('cancelEventTitle').innerText = eventTitle;
            document.getElementById('cancelRefundAmount').innerText = '₱' + price.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            document.getElementById('cancelConfirmationModal').style.display = 'flex';
            
            document.getElementById('confirmCancelSubmitBtn').onclick = function() {
                document.getElementById('cancel-form-' + activeCancelId).submit();
            };
        }

        function closeCancelModal() {
            document.getElementById('cancelConfirmationModal').style.display = 'none';
        }
    </script>
</x-app-layout>
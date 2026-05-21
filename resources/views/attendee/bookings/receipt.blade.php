<x-app-layout>
    <x-slot name="header">
        <h2 class="h2 text-gradient">Booking Confirmation</h2>
    </x-slot>

    <div style="max-width: 650px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; background: rgba(34, 197, 94, 0.1); border-radius: 50%; color: #22c55e; font-size: 2rem; margin-bottom: 1rem; border: 2px solid #22c55e;">
                ✓
            </div>
            <h3 class="h3" style="color: var(--text-high);">Thank you for your booking!</h3>
            <p class="text-muted" style="margin-top: 0.25rem;">Your registration has been successfully confirmed. A receipt is shown below.</p>
        </div>

        <!-- Receipt Card -->
        <div class="glass-panel" style="background: #fff; padding: 2rem; border-radius: 12px; box-shadow: var(--shadow-md); position: relative; overflow: hidden; margin-bottom: 2rem;">
            <!-- Top brand band -->
            <div style="position: absolute; top: 0; left: 0; right: 0; height: 6px; background: linear-gradient(90deg, var(--primary) 0%, var(--accent) 100%);"></div>

            <div class="flex justify-between items-start" style="border-bottom: 1px dashed var(--border); padding-bottom: 1.5rem; margin-bottom: 1.5rem;">
                <div>
                    <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--text-med); letter-spacing: 0.05em;">Booking Receipt</span>
                    <h4 style="font-size: 1.25rem; font-weight: 800; color: var(--text-high); margin-top: 0.25rem;">#{{ str_pad($registration->id, 6, '0', STR_PAD_LEFT) }}</h4>
                    <p class="text-sm text-muted" style="margin-top: 0.15rem;">Date: {{ $registration->created_at->format('F d, Y h:i A') }}</p>
                </div>
                <div style="text-align: right;">
                    <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--text-med); letter-spacing: 0.05em;">Payment Status</span>
                    <div style="margin-top: 0.25rem;">
                        <span class="badge badge-success" style="font-size: 0.85rem; padding: 4px 12px;">Paid</span>
                    </div>
                </div>
            </div>

            <!-- Event Details Section -->
            <div style="margin-bottom: 1.5rem;">
                <h5 style="font-size: 0.8rem; text-transform: uppercase; font-weight: 700; color: var(--text-med); letter-spacing: 0.05em; margin-bottom: 0.75rem;">Event Information</h5>
                <div class="glass-panel p-3" style="background: rgba(0, 71, 204, 0.03); border-color: rgba(0, 71, 204, 0.1); border-radius: 8px;">
                    <strong style="color: var(--text-high); display: block; font-size: 1rem; margin-bottom: 0.25rem;">{{ $registration->ticket->event->title }}</strong>
                    <span style="font-size: 0.85rem; color: var(--text-muted); display: block;">📍 {{ $registration->ticket->event->location }}</span>
                    <span style="font-size: 0.85rem; color: var(--text-muted); display: block; margin-top: 0.15rem;">📅 {{ $registration->ticket->event->start_date->format('l, F j, Y \a\t g:i A') }}</span>
                </div>
            </div>

            <!-- Pricing Breakdown -->
            <div style="margin-bottom: 2rem;">
                <h5 style="font-size: 0.8rem; text-transform: uppercase; font-weight: 700; color: var(--text-med); letter-spacing: 0.05em; margin-bottom: 0.75rem;">Payment Information</h5>
                <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--border); text-align: left; color: var(--text-med); font-weight: 600;">
                            <th style="padding: 0.5rem 0;">Item Description</th>
                            <th style="padding: 0.5rem 0; text-align: right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="border-bottom: 1px solid var(--border); color: var(--text-high);">
                            <td style="padding: 0.75rem 0;">
                                {{ $registration->ticket->name }} Ticket
                                <span style="display: block; font-size: 0.75rem; color: var(--text-muted);">1 x ₱{{ number_format($registration->ticket->price, 2) }}</span>
                            </td>
                            <td style="padding: 0.75rem 0; text-align: right; font-weight: 600;">₱{{ number_format($registration->ticket->price, 2) }}</td>
                        </tr>
                        <tr style="color: var(--text-high); font-size: 1.05rem;">
                            <td style="padding: 1rem 0 0 0; font-weight: 700;">Total Charged</td>
                            <td style="padding: 1rem 0 0 0; text-align: right; font-weight: 800; color: var(--primary);">₱{{ number_format($registration->ticket->price, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Ticket Serial Number -->
            <div style="text-align: center; border-top: 1px dashed var(--border); padding-top: 2rem;">
                <p style="font-size: 0.7rem; text-transform: uppercase; font-weight: 700; color: var(--text-med); letter-spacing: 0.1em; margin-bottom: 0.75rem;">Ticket Serial Number</p>
                <div style="display: inline-block; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 10px; padding: 0.75rem 2rem;">
                    <span style="font-family: 'Courier New', Courier, monospace; font-size: 1.4rem; font-weight: 800; color: #0047cc; letter-spacing: 3px;">EVT-{{ str_pad($registration->id, 5, '0', STR_PAD_LEFT) }}-{{ strtoupper(substr(md5($registration->id . '-' . $registration->attendee_id), 0, 6)) }}</span>
                </div>
                <p class="text-xs text-muted" style="margin-top: 0.75rem; margin-bottom: 0;">Present this serial number at the event entrance for verification.</p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div style="display: flex; gap: 1rem; justify-content: center; margin-bottom: 3rem;">
            <a href="{{ route('attendee.events.calendar.download', $registration->ticket->event) }}" class="btn btn-glass" style="display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none;">
                <span>📅</span> Add to Calendar
            </a>
            <a href="{{ route('attendee.bookings.download', $registration) }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none;">
                <span>📥</span> Download Ticket PDF
            </a>
            <a href="{{ route('attendee.tickets') }}" class="btn btn-glass" style="display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none;">
                <span>🎫</span> View Bookings
            </a>
        </div>
    </div>
</x-app-layout>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket - #{{ str_pad($registration->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #1e293b;
            margin: 0;
            padding: 20px;
            background: #ffffff;
        }
        .ticket {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 30px;
            max-width: 600px;
            margin: 0 auto;
            position: relative;
        }
        .header {
            border-bottom: 2px dashed #cbd5e1;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #0047cc;
        }
        .ticket-id {
            float: right;
            font-size: 16px;
            font-weight: bold;
            color: #64748b;
        }
        .clear {
            clear: both;
        }
        .event-title {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            margin: 10px 0;
        }
        .details-table {
            width: 100%;
            margin-bottom: 25px;
        }
        .details-table td {
            padding: 8px 0;
            vertical-align: top;
        }
        .label {
            font-size: 11px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .value {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
        }
        .serial-section {
            text-align: center;
            border-top: 1px solid #e2e8f0;
            padding-top: 25px;
            margin-top: 20px;
        }
        .serial-code-wrapper {
            display: inline-block;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 10px 24px;
            border-radius: 8px;
        }
        .serial-code {
            font-family: 'Courier New', Courier, monospace;
            font-size: 18px;
            font-weight: bold;
            color: #0047cc;
            letter-spacing: 2px;
        }
        .footer-note {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="header">
            <span class="logo">Eventry Ticket</span>
            <span class="ticket-id">#{{ str_pad($registration->id, 6, '0', STR_PAD_LEFT) }}</span>
            <div class="clear"></div>
        </div>

        <div class="event-title">{{ $registration->ticket->event->title }}</div>

        <table class="details-table">
            <tr>
                <td width="50%">
                    <div class="label">Attendee Name</div>
                    <div class="value">{{ $registration->attendee->name }}</div>
                </td>
                <td width="50%">
                    <div class="label">Ticket Type</div>
                    <div class="value">{{ $registration->ticket->name }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="label">Date & Time</div>
                    <div class="value">{{ $registration->ticket->event->start_date->format('M d, Y h:i A') }}</div>
                </td>
                <td>
                    <div class="label">Location</div>
                    <div class="value">{{ $registration->ticket->event->location }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="label">Price</div>
                    <div class="value">₱{{ number_format($registration->ticket->price, 2) }}</div>
                </td>
                <td>
                    <div class="label">Status</div>
                    <div class="value">{{ ucfirst($registration->status) }}</div>
                </td>
            </tr>
        </table>

        <div class="serial-section">
            <div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: bold; letter-spacing: 1px; margin-bottom: 8px;">TICKET SERIAL NUMBER</div>
            <div class="serial-code-wrapper">
                <span class="serial-code">EVT-{{ str_pad($registration->id, 5, '0', STR_PAD_LEFT) }}-{{ strtoupper(substr(md5($registration->id . '-' . $registration->attendee_id), 0, 6)) }}</span>
            </div>
            <div class="footer-note">Please present this ticket serial number at the event entrance for verification.</div>
        </div>
    </div>
</body>
</html>

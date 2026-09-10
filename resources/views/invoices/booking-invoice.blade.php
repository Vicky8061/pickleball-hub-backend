<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice #INV-2026-{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            margin: 0;
            padding: 24px;
            font-size: 13px;
            line-height: 1.5;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .brand-logo {
            font-size: 24px;
            font-weight: 800;
            color: #198754;
            letter-spacing: -0.5px;
        }

        .brand-subtext {
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 2px;
        }

        .invoice-badge {
            display: inline-block;
            background-color: #e8f5ee;
            color: #198754;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .invoice-title {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            margin: 6px 0 2px 0;
        }

        .invoice-meta {
            font-size: 12px;
            color: #64748b;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .info-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px 16px;
            vertical-align: top;
            width: 48%;
        }

        .info-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .info-name {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .info-detail {
            color: #475569;
            font-size: 12px;
            line-height: 1.4;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .details-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 10px 14px;
            text-align: left;
        }

        .details-table td {
            padding: 12px 14px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
        }

        .details-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .fw-bold {
            font-weight: 700;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .summary-box {
            width: 45%;
            margin-left: auto;
            border-collapse: collapse;
        }

        .summary-box td {
            padding: 6px 12px;
            font-size: 13px;
        }

        .summary-box .total-row td {
            border-top: 2px solid #0f172a;
            border-bottom: 2px solid #0f172a;
            padding: 10px 12px;
            font-size: 16px;
            font-weight: 800;
            color: #198754;
        }

        .terms-box {
            background-color: #f8fafc;
            border-left: 4px solid #198754;
            padding: 12px 16px;
            border-radius: 4px;
            margin-top: 20px;
            font-size: 11px;
            color: #64748b;
        }

        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td style="vertical-align: top;">
                <div class="brand-logo">Pickleball Hub</div>
                <div class="brand-subtext">Official Court Reservation Receipt</div>
            </td>
            <td style="text-align: right; vertical-align: top;">
                <div class="invoice-badge">{{ strtoupper($booking->payment_status ?? 'PAID') }}</div>
                <div class="invoice-title">Invoice #INV-2026-{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}</div>
                <div class="invoice-meta">
                    <strong>Date:</strong> {{ $booking->paid_at ? $booking->paid_at->format('M d, Y h:i A') : ($booking->created_at ? $booking->created_at->format('M d, Y') : now()->format('M d, Y')) }}
                </div>
                <div class="invoice-meta">
                    <strong>Status:</strong> Confirmed & Paid
                </div>
            </td>
        </tr>
    </table>

    <!-- Customer & Venue Details -->
    <table class="info-table">
        <tr>
            <td class="info-card">
                <div class="info-title">Player Details</div>
                <div class="info-name">{{ $booking->user->name ?? 'Pickleball Player' }}</div>
                <div class="info-detail">Email: {{ $booking->user->email ?? 'N/A' }}</div>
                @if(!empty($booking->user->phone))
                <div class="info-detail">Phone: {{ $booking->user->phone }}</div>
                @endif
                <div class="info-detail">Player ID: #{{ $booking->user_id }}</div>
            </td>
            <td style="width: 4%;"></td>
            <td class="info-card">
                <div class="info-title">Venue & Court</div>
                <div class="info-name">{{ $booking->court->court_name ?? ($booking->court->name ?? 'Court') }}</div>
                <div class="info-detail">{{ $booking->court->address ?? 'Pickleball Hub Arena' }}</div>
                @if(!empty($booking->court->city))
                <div class="info-detail">{{ $booking->court->city }}</div>
                @endif
                @if(!empty($booking->court->owner))
                <div class="info-detail">Hosted By: {{ $booking->court->owner->name }}</div>
                @endif
            </td>
        </tr>
    </table>

    <!-- Line Items Table -->
    <table class="details-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 45%;">Reservation Description</th>
                <th style="width: 25%;" class="text-center">Date & Time Slot</th>
                <th style="width: 10%;" class="text-center">Duration</th>
                <th style="width: 15%;" class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>
                    <strong style="color: #0f172a;">{{ $booking->court->court_name ?? ($booking->court->name ?? 'Pickleball Court') }}</strong>
                    <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                        Standard Court Booking - Confirmed Match Slot
                    </div>
                </td>
                <td class="text-center">
                    <div><strong>{{ \Carbon\Carbon::parse($booking->booking_date)->format('D, M d, Y') }}</strong></div>
                    <div style="font-size: 11px; color: #64748b;">
                        @if($booking->timeSlot)
                            {{ \Carbon\Carbon::parse($booking->timeSlot->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($booking->timeSlot->end_time)->format('h:i A') }}
                        @else
                            {{ $booking->slot_time ?? 'Reserved Slot' }}
                        @endif
                    </div>
                </td>
                <td class="text-center">1 Hour</td>
                <td class="text-right fw-bold">
                    ₹{{ number_format($booking->court_price ?? (max(0, $booking->total_amount - ($booking->platform_fee ?? 50))), 2) }}
                </td>
            </tr>
            <tr>
                <td>2</td>
                <td>
                    <strong style="color: #0f172a;">Platform & Convenience Fee</strong>
                    <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                        Instant court slot reservation, equipment access & lighting charges
                    </div>
                </td>
                <td class="text-center">Standard</td>
                <td class="text-center">--</td>
                <td class="text-right fw-bold">
                    ₹{{ number_format($booking->platform_fee ?? 50, 2) }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Pricing Summary -->
    <table class="summary-table">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 4px;">Payment Information</div>
                <div style="font-size: 12px; color: #334155;">
                    <div><strong>Method:</strong> {{ strtoupper($booking->payment_method ?? 'Razorpay') }}</div>
                    @if(!empty($booking->razorpay_payment_id))
                    <div><strong>Transaction ID:</strong> <span style="font-family: monospace;">{{ $booking->razorpay_payment_id }}</span></div>
                    @endif
                    @if(!empty($booking->razorpay_order_id))
                    <div><strong>Order ID:</strong> <span style="font-family: monospace;">{{ $booking->razorpay_order_id }}</span></div>
                    @endif
                    <div><strong>Paid On:</strong> {{ $booking->paid_at ? $booking->paid_at->format('M d, Y h:i A') : now()->format('M d, Y') }}</div>
                </div>
            </td>
            <td style="width: 50%; vertical-align: top;">
                <table class="summary-box">
                    <tr>
                        <td class="text-right">Court Fee:</td>
                        <td class="text-right fw-bold">₹{{ number_format($booking->court_price ?? (max(0, $booking->total_amount - ($booking->platform_fee ?? 50))), 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-right">Platform Fee:</td>
                        <td class="text-right fw-bold">+ ₹{{ number_format($booking->platform_fee ?? 50, 2) }}</td>
                    </tr>
                    <tr class="total-row">
                        <td class="text-right">Total Paid:</td>
                        <td class="text-right">₹{{ number_format($booking->total_amount, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Important Rules & Etiquette -->
    <div class="terms-box">
        <strong style="color: #0f172a; display: block; margin-bottom: 4px;">Important Player Guidelines & Court Rules:</strong>
        • Please arrive at least 10 minutes prior to your reserved slot start time.<br>
        • Non-marking athletic footwear is strictly required on court surfaces.<br>
        • Present this digital invoice or Booking ID (#{{ $booking->id }}) at the front desk for check-in.<br>
        • Cancellations or reschedule requests must comply with the venue policy.
    </div>

    <!-- Footer -->
    <div class="footer">
        Thank you for playing with <strong>Pickleball Hub</strong>! • Support: support@pickleballhub.com • www.pickleballhub.com
    </div>

</body>
</html>

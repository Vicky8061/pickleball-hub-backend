<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed - Pickleball Hub</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b;">

    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="table-layout: fixed;">
        <tr>
            <td align="center" style="padding: 30px 15px;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                    
                    <!-- Header -->
                    <tr>
                        <td align="center" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 36px 30px; color: #ffffff;">
                            <div style="background-color: #198754; color: #ffffff; display: inline-block; padding: 6px 16px; border-radius: 20px; font-size: 12px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 12px;">
                                MATCH RESERVATION CONFIRMED
                            </div>
                            <h1 style="margin: 0 0 8px 0; font-size: 26px; font-weight: 800; letter-spacing: -0.5px;">You're Ready to Play!</h1>
                            <p style="margin: 0; color: #cbd5e1; font-size: 15px;">
                                Hi {{ $booking->user->name ?? 'Player' }}, your court booking has been successfully paid and confirmed.
                            </p>
                        </td>
                    </tr>

                    <!-- Booking Summary Card -->
                    <tr>
                        <td style="padding: 30px 30px 20px 30px;">
                            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
                                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                    <tr>
                                        <td style="padding-bottom: 12px; border-bottom: 1px dashed #cbd5e1;">
                                            <span style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700; letter-spacing: 0.5px; display: block;">Venue & Court</span>
                                            <strong style="font-size: 18px; color: #0f172a;">{{ $booking->court->court_name ?? ($booking->court->name ?? 'Pickleball Court') }}</strong>
                                            <div style="font-size: 13px; color: #475569; margin-top: 2px;">{{ $booking->court->address ?? 'Pickleball Hub Arena' }}</div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 12px 0; border-bottom: 1px dashed #cbd5e1;">
                                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                <tr>
                                                    <td width="50%">
                                                        <span style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700; display: block;">Date</span>
                                                        <strong style="font-size: 14px; color: #0f172a;">{{ \Carbon\Carbon::parse($booking->booking_date)->format('D, M d, Y') }}</strong>
                                                    </td>
                                                    <td width="50%">
                                                        <span style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700; display: block;">Time Slot</span>
                                                        <strong style="font-size: 14px; color: #0f172a;">
                                                            @if($booking->timeSlot)
                                                                {{ \Carbon\Carbon::parse($booking->timeSlot->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($booking->timeSlot->end_time)->format('h:i A') }}
                                                            @else
                                                                {{ $booking->slot_time ?? 'Reserved Slot' }}
                                                            @endif
                                                        </strong>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 12px 0 4px 0;">
                                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                <tr>
                                                    <td width="50%">
                                                        <span style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700; display: block;">Booking ID</span>
                                                        <span style="font-size: 14px; font-weight: 700; color: #0f172a;">#{{ $booking->id }}</span>
                                                    </td>
                                                    <td width="50%">
                                                        <span style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700; display: block;">Total Paid</span>
                                                        <span style="font-size: 16px; font-weight: 800; color: #198754;">₹{{ number_format($booking->total_amount, 2) }}</span>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <!-- PDF Attached Notice -->
                            <div style="background-color: #e8f5ee; border-left: 4px solid #198754; border-radius: 6px; padding: 14px 18px; margin-bottom: 24px;">
                                <strong style="color: #198754; font-size: 14px; display: block; margin-bottom: 2px;">Official Tax Invoice Attached</strong>
                                <span style="font-size: 12px; color: #2e7d32;">
                                    A PDF copy of your reservation invoice <strong>#INV-2026-{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}.pdf</strong> is attached to this email for your records.
                                </span>
                            </div>

                            <!-- Guidelines -->
                            <h4 style="margin: 0 0 10px 0; font-size: 14px; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px;">Match Guidelines</h4>
                            <ul style="margin: 0 0 24px 0; padding-left: 20px; font-size: 13px; color: #475569; line-height: 1.6;">
                                <li>Arrive at the venue 10 minutes before your slot starts.</li>
                                <li>Non-marking sports shoes are strictly required on courts.</li>
                                <li>Show this email or booking ID at the court entrance desk.</li>
                            </ul>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 24px 30px; font-size: 12px; color: #94a3b8;">
                            <p style="margin: 0 0 4px 0; font-weight: 600; color: #64748b;">Pickleball Hub Platform</p>
                            <p style="margin: 0;">Need assistance? Reach out to support@pickleballhub.com</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>

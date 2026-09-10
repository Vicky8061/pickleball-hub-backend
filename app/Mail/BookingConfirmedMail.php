<?php

namespace App\Mail;

use App\Models\Booking;
use App\Services\Invoice\InvoiceService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingConfirmedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking->loadMissing(['user', 'court.owner', 'timeSlot']);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $courtName = $this->booking->court->court_name ?? ($this->booking->court->name ?? 'Court');
        $date = Carbon::parse($this->booking->booking_date)->format('M d, Y');

        return new Envelope(
            subject: "Booking Confirmed: {$courtName} ({$date})",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.booking_confirmed',
            with: [
                'booking' => $this->booking,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $invoiceService = app(InvoiceService::class);
        $pdfOutput = $invoiceService->output($this->booking);
        $fileName = $invoiceService->getFileName($this->booking);

        return [
            Attachment::fromData(fn () => $pdfOutput, $fileName)
                ->withMime('application/pdf'),
        ];
    }
}

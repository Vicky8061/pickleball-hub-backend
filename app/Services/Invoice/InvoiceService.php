<?php

namespace App\Services\Invoice;

use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfInstance;
use Symfony\Component\HttpFoundation\Response;

class InvoiceService
{
    /**
     * Generate the PDF invoice instance for a booking.
     */
    public function generatePdf(Booking $booking): DomPdfInstance
    {
        $booking->loadMissing(['user', 'court.owner', 'timeSlot']);

        return Pdf::loadView('invoices.booking-invoice', [
            'booking' => $booking,
        ])->setPaper('a4', 'portrait')
          ->setOptions([
              'isHtml5ParserEnabled' => true,
              'isRemoteEnabled' => true,
              'defaultFont' => 'Helvetica',
          ]);
    }

    /**
     * Get the formatted file name for a booking invoice.
     */
    public function getFileName(Booking $booking): string
    {
        return 'pickleball-invoice-' . str_pad((string) $booking->id, 5, '0', STR_PAD_LEFT) . '.pdf';
    }

    /**
     * Generate a downloadable response for the booking invoice.
     */
    public function download(Booking $booking): Response
    {
        return $this->generatePdf($booking)->download($this->getFileName($booking));
    }

    /**
     * Generate an inline streaming response for the booking invoice (view in browser).
     */
    public function stream(Booking $booking): Response
    {
        return $this->generatePdf($booking)->stream($this->getFileName($booking));
    }

    /**
     * Get the raw PDF binary string (e.g. for email attachments).
     */
    public function output(Booking $booking): string
    {
        return $this->generatePdf($booking)->output();
    }
}

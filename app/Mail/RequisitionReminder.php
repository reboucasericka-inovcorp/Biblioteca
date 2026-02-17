<?php

namespace App\Mail;

use App\Models\Requisition;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RequisitionReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Requisition $requisition
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Lembrete: Devolução amanhã - ' . $this->requisition->sequential_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.requisition-reminder',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

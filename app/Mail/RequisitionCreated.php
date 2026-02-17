<?php

namespace App\Mail;

use App\Models\Requisition;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RequisitionCreated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Requisition $requisition
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Requisição criada - ' . $this->requisition->sequential_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.requisition-created',
        );
    }

    public function attachments(): array
    {
        return [];
    }

    
}

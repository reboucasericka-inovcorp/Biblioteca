<?php

namespace App\Mail;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReviewCreatedForAdmin extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Review $review,
        public string $reviewUrl
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Novo review pendente - '.$this->review->book?->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.review-created-for-admin',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

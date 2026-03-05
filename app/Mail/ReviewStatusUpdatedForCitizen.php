<?php

namespace App\Mail;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReviewStatusUpdatedForCitizen extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Review $review
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Estado do seu review - '.$this->review->book?->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.review-status-updated-for-citizen',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

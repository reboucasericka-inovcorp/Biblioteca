<?php

namespace App\Mail;

use App\Models\Book;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookAvailableNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Book $book
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Livro disponível novamente - '.$this->book->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.book-available-notification',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

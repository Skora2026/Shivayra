<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public array $payload,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subjectMap = [
            'orders' => 'Order & Shipping',
            'returns' => 'Returns & Refunds',
            'product' => 'Product information',
            'wholesale' => 'Wholesale / Partnership',
            'other' => 'General',
        ];

        return new Envelope(
            replyTo: [new Address($this->payload['email'], $this->payload['name'])],
            subject: '[Contact] '.($subjectMap[$this->payload['inquiry']] ?? 'General').' — '
                .$this->payload['name'],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-message',
        );
    }
}

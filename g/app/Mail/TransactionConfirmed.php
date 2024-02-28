<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TransactionConfirmed extends Mailable
{
    use Queueable, SerializesModels;
    private $amount, $transactionMessage, $destination;
    /**
     * Create a new message instance.
     */
    public function __construct($amount, $destination, $transactionMessage)
    {
        $this->amount = $amount;
        $this->transactionMessage = $transactionMessage;
        $this->destination = $destination;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmation de transaction.',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.transfer-conf',
            with: [
                "transactionMessage"=>$this->transactionMessage,
                "destination"=>$this->destination,
                "amount"=>$this->amount,
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
        return [];
    }
}

<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderPlacedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $order;

    public $isAdmin;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, bool $isAdmin = false)
    {
        $this->order = $order;
        $this->isAdmin = $isAdmin;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = $this->isAdmin
            ? "New Order Received - Order #{$this->order->id}"
            : "Order Confirmation - Order #{$this->order->id}";

        return $this->subject($subject)
            ->view('emails.order-placed');
    }
}

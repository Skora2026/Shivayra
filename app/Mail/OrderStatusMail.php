<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderStatusMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $order;

    /** One of: shipped, delivered, cancelled, returned, refunded. */
    public $state;

    /**
     * @param Order  $order
     * @param string $state Lifecycle state this email announces.
     */
    public function __construct(Order $order, string $state)
    {
        $this->order = $order;
        $this->state = $state;
    }

    /**
     * Send with logging instead of throwing: a transport hiccup must never
     * abort the status change that triggered the email.
     */
    public static function sendTo(Order $order, string $state): void
    {
        try {
            Mail::to($order->email)->send(new self($order, $state));
        } catch (\Throwable $e) {
            Log::warning("Order #{$order->id} '{$state}' status email failed: {$e->getMessage()}");
        }
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subjects = [
            'shipped'   => "Your Order #{$this->order->id} Has Been Shipped",
            'delivered' => "Your Order #{$this->order->id} Has Been Delivered",
            'cancelled' => "Your Order #{$this->order->id} Has Been Cancelled",
            'returned'  => "Return Approved for Order #{$this->order->id}",
            'refunded'  => "Refund Processed for Order #{$this->order->id}",
        ];

        return $this->subject($subjects[$this->state] ?? "Update for Order #{$this->order->id}")
            ->view('emails.order-status');
    }
}

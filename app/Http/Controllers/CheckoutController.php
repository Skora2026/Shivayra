<?php

namespace App\Http\Controllers;

use App\Http\Requests\Checkout\PlaceOrderRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    protected $checkoutService;

    /**
     * Create a new Controller instance.
     */
    public function __construct(CheckoutService $checkoutService)
    {
        $this->checkoutService = $checkoutService;
    }

    /**
     * Render the checkout page and handle cancelled payments.
     */
    public function index(Request $request)
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to checkout.');
        }

        // Handle cancelled or failed payments to restore stock
        if (($request->query('payment') === 'cancelled' || $request->query('payment') === 'failed') && $request->has('order_id')) {
            $orderId = $request->query('order_id');
            $order = Order::where('id', $orderId)
                ->where('user_id', Auth::id())
                ->where('payment_status', 'pending')
                ->first();

            if ($order) {
                $order->update([
                    'payment_status' => 'failed',
                    'order_status' => 'cancelled',
                ]);

                // Restore stock: variant (if the item was a variant purchase)
                // AND parent product, mirroring how the sale deducted both
                foreach ($order->items as $item) {
                    if ($item->product_variant_id) {
                        ProductVariant::where('id', $item->product_variant_id)
                            ->increment('stock', $item->qty);
                    }
                    if ($item->product) {
                        $item->product->increment('stock', $item->qty);
                    }
                }

                Log::info("Order ID {$orderId} payment cancelled/failed and stock restored.");
                session()->flash('error', 'Payment was cancelled or failed. You can try again.');
            }
        }

        return view('front.checkout');
    }

    /**
     * Place order.
     *
     * @return RedirectResponse|View
     */
    /**
     * Garbage-collect abandoned Razorpay checkouts: pending, unpaid orders
     * that hold reserved stock forever. Frees anything still pending after a
     * day (Razorpay sessions are minutes, not hours — a day is generous).
     * Stock restore mirrors admin cancel.
     *
     * Razorpay ONLY by design: a COD order legitimately sits pending + unpaid
     * until it is fulfilled and must never be swept. These orders never got a
     * customer confirmation email, so the sweep also stays silent.
     */
    public static function sweepAbandonedRazorpayOrders(): void
    {
        Order::where('created_at', '<', now()->subDay())
            ->where('payment_method', 'razorpay')
            ->where('payment_status', 'pending')
            ->where('order_status', 'pending')
            ->with('items')
            ->chunkById(100, function ($orders): void {
                foreach ($orders as $stale) {
                    \DB::transaction(function () use ($stale): void {
                        $claimed = Order::where('id', $stale->id)
                            ->where('payment_status', 'pending')
                            ->where('order_status', 'pending')
                            ->update([
                                'payment_status' => 'failed',
                                'order_status' => 'cancelled',
                            ]);

                        if ($claimed) {
                            foreach ($stale->items as $item) {
                                if ($item->product_variant_id) {
                                    ProductVariant::where('id', $item->product_variant_id)
                                        ->increment('stock', $item->qty);
                                }
                                if ($item->product) {
                                    Product::where('id', $item->product->id)
                                        ->increment('stock', $item->qty);
                                }
                            }
                        }
                    });
                }
            });
    }

    public function placeOrder(PlaceOrderRequest $request)
    {
        // Verify Authentication
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to place an order.');
        }

        $data = $request->validated();

        // Abuse guard: cap order creation per user. Prevents both a runaway
        // double-click loop and scripted order flooding that hoards stock in
        // unpaid pending orders.
        if (RateLimiter::tooManyAttempts('checkout:'.$request->user()->id, 10)) {
            $seconds = RateLimiter::availableIn('checkout:'.$request->user()->id);

            return back()
                ->with('error', 'Too many orders attempted. Please try again in '.max(1, (int) ceil($seconds / 60)).' minute(s).')
                ->withInput();
        }
        RateLimiter::hit('checkout:'.$request->user()->id, 600);

        // Garbage collection of abandoned checkouts — rules extracted so
        // they are directly testable.
        self::sweepAbandonedRazorpayOrders();

        try {
            // Process checkout and place order in database (status: pending)
            $order = $this->checkoutService->placeOrder($data);

            // Handle Razorpay Payment
            if ($data['payment_method'] === 'razorpay') {
                try {
                    $razorpayOrderId = $this->checkoutService->createRazorpayOrder($order);
                    $order->update(['razorpay_order_id' => $razorpayOrderId]);

                    return view('front.razorpay-payment', compact('order', 'razorpayOrderId'));
                } catch (\Exception $e) {
                    // If Razorpay API fails, restore stock (variant + parent) and delete order
                    foreach ($order->items as $item) {
                        if ($item->product_variant_id) {
                            ProductVariant::where('id', $item->product_variant_id)
                                ->increment('stock', $item->qty);
                        }
                        if ($item->product) {
                            $item->product->increment('stock', $item->qty);
                        }
                    }
                    $order->delete();

                    return back()->with('error', 'Payment gateway initialization failed: '.$e->getMessage())->withInput();
                }
            }

            // For COD: Send Email Notifications (Customer and Admin)
            $this->checkoutService->sendOrderPlacedEmails($order);

            return redirect()->route('order.success', $order->id)
                ->with('success', 'Order placed successfully!');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Verify online payment.
     *
     * @return RedirectResponse
     */
    public function verifyPayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        // Ownership: you can only verify payment for your own order
        $order = Order::where('id', $request->input('order_id'))
            ->where('user_id', Auth::id())
            ->first();

        if (! $order) {
            abort(404);
        }

        // Binding: the Razorpay order id must be the one issued for this order
        if ($order->razorpay_order_id && $order->razorpay_order_id !== $request->input('razorpay_order_id')) {
            Log::warning("Razorpay order binding mismatch for Order ID {$order->id}.");

            return redirect()->route('checkout', ['payment' => 'failed', 'order_id' => $order->id])
                ->with('error', 'Payment verification failed: order mismatch.');
        }

        // Prevent double processing (idempotent across concurrent requests)
        if ($order->payment_status === 'paid'
            || DB::table('orders')->where('id', $order->id)->where('payment_status', 'paid')->exists()) {
            return redirect()->route('order.success', $order->id);
        }

        $success = $this->checkoutService->processOnlinePayment($order, $request->all());

        if ($success) {
            // Send Email Notifications
            $this->checkoutService->sendOrderPlacedEmails($order);

            return redirect()->route('order.success', $order->id)
                ->with('success', 'Payment successful and order placed!');
        }

        return redirect()->route('checkout', ['payment' => 'failed', 'order_id' => $order->id])
            ->with('error', 'Payment signature verification failed.');
    }

    /**
     * Order success page. Owners only — customer PII lives here.
     *
     * @param  int  $id
     * @return View|RedirectResponse
     */
    public function success($id)
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to view your order.');
        }

        $order = Order::with('items')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('front.order-success', compact('order'));
    }

    /**
     * Razorpay webhook: the reliable source of truth for online payments.
     * CSRF-exempt (bootstrap/app.php); authenticity comes from the webhook signature.
     *
     * @return Response
     */
    public function webhook(Request $request)
    {
        $webhookSecret = config('services.razorpay.webhook_secret');
        $body = $request->getContent();

        if (empty($webhookSecret)) {
            Log::error('Razorpay webhook secret is not configured.');

            return response('Webhook not configured', 500);
        }

        $signature = $request->header('X-Razorpay-Signature');
        $expected = hash_hmac('sha256', $body, $webhookSecret);

        if (! is_string($signature) || ! hash_equals($expected, $signature)) {
            Log::warning('Razorpay webhook signature verification failed.');

            return response('Invalid signature', 400);
        }

        $event = json_decode($body, true);

        if (($event['event'] ?? null) === 'payment.captured') {
            $entity = $event['payload']['payment']['entity'] ?? [];
            $rzpOrderId = $entity['order_id'] ?? null;
            $rzpPaymentId = $entity['id'] ?? null;
            $amount = (int) ($entity['amount'] ?? 0);

            if (! $rzpOrderId) {
                return response('Missing order id', 400);
            }

            $order = Order::where('razorpay_order_id', $rzpOrderId)->first();

            if (! $order) {
                Log::warning("Razorpay webhook: no local order for razorpay_order_id {$rzpOrderId}.");

                return response('OK', 200);
            }

            if ($order->payment_status === 'paid') {
                return response('OK', 200);
            }

            // The captured amount must match what the order says we are owed
            if ($amount !== (int) round(((float) $order->total) * 100)) {
                Log::error("Razorpay webhook amount mismatch for Order ID {$order->id}: captured={$amount}, expected=".(int) round(((float) $order->total) * 100));

                return response('Amount mismatch', 400);
            }

            $order->update([
                'payment_status' => 'paid',
                'order_status' => 'processing',
                'razorpay_payment_id' => $rzpPaymentId,
            ]);

            $this->checkoutService->sendOrderPlacedEmails($order);

            Log::info("Order ID {$order->id} marked paid via Razorpay webhook.");
        }

        return response('OK', 200);
    }
}

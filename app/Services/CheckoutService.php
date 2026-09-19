<?php

namespace App\Services;

use App\Mail\OrderPlacedMail;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckoutService
{
    protected $orderModel;

    public function __construct(Order $orderModel)
    {
        $this->orderModel = $orderModel;
    }

    /**
     * Process checkout and place an order.
     *
     * @throws \Exception
     */
    public function placeOrder(array $data): Order
    {
        $cartItems = json_decode($data['cart_data'], true);

        if (empty($cartItems)) {
            throw new \Exception('Your cart is empty!');
        }

        return DB::transaction(function () use ($data, $cartItems) {
            $subtotal = 0;
            $orderItemsToCreate = [];

            // Verify stock and calculate prices
            foreach ($cartItems as $item) {
                // cart_data is client JSON: shape and quantity must be enforced
                // server-side (a negative qty would add stock and credit money)
                if (! is_array($item) || ! isset($item['id']) || ! is_numeric($item['qty'])) {
                    throw new \Exception('Invalid cart data. Please refresh the page and try again.');
                }

                $item['qty'] = (int) $item['qty'];

                if ($item['qty'] < 1 || $item['qty'] > 999) {
                    throw new \Exception('Invalid quantity for "'.$item['name'].'".');
                }

                $product = Product::find($item['id']);
                if (! $product) {
                    throw new \Exception("Product '{$item['name']}' not found.");
                }

                $variant = null;
                if (! empty($item['variantId'])) {
                    $variant = ProductVariant::where('product_id', $product->id)
                        ->where('id', $item['variantId'])
                        ->first();
                }

                if ($variant) {
                    // Atomic decrement — row is only touched when stock is sufficient,
                    // so two concurrent buyers can never both pass this gate
                    $claimed = ProductVariant::where('id', $variant->id)
                        ->where('stock', '>=', $item['qty'])
                        ->decrement('stock', $item['qty']);

                    if (! $claimed) {
                        $varValues = collect([$variant->value_1, $variant->value_2])->filter()->implode(', ');

                        throw new \Exception("Product '{$product->name}'{$varValues} is out of stock or does not have enough stock available.");
                    }

                    $price = $variant->sale_price ?? $variant->price;
                } else {
                    $claimed = Product::where('id', $product->id)
                        ->where('stock', '>=', $item['qty'])
                        ->decrement('stock', $item['qty']);

                    if (! $claimed) {
                        throw new \Exception("Product '{$product->name}' is out of stock or does not have enough stock available.");
                    }

                    $price = $product->sale_price ?? $product->price;
                }

                $itemTotal = $price * $item['qty'];
                $subtotal += $itemTotal;

                // The stored name is derived from OUR database — never from the
                // client payload (the client string is untrusted display data).
                $displayName = $product->name;
                if ($variant) {
                    $variantLabel = collect([$variant->value_1, $variant->value_2])->filter()->implode(', ');
                    if ($variantLabel !== '') {
                        $displayName .= ' ('.$variantLabel.')';
                    }
                }

                $orderItemsToCreate[] = [
                    'product_id' => $product->id,
                    'product_variant_id' => $variant?->id,
                    'product_name' => $displayName,
                    'price' => $price,
                    'qty' => $item['qty'],
                    'total' => $itemTotal,
                ];

                // Deduct inventory stock
                if ($variant) {
                    // Variant stock already claimed above; mirror it onto the parent
                    // (order_items carry product_id, so parent must reflect what was
                    // sold). Failing to mirror rolls the whole transaction back so
                    // variant and parent stock can never diverge.
                    $mirrored = Product::where('id', $product->id)
                        ->where('stock', '>=', $item['qty'])
                        ->decrement('stock', $item['qty']);

                    if (! $mirrored) {
                        throw new \Exception("Stock records for '{$product->name}' are out of sync. Please contact support.");
                    }
                }
            }

            // Calculate charges dynamically from Admin Settings
            $setting = Setting::first();
            $taxPercent = $setting && isset($setting->tax_percent) ? (float) $setting->tax_percent : 5.0;
            $deliveryFee = $setting && isset($setting->delivery_fee) ? (float) $setting->delivery_fee : 60.0;
            $minOrderFree = $setting && isset($setting->min_order_for_free_delivery) ? (float) $setting->min_order_for_free_delivery : 1000.0;
            $isCodEnabled = $setting ? (bool) $setting->is_cod_enabled : true;

            if ($data['payment_method'] === 'cod' && ! $isCodEnabled) {
                throw new \Exception('Cash on Delivery (COD) is currently disabled. Please select online payment.');
            }

            $shippingCharge = ($minOrderFree > 0 && $subtotal >= $minOrderFree) ? 0 : $deliveryFee;
            $tax = $subtotal * ($taxPercent / 100.0);
            $total = $subtotal + $shippingCharge + $tax;

            $sameAddress = isset($data['same_address']) && ($data['same_address'] == '1' || $data['same_address'] == true);

            // Generate unique order number: ORD-YYYYMMDD-XXXX. The exists()
            // pre-check narrows collisions; the create below still retries on
            // the unique index in the unlikely race two checkouts pass together.
            $dateStr = date('Ymd');
            $newOrderNumber = fn (): string => "ORD-{$dateStr}-".strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));
            $orderNumber = $newOrderNumber();
            while ($this->orderModel::where('order_number', $orderNumber)->exists()) {
                $orderNumber = $newOrderNumber();
            }

            // Create Order — retry once on a unique-index collision
            $order = null;
            for ($attempt = 0; $attempt < 3 && $order === null; $attempt++) {
                try {
                    $order = $this->orderModel::create([
                        'order_number' => $orderNumber,
                        'user_id' => Auth::id(),
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'phone' => $data['phone'],
                        'address' => $data['address'],
                        'city' => $data['city'],
                        'state' => $data['state'],
                        'pincode' => $data['pincode'],
                        'shipping_name' => $sameAddress ? $data['name'] : ($data['shipping_name'] ?? $data['name']),
                        'shipping_email' => $sameAddress ? $data['email'] : ($data['shipping_email'] ?? $data['email']),
                        'shipping_phone' => $sameAddress ? $data['phone'] : ($data['shipping_phone'] ?? $data['phone']),
                        'shipping_address' => $sameAddress ? $data['address'] : ($data['shipping_address'] ?? $data['address']),
                        'shipping_city' => $sameAddress ? $data['city'] : ($data['shipping_city'] ?? $data['city']),
                        'shipping_state' => $sameAddress ? $data['state'] : ($data['shipping_state'] ?? $data['state']),
                        'shipping_pincode' => $sameAddress ? $data['pincode'] : ($data['shipping_pincode'] ?? $data['pincode']),
                        'subtotal' => $subtotal,
                        'shipping_charge' => $shippingCharge,
                        'tax' => $tax,
                        'total' => $total,
                        'payment_method' => $data['payment_method'],
                        'payment_status' => 'pending',
                        'order_status' => 'pending',
                    ]);
                } catch (UniqueConstraintViolationException $e) {
                    // Two checkouts drew the same number simultaneously: re-draw
                    // and retry (stock claims above stay inside the transaction,
                    // so nothing is lost by retrying)
                    if ($attempt === 2) {
                        throw $e;
                    }
                    $orderNumber = $newOrderNumber();
                }
            }

            if ($order === null) {
                throw new \RuntimeException('Could not allocate a unique order number.');
            }

            // Save order items
            foreach ($orderItemsToCreate as $itemData) {
                $order->items()->create($itemData);
            }

            return $order;
        });
    }

    /**
     * Create order in Razorpay using cURL.
     *
     * @return string|null razorpay_order_id
     */
    public function createRazorpayOrder(Order $order): ?string
    {
        $keyId = config('services.razorpay.key');
        $keySecret = config('services.razorpay.secret');

        if (empty($keyId) || empty($keySecret)) {
            Log::error('Razorpay API keys are not configured in services.php.');
            throw new \Exception('Razorpay integration configuration is missing.');
        }

        // Amount in paise
        $amount = (int) round($order->total * 100);

        $postData = [
            'amount' => $amount,
            'currency' => 'INR',
            'receipt' => $order->order_number,
        ];

        Log::info('Initiating Razorpay Order API call', ['order_id' => $order->id, 'amount' => $amount]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.razorpay.com/v1/orders');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_USERPWD, $keyId.':'.$keySecret);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            Log::error('Razorpay API curl error: '.$curlError);
            throw new \Exception('Failed to communicate with the payment gateway.');
        }

        $resData = json_decode($response, true);

        if ($httpCode !== 200 || isset($resData['error'])) {
            Log::error('Razorpay API returned error: '.($resData['error']['description'] ?? $response));
            throw new \Exception($resData['error']['description'] ?? 'Payment gateway returned an error.');
        }

        return $resData['id'] ?? null;
    }

    /**
     * Verify Razorpay payment signature and update order status.
     */
    public function processOnlinePayment(Order $order, array $data): bool
    {
        Log::info("Razorpay Payment requested for Order ID: {$order->id}");

        $keySecret = config('services.razorpay.secret');
        if (empty($keySecret)) {
            Log::error('Razorpay secret key is not configured.');

            return false;
        }

        $razorpayOrderId = $data['razorpay_order_id'] ?? $order->razorpay_order_id;
        $razorpayPaymentId = $data['razorpay_payment_id'] ?? null;
        $razorpaySignature = $data['razorpay_signature'] ?? null;

        if (! $razorpayOrderId || ! $razorpayPaymentId || ! $razorpaySignature) {
            Log::error('Missing Razorpay verification parameters.');

            return false;
        }

        $expectedSignature = hash_hmac('sha256', $razorpayOrderId.'|'.$razorpayPaymentId, $keySecret);

        // Signature FIRST — nothing is written until the payment is proven authentic
        if (! hash_equals($expectedSignature, $razorpaySignature)) {
            Log::error("Signature verification failed for Order ID {$order->id}.");

            Order::where('id', $order->id)
                ->where('payment_status', '!=', 'paid')
                ->update(['payment_status' => 'failed']);

            return false;
        }

        // Idempotent + race-safe: only one writer can flip pending -> paid
        $updated = Order::where('id', $order->id)
            ->where('payment_status', '!=', 'paid')
            ->update([
                'payment_status' => 'paid',
                'order_status' => 'processing',
                'razorpay_payment_id' => $razorpayPaymentId,
                'razorpay_signature' => $razorpaySignature,
            ]);

        if ($updated) {
            Log::info("Order ID {$order->id} payment verified successfully.");
        }

        return true;
    }

    /**
     * Send email notifications to Customer and Admin.
     */
    public function sendOrderPlacedEmails(Order $order): void
    {
        // Send email to Customer (queued — mail must never sit inside the checkout request)
        try {
            Mail::to($order->email)->send(new OrderPlacedMail($order, false));
        } catch (\Exception $e) {
            Log::error('Failed to send order confirmation email to customer: '.$e->getMessage());
        }

        // Send email to Admin
        try {
            $settings = Setting::first();
            $adminEmail = $settings->email ?? config('mail.from.address') ?? 'admin@admin.com';
            Mail::to($adminEmail)->send(new OrderPlacedMail($order, true));
        } catch (\Exception $e) {
            Log::error('Failed to send order notification email to admin: '.$e->getMessage());
        }
    }
}

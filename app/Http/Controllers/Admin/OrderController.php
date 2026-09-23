<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\OrderDataTable;
use App\Http\Controllers\Controller;
use App\Mail\OrderStatusMail;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of the orders.
     */
    public function index(OrderDataTable $dataTable)
    {
        return $dataTable->render('admin.orders.index');
    }

    /**
     * Display the specified order details.
     */
    public function show($id)
    {
        $order = Order::with('items.product')->findOrFail($id);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Update order and payment status.
     *
     * Cancellation is final: stock is restored exactly once, inside a
     * transaction, and a cancelled order can never be reopened — reopening
     * would double-count revenue and re-claim stock that may have sold since.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'order_status' => 'required|in:pending,processing,shipped,completed,cancelled,returned,refunded',
            'payment_status' => 'required|in:pending,paid,failed',
        ]);

        $order = Order::findOrFail($id);

        // A cancelled order is terminal — refuse reopening with a clear message
        if ($order->order_status === 'cancelled' && $request->input('order_status') !== 'cancelled') {
            return redirect()->back()
                ->with('error', 'Cancelled orders are final and cannot be reopened. Please create a new order for the customer.');
        }

        $restoringStock = $request->input('order_status') === 'cancelled' && $order->order_status !== 'cancelled';
        $statusChanged = $order->order_status !== $request->input('order_status');

        DB::transaction(function () use ($order, $request, $restoringStock, $statusChanged) {
            // Restore inventory exactly once, atomically
            if ($restoringStock) {
                foreach ($order->items as $item) {
                    if ($item->product_variant_id) {
                        ProductVariant::where('id', $item->product_variant_id)
                            ->increment('stock', $item->qty);
                    }
                    if ($item->product) {
                        Product::where('id', $item->product->id)
                            ->increment('stock', $item->qty);
                    }
                }

                // A cancelled order can never remain (or become) "paid"
                if ($request->input('payment_status') === 'paid') {
                    $request->merge(['payment_status' => 'failed']);
                }
            }

            $order->update([
                'order_status' => $request->input('order_status'),
                'payment_status' => $request->input('payment_status'),
                // The delivery anchor moves only when the status itself
                // changes: stamped on first completion, cleared when leaving
                // it. A resubmit (e.g. fixing payment on a delivered or
                // returned order) must never shift the return window.
                'completed_at' => $statusChanged
                    ? ($request->input('order_status') === 'completed' ? now() : null)
                    : $order->completed_at,
            ]);
        });

        // Customer notification for the lifecycle states they care about.
        // The "ordered" email is already covered at checkout (OrderPlacedMail).
        if ($statusChanged) {
            $stateMail = [
                'shipped'   => 'shipped',
                'completed' => 'delivered',
                'cancelled' => 'cancelled',
            ][$request->input('order_status')] ?? null;

            if ($stateMail !== null) {
                OrderStatusMail::sendTo($order, $stateMail);
            }
        }

        return redirect()->back()->with('success', 'Order status updated successfully.');
    }
}

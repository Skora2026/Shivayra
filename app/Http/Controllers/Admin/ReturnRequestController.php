<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\OrderStatusMail;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ReturnRequest;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturnRequestController extends Controller
{
    /**
     * Return requests queue + policy settings.
     */
    public function index(Request $request)
    {
        $query = ReturnRequest::with(['order:id,order_number', 'user:id,name', 'orderItem:id,product_name,price']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $returns = $query->latest('requested_at')->paginate(15)->withQueryString();

        $stats = [
            'pending' => ReturnRequest::where('status', 'pending')->count(),
            'approved' => ReturnRequest::where('status', 'approved')->count(),
            'refunded' => ReturnRequest::where('status', 'refunded')->count(),
            'rejected' => ReturnRequest::where('status', 'rejected')->count(),
        ];

        $setting = Setting::first();
        $returnableCount = Product::where('status', 'active')->where('is_returnable', true)->count();
        $nonReturnableCount = Product::where('status', 'active')->where('is_returnable', false)->count();

        return view('admin.returns.index', compact(
            'returns', 'stats', 'setting', 'returnableCount', 'nonReturnableCount'
        ));
    }

    /**
     * Inspect a single return request.
     */
    public function show(ReturnRequest $returnRequest)
    {
        $returnRequest->load(['order.items.product', 'user', 'orderItem.product']);

        return view('admin.returns.show', compact('returnRequest'));
    }

    /**
     * Approve / reject / mark refunded. Approval restores the stock;
     * refund closes the loop with the money side.
     */
    public function updateStatus(Request $request, ReturnRequest $returnRequest)
    {
        $data = $request->validate([
            'status' => 'required|in:approved,rejected,refunded',
            'admin_note' => 'nullable|string|max:1000',
        ]);

        if ($returnRequest->status === 'refunded') {
            return redirect()->back()->with('error', 'Refunded requests are final and cannot change state.');
        }

        $previousStatus = $returnRequest->status;

        DB::transaction(function () use ($data, $returnRequest, $previousStatus) {
            $wasPending = $returnRequest->status === 'pending';

            $returnRequest->update([
                'status' => $data['status'],
                'admin_note' => $data['admin_note'] ?? $returnRequest->admin_note,
                'resolved_at' => in_array($data['status'], ['approved', 'rejected']) ? now() : $returnRequest->resolved_at,
            ]);

            // Approving puts the item back on the shelf exactly once
            if ($data['status'] === 'approved' && $wasPending) {
                if ($returnRequest->orderItem->product_variant_id) {
                    ProductVariant::where('id', $returnRequest->orderItem->product_variant_id)
                        ->increment('stock', $returnRequest->orderItem->qty);
                }
                if ($returnRequest->orderItem->product_id) {
                    Product::where('id', $returnRequest->orderItem->product_id)
                        ->increment('stock', $returnRequest->orderItem->qty);
                }
            }

            // Mirror the return lifecycle onto the order itself — returned on
            // approval, refunded on refund. Cancelled is terminal and stays;
            // a rejection after approval rolls "returned" back to delivered.
            if ($previousStatus !== $data['status'] && ($order = $returnRequest->order)) {
                if ($data['status'] === 'refunded') {
                    $order->payment_status = 'refunded';
                    if ($order->order_status !== 'cancelled') {
                        $order->order_status = 'refunded';
                    }
                    $order->save();
                } elseif ($order->order_status !== 'cancelled') {
                    if ($data['status'] === 'approved') {
                        $order->update(['order_status' => 'returned']);
                    } elseif ($data['status'] === 'rejected' && $order->order_status === 'returned') {
                        $order->update(['order_status' => 'completed']);
                    }
                }
            }
        });

        // Customer notification for the two order states this flow creates.
        if ($previousStatus !== $data['status'] && ($order = $returnRequest->order)) {
            if ($data['status'] === 'refunded') {
                OrderStatusMail::sendTo($order, 'refunded');
            } elseif ($data['status'] === 'approved' && $order->order_status !== 'cancelled') {
                OrderStatusMail::sendTo($order, 'returned');
            }
        }

        return redirect()->back()->with('success', 'Return marked as '.$data['status'].'.');
    }
}

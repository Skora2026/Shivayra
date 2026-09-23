<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\ProductReview;
use App\Models\ReturnRequest;
use App\Models\Setting;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

/**
 * Shared verified-purchase gating used by both the review and return flows.
 *
 * A customer may act on an order item only when:
 *  - the order belongs to them,
 *  - the order is completed (i.e. received),
 *  - and, for returns, the item is inside the admin-controlled return window.
 */
abstract class ReviewReturnController extends Controller
{
    use AuthorizesRequests;

    protected function verifiedItem(int $orderItemId, $userId): ?OrderItem
    {
        return OrderItem::where('id', $orderItemId)
            ->whereHas('order', function ($q) use ($userId) {
                $q->where('user_id', $userId)
                    ->where('order_status', 'completed');
            })
            ->with(['order', 'product'])
            ->first();
    }

    protected function withinReturnWindow(OrderItem $item): bool
    {
        $window = (int) (Setting::first()?->return_window_days ?? 7);

        // Anchor strictly on the completion stamp — never on updated_at,
        // which any later admin edit would refresh and silently extend
        // the window. Orders completed before the stamp existed fall back
        // to created_at (still safer than updated_at).
        $completedAt = $item->order->completed_at
            ?? $item->order->created_at;

        return $completedAt->copy()->addDays($window)->isFuture();
    }
}

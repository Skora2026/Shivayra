<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductReview;
use App\Models\ReturnRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AccountController extends ReviewReturnController
{
    /**
     * Display the My Account dashboard (Orders & Profile).
     */
    public function index()
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to access your account.');
        }

        $user = Auth::user();
        // A customer with hundreds of orders must not render them all at once
        $orders = Order::where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('front.account', compact('user', 'orders'));
    }

    /**
     * Display order details & shipment tracking timeline.
     *
     * @param  string  $order_number
     */
    public function orderDetails($order_number)
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to view order details.');
        }

        $order = Order::with('items.product')
            ->where('order_number', $order_number)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('front.order-detail', compact('order'));
    }

    /**
     * Render the order invoice on its own chrome-free page, so it can be
     * printed or saved as a PDF without appearing inside the dashboard.
     */
    public function invoice($order_number)
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to view your invoice.');
        }

        // Scoped to the signed-in customer: one customer can never open
        // another's invoice by guessing an order number.
        $order = Order::with('items.product')
            ->where('order_number', $order_number)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('front.invoice', compact('order'));
    }

    /**
     * Update user profile settings.
     */
    public function updateProfile(Request $request)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:15',
            'current_password' => 'nullable|required_with:password|string',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->name = $request->name;
        $user->first_name = $request->name;

        if ($request->filled('phone')) {
            $user->phone = $request->phone;
        }

        if ($request->filled('password')) {
            // Changing the password requires proving you own the account
            if (! Hash::check($request->input('current_password', ''), $user->password)) {
                return back()->withErrors(['current_password' => 'Your current password is incorrect.'])->withInput();
            }

            $user->password = $request->password; // hashed via model cast
        }

        $user->save();

        return back()->with('success', 'Profile updated successfully.');
    }

    /* ================================================================
       REVIEWS — verified purchases only
       ================================================================ */

    /**
     * Review form for a delivered order item.
     */
    public function reviewForm($orderItemId)
    {
        $item = $this->verifiedItem((int) $orderItemId, Auth::id());

        if (! $item) {
            return redirect()->route('my-account')
                ->with('error', 'You can only review items from orders that have been completed.');
        }

        $existing = ProductReview::where('order_item_id', $item->id)->first();

        if ($existing) {
            return redirect()->route('my-account')->with('error', 'You have already reviewed this item.');
        }

        return view('front.review-form', [
            'item' => $item->load('product'),
            'existing' => $existing,
        ]);
    }

    /**
     * Store a review for a delivered order item.
     */
    public function storeReview(Request $request, $orderItemId)
    {
        $item = $this->verifiedItem((int) $orderItemId, Auth::id());

        if (! $item) {
            return redirect()->route('my-account')
                ->with('error', 'You can only review items from orders that have been completed.');
        }

        if (ProductReview::where('order_item_id', $item->id)->exists()) {
            return redirect()->route('my-account')->with('error', 'You have already reviewed this item.');
        }

        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:120',
            'body' => 'required|string|min:10|max:2000',
        ]);

        ProductReview::create([
            'product_id' => $item->product_id,
            'user_id' => Auth::id(),
            'order_id' => $item->order_id,
            'order_item_id' => $item->id,
            'rating' => (int) $data['rating'],
            'title' => $data['title'] ?? null,
            'body' => $data['body'],
            // Moderation: reviews go live only after admin approval
            // (Admin\ReviewController::updateApproval).
            'is_approved' => false,
        ]);

        return redirect()->route('my-account')
            ->with('success', 'Thank you! Your review was submitted and will appear once approved.');
    }

    /* ================================================================
       RETURNS — verified purchases within the admin-set window
       ================================================================ */

    /**
     * Return-request form for a delivered order item.
     */
    public function returnForm($orderItemId)
    {
        $item = $this->verifiedItem((int) $orderItemId, Auth::id());

        if (! $item) {
            return redirect()->route('my-account')
                ->with('error', 'You can only return items from orders that have been completed.');
        }

        if ($item->returnRequest) {
            return redirect()->route('my-account')
                ->with('error', 'A return request already exists for this item ('.$item->returnRequest->request_number.').');
        }

        if (! ($item->product?->is_returnable ?? true)) {
            return redirect()->route('my-account')
                ->with('error', 'This product is marked as non-returnable.');
        }

        if (! $this->withinReturnWindow($item)) {
            return redirect()->route('my-account')
                ->with('error', 'The return window for this order has closed.');
        }

        return view('front.return-form', ['item' => $item->load('product')]);
    }

    /**
     * File a return request for a delivered order item.
     */
    public function storeReturn(Request $request, $orderItemId)
    {
        $item = $this->verifiedItem((int) $orderItemId, Auth::id());

        if (! $item) {
            return redirect()->route('my-account')
                ->with('error', 'You can only return items from orders that have been completed.');
        }

        if ($item->returnRequest) {
            return redirect()->route('my-account')
                ->with('error', 'A return request already exists for this item.');
        }

        if (! ($item->product?->is_returnable ?? true)) {
            return redirect()->route('my-account')
                ->with('error', 'This product is marked as non-returnable.');
        }

        if (! $this->withinReturnWindow($item)) {
            return redirect()->route('my-account')
                ->with('error', 'The return window for this order has closed.');
        }

        $data = $request->validate([
            'reason' => 'required|string|min:10|max:1000',
        ]);

        DB::transaction(function () use ($data, $item) {
            ReturnRequest::create([
                'request_number' => ReturnRequest::makeNumber(),
                'order_id' => $item->order_id,
                'user_id' => Auth::id(),
                'order_item_id' => $item->id,
                'status' => 'pending',
                'reason' => $data['reason'],
                'requested_at' => now(),
            ]);
        });

        return redirect()->route('my-account')
            ->with('success', 'Return request filed. We will review it and get back to you.');
    }
}

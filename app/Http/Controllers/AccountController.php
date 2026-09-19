<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
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
}

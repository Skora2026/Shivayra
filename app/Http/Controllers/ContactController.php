<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessageMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Store a contact enquiry and notify the store owner.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'order' => 'nullable|string|max:50',
            'inquiry' => 'required|in:orders,returns,product,wholesale,other',
            'message' => 'required|string|max:2000',
        ]);

        $subjectMap = [
            'orders' => 'Order & Shipping',
            'returns' => 'Returns & Refunds',
            'product' => 'Product information',
            'wholesale' => 'Wholesale / Partnership',
            'other' => 'General',
        ];

        $validated['inquiry_label'] = $subjectMap[$validated['inquiry']];

        try {
            Mail::to(config('mail.from.address'))->send(new ContactMessageMail($validated));
        } catch (\Throwable $e) {
            // Never block the customer on mail transport issues — log for follow-up.
            Log::error('Contact mail failed: '.$e->getMessage(), ['payload' => $validated]);
        }

        return back()
            ->with('contact_success', "Thanks {$validated['name']}! Your message has been sent — we'll get back to you shortly.");
    }
}

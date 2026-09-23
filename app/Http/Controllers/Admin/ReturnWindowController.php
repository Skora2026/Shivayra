<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class ReturnWindowController extends Controller
{
    /**
     * Update the return-window (days after delivery) policy.
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'return_window_days' => 'required|integer|min:0|max:90',
        ]);

        $setting = Setting::first();
        if (! $setting) {
            return redirect()->back()->with('error', 'Store settings row missing.');
        }

        $setting->update($data);

        return redirect()->back()->with('success', 'Return window set to '.$data['return_window_days'].' days.');
    }
}

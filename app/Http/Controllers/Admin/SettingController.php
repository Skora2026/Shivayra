<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Setting\UpdateRequest;
use App\Models\Setting;
use App\Services\SettingService;

class SettingController extends Controller
{
    protected $settingService;

    /**
     * Create a new Controller instance.
     */
    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    /**
     * There is exactly one settings row for the store, so the index IS the
     * settings form — no listing, no create/delete ceremony. The row is
     * created on first visit so the form always has an ID to post to.
     */
    public function index()
    {
        $setting = Setting::first() ?? Setting::create([]);

        return view('admin.settings.edit', compact('setting'));
    }

    /**
     * Alias so admin.settings.edit (and any stale bookmarks to it) land on the
     * same single settings form.
     */
    public function edit($id)
    {
        return $this->index();
    }

    /**
     * Update the settings row.
     */
    public function update(UpdateRequest $request, $id)
    {
        $setting = $this->settingService->getDataById($id);

        $data = $this->settingService->getDataFromRequest($request);

        $data['logo'] = $this->settingService->handleImageUpload(
            $request,
            'logo',
            $setting->logo
        );

        $data['favicon'] = $this->settingService->handleImageUpload(
            $request,
            'favicon',
            $setting->favicon
        );

        $this->settingService->updateData($id, $data);

        $message = 'Setting updated successfully.';

        // Save-then-look: the row is the source of truth, so the warning
        // reflects the persisted state regardless of how the request omitted
        // or set the checkbox.
        if (! $setting->fresh()->is_cod_enabled) {
            $message .= ' Note: Cash on Delivery is currently DISABLED — customers can only pay online.';
        }

        return redirect()->route('admin.settings.index')
            ->with('success', $message);
    }
}

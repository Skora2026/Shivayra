<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\SettingDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Setting\CreateRequest;
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
     * Display a listing of the resource.
     */
    public function index(SettingDataTable $dataTable)
    {
        $hasSettings = $this->settingService->exists([]);

        return $dataTable->render('admin.settings.index', compact('hasSettings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.settings.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateRequest $request)
    {
        $settingData = $this->settingService->getDataFromRequest($request);
        $settingData['logo'] = $this->settingService->handleImageUpload($request, 'logo');
        $settingData['favicon'] = $this->settingService->handleImageUpload($request, 'favicon');

        $this->settingService->addData($settingData);

        return redirect()->route('admin.settings.index')->with('success', 'Settings created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Setting $setting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Setting $setting)
    {
        $setting = Setting::first();

        return view('admin.settings.edit', compact('setting'));

    }

    /**
     * Update the specified resource in storage.
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

        return redirect()->route('admin.settings.index')
            ->with('success', 'Setting updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Setting $setting)
    {
        foreach (['logo', 'favicon'] as $file) {
            if ($setting->{$file} && \Storage::disk('public')->exists($setting->{$file})) {
                \Storage::disk('public')->delete($setting->{$file});
            }
        }

        $this->settingService->deleteData($setting->id);

        return redirect()->route('admin.settings.index')
            ->with('success', 'Setting deleted successfully.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\BannerDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Banners\CreateRequest;
use App\Http\Requests\Banners\UpdateRequest;
use App\Models\Banner;
use App\Services\BannerService;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    protected $bannerService;

    public function __construct(BannerService $bannerService)
    {
        $this->bannerService = $bannerService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(BannerDataTable $dataTable)
    {
        return $dataTable->render(
            'admin.banner.index'
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.banner.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateRequest $request)
    {
        $data = $this->bannerService->getDataFromRequest($request);
        $data['image'] = $this->bannerService->handleImageUpload($request, null, 'image');
        $data['mobile_image'] = $this->bannerService->handleImageUpload($request, null, 'mobile_image');

        $this->bannerService->addData($data);

        return redirect()->route('admin.banners.index')
            ->with('success', 'Banner created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Banner $banner)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $banner = $this->bannerService->getDataById($id);

        return view(
            'admin.banner.edit',
            compact('banner')
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateRequest $request,
        string $id
    ) {
        $banner = $this->bannerService
            ->getDataById($id);

        $data = $this->bannerService
            ->getDataFromRequest($request);

        $data['image'] = $this->bannerService
            ->handleImageUpload(
                $request,
                $banner->image,
                'image'
            );

        $data['mobile_image'] = $this->bannerService
            ->handleImageUpload(
                $request,
                $banner->mobile_image,
                'mobile_image'
            );

        $this->bannerService
            ->updateData($id, $data);

        return redirect()
            ->route('admin.banners.index')
            ->with('success', 'Banner Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $banner = $this->bannerService
            ->getDataById($id);

        if ($banner) {
            if (
                $banner->image &&
                Storage::disk('public')->exists($banner->image)
            ) {
                Storage::disk('public')->delete($banner->image);
            }
            if (
                $banner->mobile_image &&
                Storage::disk('public')->exists($banner->mobile_image)
            ) {
                Storage::disk('public')->delete($banner->mobile_image);
            }

            $this->bannerService->deleteData($id);
        }

        return redirect()->route('admin.banners.index')
            ->with('success', 'Banner deleted successfully.');
    }
}

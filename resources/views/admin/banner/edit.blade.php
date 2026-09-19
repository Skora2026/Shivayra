@extends('admin.layouts.app')

@section('styles')
<style>
    .form-card {
        background: #fff;
        border-radius: 1.5rem;
        box-shadow: 0 4px 24px rgba(0,0,0,0.06);
        overflow: hidden;
    }
    .form-card-header {
        background: linear-gradient(135deg, #004727 0%, #0A9051 100%);
        padding: 1.75rem 2rem;
        color: #fff;
    }
    .form-card-body { padding: 2rem; }
    .form-label { font-weight: 600; font-size: 0.9rem; color: #374151; margin-bottom: 0.4rem; }
    .form-control, .form-select {
        border: 1.5px solid #e5e7eb;
        border-radius: 0.75rem;
        padding: 0.6rem 1rem;
        font-size: 0.93rem;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .form-control:focus, .form-select:focus {
        border-color: #0A9051;
        box-shadow: 0 0 0 3px rgba(10,144,81,0.1);
    }
    .image-upload-zone {
        border: 2px dashed #d1d5db;
        border-radius: 1rem;
        padding: 2rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        position: relative;
        background: #fafafa;
    }
    .image-upload-zone:hover { border-color: #0A9051; background: #f0fdf4; }
    .image-upload-zone input[type="file"] {
        position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
    }
    .image-preview-wrap {
        width: 120px; height: 120px;
        border-radius: 1rem;
        overflow: hidden;
        border: 3px solid #e9ecef;
        margin: 0 auto 1rem;
        background: #f3f4f6;
        display: flex; align-items: center; justify-content: center;
    }
    .image-preview-wrap img { width: 100%; height: 100%; object-fit: cover; }
    .btn-save {
        background: linear-gradient(100deg, #004727 0%, #0A9051 100%);
        color: #fff;
        border: none;
        border-radius: 2rem;
        padding: 0.6rem 2rem;
        font-weight: 600;
        transition: all 0.2s;
        box-shadow: 0 4px 12px rgba(0,71,39,0.15);
    }
    .btn-save:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(0,71,39,0.25); color:#fff; }
    .current-image-badge {
        background: #f0fdf4;
        border: 1.5px solid #d1fae5;
        border-radius: 0.75rem;
        padding: 0.75rem 1rem;
        display: flex; align-items: center; gap: 1rem;
        margin-bottom: 1rem;
    }
    .current-image-badge img { width: 64px; height: 64px; object-fit: cover; border-radius: 0.5rem; }
</style>
@endsection

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('admin.banners.index') }}"
        class="btn btn-sm btn-outline-secondary rounded-pill px-3">
        <i class="fa-solid fa-arrow-left me-1"></i> Back
    </a>

    <h2 class="mb-0 fw-700" style="font-size:1.4rem;">
        Edit Banner
    </h2>
</div>

<div class="form-card">

    <div class="form-card-header">
        <h5 class="mb-0 fw-600">
            <i class="fa-solid fa-image me-2"></i>
            Edit Banner
        </h5>

        <p class="mb-0 opacity-75 mt-1" style="font-size:0.875rem;">
            Update banner details below.
        </p>
    </div>

    <div class="form-card-body">

        <form action="{{ route('admin.banners.update',$banner->id) }}"
            method="POST"
            enctype="multipart/form-data">

            @csrf
            @method('PUT')

            <div class="row g-4">

                {{-- Device Type Dropdown --}}
                <div class="col-md-12">
                    <label class="form-label">
                        Device / Display Type
                    </label>
                    <select name="device_type" id="deviceTypeSelect" class="form-select">
                        <option value="both" {{ old('device_type', $banner->device_type) == 'both' ? 'selected' : '' }}>Both (Desktop & Mobile Banners)</option>
                        <option value="desktop" {{ old('device_type', $banner->device_type) == 'desktop' ? 'selected' : '' }}>Desktop Only Banner</option>
                        <option value="mobile" {{ old('device_type', $banner->device_type) == 'mobile' ? 'selected' : '' }}>Mobile Only Banner</option>
                    </select>
                </div>

                {{-- Desktop Banner Image --}}
                <div class="col-md-6" id="desktopImageContainer">
                    <label class="form-label d-flex justify-content-between">
                        <span>Desktop Banner Image</span>
                        <small class="text-primary fw-normal">(Recommended: 1920x800px)</small>
                    </label>

                    @if($banner->image)
                    <div class="current-image-badge">
                        <img src="{{ asset('storage/'.$banner->image) }}">
                        <div>
                            <p class="mb-0 fw-600 text-success" style="font-size: 0.85rem;">
                                Current Desktop Banner
                            </p>
                            <p class="mb-0 text-muted" style="font-size: 0.75rem;">
                                Upload new to replace
                            </p>
                        </div>
                    </div>
                    @endif

                    <div class="image-upload-zone">
                        <input type="file"
                            name="image"
                            accept="image/*"
                            onchange="previewImage(this, 'preview-wrap-desktop', 'img-preview-desktop', 'upload-icon-desktop')">

                        <div id="upload-placeholder-desktop">
                            <div class="image-preview-wrap"
                                id="preview-wrap-desktop"
                                style="display:none;">
                                <img id="img-preview-desktop">
                            </div>
                            <i class="fa-solid fa-cloud-arrow-up fa-2x text-muted mb-2"
                                id="upload-icon-desktop"></i>
                            <p class="mb-1 fw-500 text-secondary" style="font-size: 0.85rem;">
                                Click/drag desktop banner here
                            </p>
                            <p class="text-muted" style="font-size:0.75rem;">
                                JPG, PNG, WEBP
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Mobile Banner Image --}}
                <div class="col-md-6" id="mobileImageContainer">
                    <label class="form-label d-flex justify-content-between">
                        <span>Mobile Banner Image</span>
                        <small class="text-primary fw-normal">(Recommended: 600x600px)</small>
                    </label>

                    @if($banner->mobile_image)
                    <div class="current-image-badge">
                        <img src="{{ asset('storage/'.$banner->mobile_image) }}">
                        <div>
                            <p class="mb-0 fw-600 text-success" style="font-size: 0.85rem;">
                                Current Mobile Banner
                            </p>
                            <p class="mb-0 text-muted" style="font-size: 0.75rem;">
                                Upload new to replace
                            </p>
                        </div>
                    </div>
                    @else
                    <div class="current-image-badge bg-light border-dashed">
                        <div class="py-2">
                            <p class="mb-0 text-muted" style="font-size: 0.8rem;">
                                <i class="fa-solid fa-circle-info text-primary me-1"></i> No mobile banner uploaded yet.
                            </p>
                        </div>
                    </div>
                    @endif

                    <div class="image-upload-zone">
                        <input type="file"
                            name="mobile_image"
                            accept="image/*"
                            onchange="previewImage(this, 'preview-wrap-mobile', 'img-preview-mobile', 'upload-icon-mobile')">

                        <div id="upload-placeholder-mobile">
                            <div class="image-preview-wrap"
                                id="preview-wrap-mobile"
                                style="display:none;">
                                <img id="img-preview-mobile">
                            </div>
                            <i class="fa-solid fa-cloud-arrow-up fa-2x text-muted mb-2"
                                id="upload-icon-mobile"></i>
                            <p class="mb-1 fw-500 text-secondary" style="font-size: 0.85rem;">
                                Click/drag mobile banner here
                            </p>
                            <p class="text-muted" style="font-size:0.75rem;">
                                JPG, PNG, WEBP
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Badge --}}
                <div class="col-md-6">
                    <label class="form-label">
                        Badge Text
                    </label>

                    <input type="text"
                        name="badge"
                        class="form-control"
                        value="{{ old('badge',$banner->badge) }}">
                </div>

                {{-- Title --}}
                <div class="col-md-6">
                    <label class="form-label">
                        Banner Title
                    </label>

                    <input type="text"
                        name="title"
                        class="form-control"
                        value="{{ old('title',$banner->title) }}">
                </div>

                {{-- Description --}}
                <div class="col-12">
                    <label class="form-label">
                        Description
                    </label>

                    <textarea
                        name="description"
                        rows="4"
                        class="form-control">{{ old('description',$banner->description) }}</textarea>
                </div>

                {{-- Primary Button --}}
                <div class="col-md-6">
                    <label class="form-label">
                        Primary Button Text
                    </label>

                    <input type="text"
                        name="button_text"
                        class="form-control"
                        value="{{ old('button_text',$banner->button_text) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">
                        Primary Button Link
                    </label>

                    <input type="text"
                        name="button_link"
                        class="form-control"
                        value="{{ old('button_link',$banner->button_link) }}">
                </div>

                {{-- Secondary Button --}}
                <div class="col-md-6">
                    <label class="form-label">
                        Secondary Button Text
                    </label>

                    <input type="text"
                        name="secondary_button_text"
                        class="form-control"
                        value="{{ old('secondary_button_text',$banner->secondary_button_text) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">
                        Secondary Button Link
                    </label>

                    <input type="text"
                        name="secondary_button_link"
                        class="form-control"
                        value="{{ old('secondary_button_link',$banner->secondary_button_link) }}">
                </div>

                {{-- Sort Order --}}
                <div class="col-md-6">
                    <label class="form-label">
                        Sort Order
                    </label>

                    <input type="number"
                        name="sort_order"
                        class="form-control"
                        value="{{ old('sort_order',$banner->sort_order) }}">
                </div>

                {{-- Status --}}
                <div class="col-md-6">
                    <label class="form-label">
                        Status
                    </label>

                    <select name="status"
                        class="form-select">

                        <option value="1"
                            {{ old('status',$banner->status)==1 ? 'selected' : '' }}>
                            Active
                        </option>

                        <option value="0"
                            {{ old('status',$banner->status)==0 ? 'selected' : '' }}>
                            Inactive
                        </option>

                    </select>
                </div>

            </div>

            <div class="d-flex align-items-center gap-3 mt-4 pt-3 border-top">

                <button type="submit"
                    class="btn btn-save px-4">

                    <i class="fa-solid fa-floppy-disk me-2"></i>
                    Update Banner

                </button>

                <a href="{{ route('admin.banners.index') }}"
                    class="btn btn-outline-secondary rounded-pill px-4">

                    Cancel

                </a>

            </div>

        </form>

    </div>

</div>
@endsection

@section('scripts')
<script>
    function previewImage(input, previewWrapId, imgPreviewId, iconId) {
        const previewWrap = document.getElementById(previewWrapId);
        const imgPreview  = document.getElementById(imgPreviewId);
        const icon        = document.getElementById(iconId);
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imgPreview.src = e.target.result;
                previewWrap.style.display = 'flex';
                if (icon) icon.style.display = 'none';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const deviceSelect = document.getElementById('deviceTypeSelect');
        const desktopContainer = document.getElementById('desktopImageContainer');
        const mobileContainer = document.getElementById('mobileImageContainer');

        function toggleInputs() {
            const val = deviceSelect.value;
            if (val === 'both') {
                desktopContainer.style.display = 'block';
                mobileContainer.style.display = 'block';
            } else if (val === 'desktop') {
                desktopContainer.style.display = 'block';
                mobileContainer.style.display = 'none';
            } else if (val === 'mobile') {
                desktopContainer.style.display = 'none';
                mobileContainer.style.display = 'block';
            }
        }

        deviceSelect.addEventListener('change', toggleInputs);
        toggleInputs(); // Run once on initialization
    });
</script>
@endsection

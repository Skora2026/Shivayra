@extends('admin.layouts.app')

@section('content')

<div class="container-fluid p-0">


<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-700 text-dark mb-1">Create New Banner</h2>
        <p class="text-muted mb-0">Banners</p>
    </div>

    <div>
        <a href="{{ route('admin.banners.index') }}"
            class="btn btn-outline-secondary rounded-pill px-4 fw-600">
            <i class="fa-solid fa-arrow-left me-2"></i>
            {{ config('button.back') }}
        </a>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger border-0 rounded-3 mb-4">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card border-0 rounded-4 shadow-sm">
    <div class="card-body p-4">

        <form action="{{ route('admin.banners.store') }}"
            method="POST"
            enctype="multipart/form-data">

            @csrf

            <div class="row g-3">

                {{-- Device Type Dropdown --}}
                <div class="col-md-12">
                    <label class="form-label fw-600 text-dark">
                        Device / Display Type
                    </label>
                    <select name="device_type" id="deviceTypeSelect" class="form-select rounded-3">
                        <option value="both" {{ old('device_type') == 'both' ? 'selected' : '' }}>Both (Desktop & Mobile Banners)</option>
                        <option value="desktop" {{ old('device_type') == 'desktop' ? 'selected' : '' }}>Desktop Only Banner</option>
                        <option value="mobile" {{ old('device_type') == 'mobile' ? 'selected' : '' }}>Mobile Only Banner</option>
                    </select>
                </div>

                {{-- Banner Image --}}
                <div class="col-md-6" id="desktopImageContainer">
                    <label class="form-label fw-600 text-dark d-flex justify-content-between">
                        <span>Desktop Banner Image</span>
                        <small class="text-primary fw-normal">(Recommended: 1920x800px)</small>
                    </label>

                    <input type="file"
                        id="desktopImageInput"
                        name="image"
                        class="form-control rounded-3"
                        accept="image/*"
                        data-dropzone
                        data-preview="#desktop-image-preview"
                        required>
                    <small data-dropzone-hint style="display:none;"></small>
                    <img id="desktop-image-preview" alt="Desktop banner preview"
                         class="rounded-3 border mt-2" style="display:none; max-width:100%;">
                </div>

                {{-- Mobile Banner Image --}}
                <div class="col-md-6" id="mobileImageContainer">
                    <label class="form-label fw-600 text-dark d-flex justify-content-between">
                        <span>Mobile Banner Image</span>
                        <small class="text-primary fw-normal">(Recommended: 600x600px or 1:1 Aspect Ratio)</small>
                    </label>

                    <input type="file"
                        id="mobileImageInput"
                        name="mobile_image"
                        class="form-control rounded-3"
                        accept="image/*"
                        data-dropzone
                        data-preview="#mobile-image-preview">
                    <small data-dropzone-hint style="display:none;"></small>
                    <img id="mobile-image-preview" alt="Mobile banner preview"
                         class="rounded-3 border mt-2" style="display:none; max-width:100%;">
                </div>

                {{-- Badge --}}
                <div class="col-md-6">
                    <label class="form-label fw-600 text-dark">
                        Badge Text
                    </label>

                    <input type="text"
                        name="badge"
                        class="form-control rounded-3"
                        value="{{ old('badge') }}">
                </div>

                {{-- Title --}}
                <div class="col-md-12">
                    <label class="form-label fw-600 text-dark">
                        Banner Title
                    </label>

                    <input type="text"
                        name="title"
                        class="form-control rounded-3"
                        value="{{ old('title') }}"
                    >
                </div>

                {{-- Description --}}
                <div class="col-md-12">
                    <label class="form-label fw-600 text-dark">
                        Description
                    </label>

                    <textarea
                        name="description"
                        rows="4"
                        class="form-control rounded-3">{{ old('description') }}</textarea>
                </div>

                {{-- Button Text --}}
                <div class="col-md-6">
                    <label class="form-label fw-600 text-dark">
                        Primary Button Text
                    </label>

                    <input type="text"
                        name="button_text"
                        class="form-control rounded-3"
                        value="{{ old('button_text') }}">
                </div>

                {{-- Button Link --}}
                <div class="col-md-6">
                    <label class="form-label fw-600 text-dark">
                        Primary Button Link
                    </label>

                    <input type="text"
                        name="button_link"
                        class="form-control rounded-3"
                        value="{{ old('button_link') }}">
                </div>

                {{-- Secondary Button Text --}}
                <div class="col-md-6">
                    <label class="form-label fw-600 text-dark">
                        Secondary Button Text
                    </label>

                    <input type="text"
                        name="secondary_button_text"
                        class="form-control rounded-3"
                        value="{{ old('secondary_button_text') }}">
                </div>

                {{-- Secondary Button Link --}}
                <div class="col-md-6">
                    <label class="form-label fw-600 text-dark">
                        Secondary Button Link
                    </label>

                    <input type="text"
                        name="secondary_button_link"
                        class="form-control rounded-3"
                        value="{{ old('secondary_button_link') }}">
                </div>

                {{-- Sort Order --}}
                <div class="col-md-6">
                    <label class="form-label fw-600 text-dark">
                        Sort Order
                    </label>

                    <input type="number"
                        name="sort_order"
                        class="form-control rounded-3"
                        value="{{ old('sort_order',0) }}">
                </div>

                {{-- Status --}}
                <div class="col-md-6">
                    <label class="form-label fw-600 text-dark">
                        Status
                    </label>

                    <select name="status"
                        class="form-select rounded-3"
                        >

                        <option value="1"
                            {{ old('status') == '1' ? 'selected' : '' }}>
                            Active
                        </option>

                        <option value="0"
                            {{ old('status') == '0' ? 'selected' : '' }}>
                            Inactive
                        </option>

                    </select>
                </div>

            </div>

            <div class="d-flex gap-2 justify-content-end mt-4">

                <a href="{{ route('admin.banners.index') }}"
                    class="btn btn-light rounded-pill px-4">
                    {{ config('button.cancel') }}
                </a>

                <button type="submit"
                    class="btn btn-custom-primary px-5">
                    {{ config('button.submit') }}
                </button>

            </div>

        </form>

    </div>
</div>


</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const deviceSelect = document.getElementById('deviceTypeSelect');
        const desktopContainer = document.getElementById('desktopImageContainer');
        const mobileContainer = document.getElementById('mobileImageContainer');
        const desktopInput = document.getElementById('desktopImageInput');
        const mobileInput = document.getElementById('mobileImageInput');

        function toggleInputs() {
            const val = deviceSelect.value;
            if (val === 'both') {
                desktopContainer.style.display = 'block';
                mobileContainer.style.display = 'block';
                desktopInput.required = true;
                mobileInput.required = false;
            } else if (val === 'desktop') {
                desktopContainer.style.display = 'block';
                mobileContainer.style.display = 'none';
                desktopInput.required = true;
                mobileInput.required = false;
                mobileInput.value = ''; // Clear file if hidden
            } else if (val === 'mobile') {
                desktopContainer.style.display = 'none';
                mobileContainer.style.display = 'block';
                desktopInput.required = false;
                mobileInput.required = true;
                desktopInput.value = ''; // Clear file if hidden
            }
        }

        deviceSelect.addEventListener('change', toggleInputs);
        toggleInputs(); // Run once on initialization
    });
</script>
@endsection

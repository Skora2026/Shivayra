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
    .invalid-feedback { font-size: 0.82rem; }
</style>
@endsection

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('admin.categories.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
        <i class="fa-solid fa-arrow-left me-1"></i> Back
    </a>
    <h2 class="mb-0 fw-700" style="font-size:1.4rem;">{{ __('labels.create_new_category') }}</h2>
</div>

<div class="form-card">
    <div class="form-card-header">
        <h5 class="mb-0 fw-600"><i class="fa-solid fa-tags me-2"></i> Category Details</h5>
        <p class="mb-0 opacity-75 mt-1" style="font-size:0.875rem;">Fill in the information below to create a new category.</p>
    </div>
    <div class="form-card-body">
        <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf

            <div class="row g-4">
                {{-- Name --}}
                <div class="col-md-6">
                    <label class="form-label" for="cat-name">
                        Category Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" 
                           id="cat-name"
                           name="name" 
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}"
                           placeholder="e.g. Electronics"
                           required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Status --}}
                <div class="col-md-6">
                    <label class="form-label" for="cat-status">
                        Status <span class="text-danger">*</span>
                    </label>
                    <select id="cat-status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="">-- Select Status --</option>
                        <option value="active"   {{ old('status') === 'active'   ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Description --}}
                <div class="col-12">
                    <label class="form-label" for="cat-description">Description</label>
                    <textarea id="cat-description"
                              name="description" 
                              class="form-control @error('description') is-invalid @enderror"
                              rows="3"
                              placeholder="Brief description of this category...">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Image Upload --}}
                <div class="col-12">
                    <label class="form-label">Category Image</label>
                    <div class="image-upload-zone" id="upload-zone">
                        <input type="file" name="image" id="cat-image" accept="image/*" onchange="previewImage(this)">
                        <div id="upload-placeholder">
                            <div class="image-preview-wrap" id="preview-wrap" style="display:none;">
                                <img id="img-preview" src="" alt="Preview">
                            </div>
                            <i class="fa-solid fa-cloud-arrow-up fa-2x text-muted mb-2"></i>
                            <p class="mb-1 fw-500 text-secondary">Click or drag image here</p>
                            <p class="text-muted" style="font-size:0.8rem;">JPG, PNG, WEBP — max 2MB</p>
                        </div>
                    </div>
                    @error('image')
                        <div class="text-danger mt-1" style="font-size:0.82rem;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex align-items-center gap-3 mt-4 pt-3 border-top">
                <button type="submit" class="btn btn-save px-4">
                    <i class="fa-solid fa-floppy-disk me-2"></i> Save Category
                </button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function previewImage(input) {
        const previewWrap = document.getElementById('preview-wrap');
        const imgPreview  = document.getElementById('img-preview');
        const icon        = document.querySelector('.image-upload-zone .fa-cloud-arrow-up');
        
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
</script>
@endsection

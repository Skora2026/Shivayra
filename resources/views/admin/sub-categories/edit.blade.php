@extends('admin.layouts.app')

@section('styles')
<style>
    .form-card { background: #fff; border-radius: 1.5rem; box-shadow: 0 4px 24px rgba(0,0,0,0.06); overflow: hidden; }
    .form-card-header { background: linear-gradient(135deg, #7F3100 0%, #FE914B 100%); padding: 1.75rem 2rem; color: #fff; }
    .form-card-body { padding: 2rem; }
    .form-label { font-weight: 600; font-size: 0.9rem; color: #374151; margin-bottom: 0.4rem; }
    .form-control, .form-select { border: 1.5px solid #e5e7eb; border-radius: 0.75rem; padding: 0.6rem 1rem; font-size: 0.93rem; transition: border-color 0.2s, box-shadow 0.2s; }
    .form-control:focus, .form-select:focus { border-color: #FE914B; box-shadow: 0 0 0 3px rgba(254,145,75,0.12); }
    .image-upload-zone { border: 2px dashed #d1d5db; border-radius: 1rem; padding: 2rem; text-align: center; cursor: pointer; transition: all 0.2s; position: relative; background: #fafafa; }
    .image-upload-zone:hover { border-color: #FE914B; background: #fff8f5; }
    .image-upload-zone input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }
    .image-preview-wrap { width: 120px; height: 120px; border-radius: 1rem; overflow: hidden; border: 3px solid #e9ecef; margin: 0 auto 1rem; background: #f3f4f6; display: flex; align-items: center; justify-content: center; }
    .image-preview-wrap img { width: 100%; height: 100%; object-fit: cover; }
    .btn-save { background: linear-gradient(100deg, #7F3100 0%, #FE914B 100%); color: #fff; border: none; border-radius: 2rem; padding: 0.6rem 2rem; font-weight: 600; transition: all 0.2s; box-shadow: 0 4px 12px rgba(127,49,0,0.2); }
    .btn-save:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(127,49,0,0.3); color:#fff; }
    .current-image-badge { background: #fff8f5; border: 1.5px solid #fed7aa; border-radius: 0.75rem; padding: 0.75rem 1rem; display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem; }
    .current-image-badge img { width: 64px; height: 64px; object-fit: cover; border-radius: 0.5rem; }
</style>
@endsection

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('admin.sub-categories.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
        <i class="fa-solid fa-arrow-left me-1"></i> Back
    </a>
    <h2 class="mb-0 fw-700" style="font-size:1.4rem;">{{ __('labels.edit_sub_category') }}: {{ $subCategory->name }}</h2>
</div>

<div class="form-card">
    <div class="form-card-header">
        <h5 class="mb-0 fw-600"><i class="fa-solid fa-pen-to-square me-2"></i> Edit Sub-Category</h5>
        <p class="mb-0 opacity-75 mt-1" style="font-size:0.875rem;">Update the information below to edit this sub-category.</p>
    </div>
    <div class="form-card-body">
        <form action="{{ route('admin.sub-categories.update', $subCategory->id) }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf
            @method('PUT')

            <div class="row g-4">
                {{-- Parent Category --}}
                <div class="col-md-6">
                    <label class="form-label" for="subcat-category">
                        Parent Category <span class="text-danger">*</span>
                    </label>
                    <select id="subcat-category" name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                        <option value="">-- Select Category --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $subCategory->category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Status --}}
                <div class="col-md-6">
                    <label class="form-label" for="subcat-status">
                        Status <span class="text-danger">*</span>
                    </label>
                    <select id="subcat-status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="active"   {{ old('status', $subCategory->status) === 'active'   ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $subCategory->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Name --}}
                <div class="col-12">
                    <label class="form-label" for="subcat-name">
                        Sub-Category Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" 
                           id="subcat-name"
                           name="name" 
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $subCategory->name) }}"
                           required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Description --}}
                <div class="col-12">
                    <label class="form-label" for="subcat-description">Description</label>
                    <textarea id="subcat-description"
                              name="description" 
                              class="form-control @error('description') is-invalid @enderror"
                              rows="3">{{ old('description', $subCategory->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Image Upload --}}
                <div class="col-12">
                    <label class="form-label">Sub-Category Image</label>

                    @if($subCategory->image)
                    <div class="current-image-badge">
                        <img src="{{ $subCategory->image_url }}" alt="{{ $subCategory->name }}">
                        <div>
                            <p class="mb-0 fw-600" style="font-size:0.875rem;color:#7F3100;">Current Image</p>
                            <p class="mb-0 text-muted" style="font-size:0.8rem;">Upload a new image below to replace it.</p>
                        </div>
                    </div>
                    @endif

                    <div class="image-upload-zone">
                        <input type="file" name="image" id="subcat-image" accept="image/*" onchange="previewImage(this)">
                        <div class="image-preview-wrap" id="preview-wrap" style="display:none;">
                            <img id="img-preview" src="" alt="Preview">
                        </div>
                        <i class="fa-solid fa-cloud-arrow-up fa-2x text-muted mb-2" id="upload-icon"></i>
                        <p class="mb-1 fw-500 text-secondary">Click or drag new image here</p>
                        <p class="text-muted" style="font-size:0.8rem;">JPG, PNG, WEBP — max 2MB</p>
                    </div>
                    @error('image')
                        <div class="text-danger mt-1" style="font-size:0.82rem;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex align-items-center gap-3 mt-4 pt-3 border-top">
                <button type="submit" class="btn btn-save px-4">
                    <i class="fa-solid fa-floppy-disk me-2"></i> Update Sub-Category
                </button>
                <a href="{{ route('admin.sub-categories.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
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
        const icon        = document.getElementById('upload-icon');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                imgPreview.src = e.target.result;
                previewWrap.style.display = 'flex';
                if (icon) icon.style.display = 'none';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection

@extends('admin.layouts.app')

@section('styles')
    <style>
        .form-card {
            background: #fff;
            border-radius: 1.5rem;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        .form-card-header {
            background: linear-gradient(135deg, #1e3a5f 0%, #2d6aad 100%);
            padding: 1.75rem 2rem;
            color: #fff;
        }

        .form-card-body {
            padding: 2rem;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.9rem;
            color: #374151;
            margin-bottom: 0.4rem;
        }

        .form-control,
        .form-select {
            border: 1.5px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 0.6rem 1rem;
            font-size: 0.93rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #2d6aad;
            box-shadow: 0 0 0 3px rgba(45, 106, 173, 0.12);
        }

        .form-control:disabled {
            background: #f3f4f6;
            cursor: not-allowed;
        }

        .section-divider {
            border: none;
            border-top: 2px dashed #e9ecef;
            margin: 1.5rem 0;
        }

        .section-title {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #6b7280;
            margin-bottom: 1rem;
        }

        .price-input-group {
            position: relative;
        }

        .price-input-group .currency-symbol {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            font-weight: 600;
        }

        .price-input-group input {
            padding-left: 2rem;
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

        .image-upload-zone:hover {
            border-color: #2d6aad;
            background: #f0f7ff;
        }

        .image-upload-zone input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }

        .image-preview-wrap {
            width: 120px;
            height: 120px;
            border-radius: 1rem;
            overflow: hidden;
            border: 3px solid #e9ecef;
            margin: 0 auto 1rem;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .image-preview-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .btn-save {
            background: linear-gradient(100deg, #1e3a5f 0%, #2d6aad 100%);
            color: #fff;
            border: none;
            border-radius: 2rem;
            padding: 0.6rem 2rem;
            font-weight: 600;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(30, 58, 95, 0.2);
        }

        .btn-save:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(30, 58, 95, 0.3);
            color: #fff;
        }

        #subcat-loading {
            font-size: 0.82rem;
            color: #6b7280;
        }
    </style>
@endsection

@section('content')
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
            <i class="fa-solid fa-arrow-left me-1"></i> Back
        </a>
        <h2 class="mb-0 fw-700" style="font-size:1.4rem;">{{ __('labels.create_new_product') }}</h2>
    </div>

    <div class="form-card">
        <div class="form-card-header">
            <h5 class="mb-0 fw-600"><i class="fa-solid fa-box-open me-2"></i> Product Details</h5>
            <p class="mb-0 opacity-75 mt-1" style="font-size:0.875rem;">Fill in the information below to create a new
                product.</p>
        </div>
        <div class="form-card-body">
            <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf

                {{-- BASIC INFO --}}
                <p class="section-title"><i class="fa-solid fa-circle-info me-1"></i> Basic Information</p>
                <div class="row g-4">
                    {{-- Name --}}
                    <div class="col-md-8">
                        <label class="form-label" for="prod-name">Product Name <span class="text-danger">*</span></label>
                        <input type="text" id="prod-name" name="name"
                            class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}"
                            placeholder="e.g. iPhone 15 Pro" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Status --}}
                    <div class="col-md-4">
                        <label class="form-label" for="prod-status">Status <span class="text-danger">*</span></label>
                        <select id="prod-status" name="status" class="form-select @error('status') is-invalid @enderror"
                            required>
                            <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active
                            </option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Category --}}
                    <div class="col-md-6">
                        <label class="form-label" for="prod-category">Category <span class="text-danger">*</span></label>
                        <select id="prod-category" name="category_id"
                            class="form-select @error('category_id') is-invalid @enderror" required>
                            <option value="">-- Select Category --</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Sub-Category --}}
                    <div class="col-md-6">
                        <label class="form-label" for="prod-subcategory">
                            Sub-Category
                            <span id="subcat-loading" class="ms-2" style="display:none;">
                                <i class="fa-solid fa-spinner fa-spin"></i> Loading...
                            </span>
                        </label>
                        <select id="prod-subcategory" name="sub_category_id"
                            class="form-select @error('sub_category_id') is-invalid @enderror" disabled>
                            <option value="">-- Select Category First --</option>
                        </select>
                        @error('sub_category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div class="col-12">
                        <label class="form-label" for="prod-description">Description</label>
                        <textarea id="prod-description" name="description" class="form-control @error('description') is-invalid @enderror"
                            rows="4" placeholder="Detailed product description...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <hr class="section-divider">
                <p class="section-title"><i class="fa-solid fa-indian-rupee-sign me-1"></i> Pricing & Stock</p>
                <div class="row g-4">
                    <div class="col-md-4">
                        <label class="form-label" for="prod-price">Regular Price <span class="text-danger">*</span></label>
                        <div class="price-input-group">
                            <span class="currency-symbol">₹</span>
                            <input type="number" id="prod-price" name="price"
                                   class="form-control @error('price') is-invalid @enderror"
                                   value="{{ old('price') }}" min="0" step="0.01" required>
                        </div>
                        @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" for="prod-sale-price">Sale Price <small class="text-muted fw-400">(optional)</small></label>
                        <div class="price-input-group">
                            <span class="currency-symbol">₹</span>
                            <input type="number" id="prod-sale-price" name="sale_price"
                                   class="form-control @error('sale_price') is-invalid @enderror"
                                   value="{{ old('sale_price') }}" min="0" step="0.01" placeholder="0.00">
                        </div>
                        @error('sale_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" for="prod-stock">Stock Quantity <span class="text-danger">*</span></label>
                        <input type="number" id="prod-stock" name="stock"
                               class="form-control @error('stock') is-invalid @enderror"
                               value="{{ old('stock', '0') }}" min="0" required>
                        @error('stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <hr class="section-divider">
                <p class="section-title"><i class="fa-solid fa-tags me-1"></i> Specifications & Filters (Optional)</p>
                <div class="row g-4">
                    {{-- Size --}}
                <div class="row g-4">
                    {{-- Size --}}
                    <div class="col-md-4">
                        <label class="form-label" for="prod-size">Size / Ring Size</label>
                        <select id="prod-size" name="size" class="form-select @error('size') is-invalid @enderror">
                            <option value="">-- Select Size --</option>
                            <option value="Adjustable" {{ old('size') === 'Adjustable' ? 'selected' : '' }}>Adjustable</option>
                            @for ($i = 6; $i <= 25; $i++)
                                <option value="{{ $i }}" {{ old('size') === (string)$i ? 'selected' : '' }}>Size {{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    {{-- Color --}}
                    <div class="col-md-4">
                        <label class="form-label" for="prod-color">Metal Color</label>
                        <select id="prod-color" name="color" class="form-select @error('color') is-invalid @enderror">
                            <option value="">-- Select Color --</option>
                            <option value="Yellow Gold" {{ old('color') === 'Yellow Gold' ? 'selected' : '' }}>Yellow Gold</option>
                            <option value="Rose Gold" {{ old('color') === 'Rose Gold' ? 'selected' : '' }}>Rose Gold</option>
                            <option value="White Gold" {{ old('color') === 'White Gold' ? 'selected' : '' }}>White Gold</option>
                            <option value="Silver" {{ old('color') === 'Silver' ? 'selected' : '' }}>Silver</option>
                            <option value="Platinum" {{ old('color') === 'Platinum' ? 'selected' : '' }}>Platinum</option>
                            <option value="Two-Tone" {{ old('color') === 'Two-Tone' ? 'selected' : '' }}>Two-Tone</option>
                            <option value="Three-Tone" {{ old('color') === 'Three-Tone' ? 'selected' : '' }}>Three-Tone</option>
                        </select>
                        @error('color')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Pattern --}}
                    <div class="col-md-4">
                        <label class="form-label" for="prod-pattern">Purity / Carat</label>
                        <select id="prod-pattern" name="pattern" class="form-select @error('pattern') is-invalid @enderror">
                            <option value="">-- Select Purity --</option>
                            <option value="18K Gold" {{ old('pattern') === '18K Gold' ? 'selected' : '' }}>18K Gold</option>
                            <option value="22K Gold" {{ old('pattern') === '22K Gold' ? 'selected' : '' }}>22K Gold</option>
                            <option value="24K Gold" {{ old('pattern') === '24K Gold' ? 'selected' : '' }}>24K Gold</option>
                            <option value="925 Sterling Silver" {{ old('pattern') === '925 Sterling Silver' ? 'selected' : '' }}>925 Sterling Silver</option>
                            <option value="Platinum 950" {{ old('pattern') === 'Platinum 950' ? 'selected' : '' }}>Platinum 950</option>
                        </select>
                        @error('pattern')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Occasion --}}
                    <div class="col-md-4">
                        <label class="form-label" for="prod-occasion">Occasion</label>
                        <select id="prod-occasion" name="occasion"
                            class="form-select @error('occasion') is-invalid @enderror">
                            <option value="">-- Select Occasion --</option>
                            <option value="Daily Wear" {{ old('occasion') === 'Daily Wear' ? 'selected' : '' }}>Daily Wear</option>
                            <option value="Wedding" {{ old('occasion') === 'Wedding' ? 'selected' : '' }}>Wedding</option>
                            <option value="Engagement" {{ old('occasion') === 'Engagement' ? 'selected' : '' }}>Engagement</option>
                            <option value="Anniversary" {{ old('occasion') === 'Anniversary' ? 'selected' : '' }}>Anniversary</option>
                            <option value="Festive" {{ old('occasion') === 'Festive' ? 'selected' : '' }}>Festive</option>
                            <option value="Gifting" {{ old('occasion') === 'Gifting' ? 'selected' : '' }}>Gifting</option>
                        </select>
                        @error('occasion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Metal / Material --}}
                    <div class="col-md-4">
                        <label class="form-label" for="prod-fabric">Metal / Material</label>
                        <select id="prod-fabric" name="fabric" class="form-select @error('fabric') is-invalid @enderror">
                            <option value="">-- Select Metal --</option>
                            <option value="Gold" {{ old('fabric') === 'Gold' ? 'selected' : '' }}>Gold</option>
                            <option value="Silver" {{ old('fabric') === 'Silver' ? 'selected' : '' }}>Silver</option>
                            <option value="Rose Gold" {{ old('fabric') === 'Rose Gold' ? 'selected' : '' }}>Rose Gold</option>
                            <option value="Platinum" {{ old('fabric') === 'Platinum' ? 'selected' : '' }}>Platinum</option>
                            <option value="Brass / Alloy" {{ old('fabric') === 'Brass / Alloy' ? 'selected' : '' }}>Brass / Alloy</option>
                        </select>
                        @error('fabric')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Gemstone --}}
                    <div class="col-md-4">
                        <label class="form-label" for="prod-neckline">Gemstone</label>
                        <select id="prod-neckline" name="neckline"
                            class="form-select @error('neckline') is-invalid @enderror">
                            <option value="">-- Select Gemstone --</option>
                            <option value="Diamond" {{ old('neckline') === 'Diamond' ? 'selected' : '' }}>Diamond</option>
                            <option value="Emerald" {{ old('neckline') === 'Emerald' ? 'selected' : '' }}>Emerald</option>
                            <option value="Ruby" {{ old('neckline') === 'Ruby' ? 'selected' : '' }}>Ruby</option>
                            <option value="Sapphire" {{ old('neckline') === 'Sapphire' ? 'selected' : '' }}>Sapphire</option>
                            <option value="Pearl" {{ old('neckline') === 'Pearl' ? 'selected' : '' }}>Pearl</option>
                            <option value="Cubic Zirconia" {{ old('neckline') === 'Cubic Zirconia' ? 'selected' : '' }}>Cubic Zirconia (CZ)</option>
                            <option value="No Gemstone" {{ old('neckline') === 'No Gemstone' ? 'selected' : '' }}>No Gemstone</option>
                        </select>
                        @error('neckline')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- New Arrival --}}
                    <div class="col-md-4">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" id="prod-new-arrival" name="is_new_arrival"
                                value="1" {{ old('is_new_arrival') ? 'checked' : '' }}>
                            <label class="form-check-label fw-600" for="prod-new-arrival">New Arrival</label>
                            <small class="form-text text-muted d-block">Show this product in the "New Arrivals" list.</small>
                        </div>
                    </div>

                    {{-- Trending --}}
                    <div class="col-md-4">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" id="prod-trending" name="is_trending"
                                value="1" {{ old('is_trending') ? 'checked' : '' }}>
                            <label class="form-check-label fw-600" for="prod-trending">Trending Product</label>
                            <small class="form-text text-muted d-block">Show this product in the "Trending Products" section.</small>
                        </div>
                    </div>

                    {{-- Featured --}}
                    <div class="col-md-4">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" id="prod-featured" name="is_featured"
                                value="1" {{ old('is_featured') ? 'checked' : '' }}>
                            <label class="form-check-label fw-600" for="prod-featured">Featured Product</label>
                            <small class="form-text text-muted d-block">Show this product in the "Featured Products" section.</small>
                        </div>
                    </div>
                </div>

                <hr class="section-divider">
                <p class="section-title"><i class="fa-solid fa-list-check me-1"></i> Custom Specifications (Key-Value)</p>
                <div id="specs-container" class="mb-3">
                    <!-- Starts empty for new products -->
                </div>
                <button type="button" class="btn btn-outline-success btn-sm rounded-pill mb-4" id="add-spec-btn">
                    <i class="fa-solid fa-plus me-1"></i> Add Specification Field
                </button>

                <hr class="section-divider">

                <div class="mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0 fw-600"><i class="fa-solid fa-layer-group me-1"></i> Product Variants</h6>
                        <button type="button" id="add-variant-btn" class="btn btn-sm btn-primary rounded-pill px-3">
                            <i class="fa-solid fa-plus me-1"></i> Add Variant
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" id="variants-table">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 180px;">
                                        <div class="d-flex align-items-center gap-1">
                                            <input type="text" name="variant_name_1"
                                                class="form-control form-control-sm fw-700 text-dark bg-transparent border-0 border-bottom border-secondary-subtle"
                                                value="Size" placeholder="Variant 1 Name">
                                            <i class="fa-solid fa-pen-to-square text-muted"
                                                style="font-size: 0.8rem;"></i>
                                        </div>
                                        <select class="form-select form-select-sm mt-1 border-0 text-muted"
                                            style="font-size:0.75rem;" disabled>
                                            <option>Text Type</option>
                                        </select>
                                    </th>
                                    <th style="width: 180px;">
                                        <div class="d-flex align-items-center gap-1">
                                            <input type="text" name="variant_name_2"
                                                class="form-control form-control-sm fw-700 text-dark bg-transparent border-0 border-bottom border-secondary-subtle"
                                                value="Color" placeholder="Variant 2 Name">
                                            <i class="fa-solid fa-pen-to-square text-muted"
                                                style="font-size: 0.8rem;"></i>
                                        </div>
                                        <select class="form-select form-select-sm mt-1 border-0 text-muted"
                                            style="font-size:0.75rem;" disabled>
                                            <option>Color Type</option>
                                        </select>
                                    </th>
                                    <th>Regular Price *</th>
                                    <th>Sale Price (optional)</th>
                                    <th style="width: 110px;">Stock *</th>
                                    <th style="width: 250px;">Variant Image</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody id="variants-container">
                                <!-- Starts empty for new products -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <hr class="section-divider">
                <p class="section-title"><i class="fa-solid fa-image me-1"></i> Product Main Image</p>
                <div class="image-upload-zone mb-3" id="upload-zone">
                    <input type="file" name="image" id="prod-image" accept="image/*"
                        onchange="previewImage(this)">
                    <div class="image-preview-wrap" id="preview-wrap" style="display:none;">
                        <img id="img-preview" src="" alt="Preview">
                    </div>
                    <i class="fa-solid fa-cloud-arrow-up fa-2x text-muted mb-2" id="upload-icon"></i>
                    <p class="mb-1 fw-500 text-secondary">Click or drag product image here</p>
                    <p class="text-muted" style="font-size:0.8rem;">JPG, PNG, WEBP — max 3MB</p>
                </div>
                @error('image')
                    <div class="text-danger mt-1 mb-3" style="font-size:0.82rem;">{{ $message }}</div>
                @enderror

                {{-- <hr class="section-divider">
                <p class="section-title"><i class="fa-solid fa-images me-1"></i> Product Gallery Images (Multiple)</p>
                <div class="image-upload-zone mb-3" style="border: 2px dashed rgba(10, 144, 81, 0.25);">
                    <input type="file" name="gallery[]" id="prod-gallery" accept="image/*" multiple>
                    <i class="fa-solid fa-images fa-2x text-muted mb-2"></i>
                    <p class="mb-1 fw-500 text-secondary">Click or drag additional gallery images here</p>
                    <p class="text-muted" style="font-size:0.8rem;">Select multiple files at once — JPG, PNG, WEBP</p>
                </div> --}}
                @error('gallery')
                    <div class="text-danger mt-1" style="font-size:0.82rem;">{{ $message }}</div>
                @enderror

                <div class="d-flex align-items-center gap-3 mt-4 pt-3 border-top">
                    <button type="submit" class="btn btn-save px-4">
                        <i class="fa-solid fa-floppy-disk me-2"></i> Save Product
                    </button>
                    <a href="{{ route('admin.products.index') }}"
                        class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function previewImage(input) {
            const previewWrap = document.getElementById('preview-wrap');
            const imgPreview = document.getElementById('img-preview');
            const icon = document.getElementById('upload-icon');
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

        // AJAX Cascading Sub-Category Dropdown
        const categorySelect = document.getElementById('prod-category');
        const subCategorySelect = document.getElementById('prod-subcategory');
        const loadingSpinner = document.getElementById('subcat-loading');

        categorySelect.addEventListener('change', function() {
            const categoryId = this.value;
            subCategorySelect.innerHTML = '<option value="">-- Loading... --</option>';
            subCategorySelect.disabled = true;
            loadingSpinner.style.display = 'inline';

            if (!categoryId) {
                subCategorySelect.innerHTML = '<option value="">-- Select Category First --</option>';
                loadingSpinner.style.display = 'none';
                return;
            }

            fetch(`{{ url('admin/sub-categories/by-category') }}/${categoryId}`)
                .then(res => res.json())
                .then(data => {
                    loadingSpinner.style.display = 'none';
                    subCategorySelect.disabled = false;
                    if (data.length === 0) {
                        subCategorySelect.innerHTML =
                            '<option value="">-- No Sub-Categories Available --</option>';
                    } else {
                        subCategorySelect.innerHTML =
                            '<option value="">-- Select Sub-Category (Optional) --</option>';
                        data.forEach(sub => {
                            subCategorySelect.innerHTML +=
                                `<option value="${sub.id}">${sub.name}</option>`;
                        });
                    }
                })
                .catch(() => {
                    loadingSpinner.style.display = 'none';
                    subCategorySelect.innerHTML = '<option value="">-- Error loading --</option>';
                    subCategorySelect.disabled = false;
                });
        });

        // Dynamic Specifications add/remove
        const specsContainer = document.getElementById('specs-container');
        const addSpecBtn = document.getElementById('add-spec-btn');

        if (addSpecBtn && specsContainer) {
            addSpecBtn.addEventListener('click', function() {
                const specRow = document.createElement('div');
                specRow.className = 'row g-2 mb-2 spec-row';
                specRow.innerHTML = `
                <div class="col-md-5">
                    <input type="text" name="spec_names[]" class="form-control" placeholder="Specification Name (e.g. Weight, Material)">
                </div>
                <div class="col-md-5">
                    <input type="text" name="spec_values[]" class="form-control" placeholder="Value (e.g. 280g, 100% Cotton)">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger w-100 remove-spec-btn"><i class="fa-solid fa-trash"></i> Remove</button>
                </div>
            `;
                specsContainer.appendChild(specRow);
            });

            specsContainer.addEventListener('click', function(e) {
                if (e.target.closest('.remove-spec-btn')) {
                    e.target.closest('.spec-row').remove();
                }
            });
        }

        // Dynamic Variants add/remove
        const variantsContainer = document.getElementById('variants-container');
        const addVariantBtn = document.getElementById('add-variant-btn');
        let variantIndex = 0;

        if (addVariantBtn && variantsContainer) {
            addVariantBtn.addEventListener('click', function() {
                const tr = document.createElement('tr');
                tr.className = 'variant-row';
                tr.innerHTML = `
                <td>
                    <input type="text" name="variants[\${variantIndex}][value_1]" class="form-control form-control-sm" placeholder="e.g. S, M, L or 4g">
                </td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <input type="color" class="form-control-color border-0 p-0" style="width: 30px; height: 30px; cursor: pointer; border-radius: 50%;" value="#000000" onchange="document.getElementById('hex_val_\${variantIndex}').value = this.value">
                        <input type="text" id="hex_val_\${variantIndex}" name="variants[\${variantIndex}][value_2]" class="form-control form-control-sm" placeholder="#c63939" value="#000000">
                    </div>
                </td>
                <td>
                    <div class="price-input-group">
                        <span class="currency-symbol">₹</span>
                        <input type="number" step="0.01" name="variants[\${variantIndex}][price]" class="form-control form-control-sm" placeholder="2500.00" required style="padding-left:1.5rem;">
                    </div>
                </td>
                <td>
                    <div class="price-input-group">
                        <span class="currency-symbol">₹</span>
                        <input type="number" step="0.01" name="variants[\${variantIndex}][sale_price]" class="form-control form-control-sm" placeholder="1899.00" style="padding-left:1.5rem;">
                    </div>
                </td>
                <td>
                    <input type="number" name="variants[\${variantIndex}][stock]" class="form-control form-control-sm" placeholder="23" required>
                </td>
                <td>
                    <input type="file" name="variants[\${variantIndex}][images][]" class="form-control form-control-sm" accept="image/*" multiple>
                </td>
                <td>
                    <button type="button" class="btn btn-outline-danger btn-sm border-0 remove-variant-row"><i class="fa-solid fa-trash"></i></button>
                </td>
            `;
                variantsContainer.appendChild(tr);
                variantIndex++;
            });

            variantsContainer.addEventListener('click', function(e) {
                if (e.target.closest('.remove-variant-row')) {
                    e.target.closest('.variant-row').remove();
                }
            });
        }
    </script>
@endsection

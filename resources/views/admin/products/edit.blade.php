@extends('admin.layouts.app')

@section('styles')
<style>
    .form-card { background: #fff; border-radius: 1.5rem; box-shadow: 0 4px 24px rgba(0,0,0,0.06); overflow: hidden; }
    .form-card-header { background: linear-gradient(135deg, #1e3a5f 0%, #2d6aad 100%); padding: 1.75rem 2rem; color: #fff; }
    .form-card-body { padding: 2rem; }
    .form-label { font-weight: 600; font-size: 0.9rem; color: #374151; margin-bottom: 0.4rem; }
    .form-control, .form-select { border: 1.5px solid #e5e7eb; border-radius: 0.75rem; padding: 0.6rem 1rem; font-size: 0.93rem; transition: border-color 0.2s, box-shadow 0.2s; }
    .form-control:focus, .form-select:focus { border-color: #2d6aad; box-shadow: 0 0 0 3px rgba(45,106,173,0.12); }
    .section-divider { border: none; border-top: 2px dashed #e9ecef; margin: 1.5rem 0; }
    .section-title { font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #6b7280; margin-bottom: 1rem; }
    .price-input-group { position: relative; }
    .price-input-group .currency-symbol { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #6b7280; font-weight: 600; }
    .price-input-group input { padding-left: 2rem; }
    .image-upload-zone { border: 2px dashed #d1d5db; border-radius: 1rem; padding: 2rem; text-align: center; cursor: pointer; transition: all 0.2s; position: relative; background: #fafafa; }
    .image-upload-zone:hover { border-color: #2d6aad; background: #f0f7ff; }
    .image-upload-zone input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }
    .image-preview-wrap { width: 120px; height: 120px; border-radius: 1rem; overflow: hidden; border: 3px solid #e9ecef; margin: 0 auto 1rem; background: #f3f4f6; display: flex; align-items: center; justify-content: center; }
    .image-preview-wrap img { width: 100%; height: 100%; object-fit: cover; }
    .btn-save { background: linear-gradient(100deg, #1e3a5f 0%, #2d6aad 100%); color: #fff; border: none; border-radius: 2rem; padding: 0.6rem 2rem; font-weight: 600; transition: all 0.2s; box-shadow: 0 4px 12px rgba(30,58,95,0.2); }
    .btn-save:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(30,58,95,0.3); color:#fff; }
    .current-image-badge { background: #eff6ff; border: 1.5px solid #bfdbfe; border-radius: 0.75rem; padding: 0.75rem 1rem; display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem; }
    .current-image-badge img { width: 64px; height: 64px; object-fit: cover; border-radius: 0.5rem; }
</style>
@endsection

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
        <i class="fa-solid fa-arrow-left me-1"></i> Back
    </a>
    <h2 class="mb-0 fw-700" style="font-size:1.4rem;">{{ __('labels.edit_product') }}: {{ $product->name }}</h2>
</div>

<div class="form-card">
    <div class="form-card-header">
        <h5 class="mb-0 fw-600"><i class="fa-solid fa-pen-to-square me-2"></i> Edit Product</h5>
        <p class="mb-0 opacity-75 mt-1" style="font-size:0.875rem;">Update the information below to edit this product.</p>
    </div>
    <div class="form-card-body">
        <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf
            @method('PUT')

            {{-- BASIC INFO --}}
            <p class="section-title"><i class="fa-solid fa-circle-info me-1"></i> Basic Information</p>
            <div class="row g-4">
                <div class="col-md-8">
                    <label class="form-label" for="prod-name">Product Name <span class="text-danger">*</span></label>
                    <input type="text" id="prod-name" name="name" 
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $product->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="prod-status">Status <span class="text-danger">*</span></label>
                    <select id="prod-status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="active"   {{ old('status', $product->status) === 'active'   ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $product->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="prod-category">Category <span class="text-danger">*</span></label>
                    <select id="prod-category" name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                        <option value="">-- Select Category --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="prod-subcategory">
                        Sub-Category
                        <span id="subcat-loading" class="ms-2" style="display:none;">
                            <i class="fa-solid fa-spinner fa-spin"></i> Loading...
                        </span>
                    </label>
                    <select id="prod-subcategory" name="sub_category_id" 
                            class="form-select @error('sub_category_id') is-invalid @enderror">
                        <option value="">-- None --</option>
                        @foreach($subCategories as $sub)
                            <option value="{{ $sub->id }}" {{ old('sub_category_id', $product->sub_category_id) == $sub->id ? 'selected' : '' }}>
                                {{ $sub->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('sub_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label" for="prod-description">Description</label>
                    <textarea id="prod-description" name="description" 
                              class="form-control @error('description') is-invalid @enderror"
                              rows="4">{{ old('description', $product->description) }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                               value="{{ old('price', $product->price) }}" min="0" step="0.01" required>
                    </div>
                    @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="prod-sale-price">Sale Price <small class="text-muted fw-400">(optional)</small></label>
                    <div class="price-input-group">
                        <span class="currency-symbol">₹</span>
                        <input type="number" id="prod-sale-price" name="sale_price"
                               class="form-control @error('sale_price') is-invalid @enderror"
                               value="{{ old('sale_price', $product->sale_price) }}" min="0" step="0.01">
                    </div>
                    @error('sale_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="prod-stock">Stock Quantity <span class="text-danger">*</span></label>
                    <input type="number" id="prod-stock" name="stock"
                           class="form-control @error('stock') is-invalid @enderror"
                           value="{{ old('stock', $product->stock) }}" min="0" required>
                    @error('stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <hr class="section-divider">
            <p class="section-title"><i class="fa-solid fa-tags me-1"></i> Specifications & Filters (Optional)</p>
            <div class="row g-4">
                {{-- Size --}}
                <div class="col-md-4">
                    <label class="form-label" for="prod-size">Size / Ring Size</label>
                    <select id="prod-size" name="size" class="form-select @error('size') is-invalid @enderror">
                        <option value="">-- Select Size --</option>
                        <option value="Adjustable" {{ old('size', $product->size) === 'Adjustable' ? 'selected' : '' }}>Adjustable</option>
                        @for ($i = 6; $i <= 25; $i++)
                            <option value="{{ $i }}" {{ old('size', $product->size) === (string)$i ? 'selected' : '' }}>Size {{ $i }}</option>
                        @endfor
                    </select>
                    @error('size')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Color --}}
                <div class="col-md-4">
                    <label class="form-label" for="prod-color">Metal Color</label>
                    <select id="prod-color" name="color" class="form-select @error('color') is-invalid @enderror">
                        <option value="">-- Select Color --</option>
                        <option value="Yellow Gold" {{ old('color', $product->color) === 'Yellow Gold' ? 'selected' : '' }}>Yellow Gold</option>
                        <option value="Rose Gold" {{ old('color', $product->color) === 'Rose Gold' ? 'selected' : '' }}>Rose Gold</option>
                        <option value="White Gold" {{ old('color', $product->color) === 'White Gold' ? 'selected' : '' }}>White Gold</option>
                        <option value="Silver" {{ old('color', $product->color) === 'Silver' ? 'selected' : '' }}>Silver</option>
                        <option value="Platinum" {{ old('color', $product->color) === 'Platinum' ? 'selected' : '' }}>Platinum</option>
                        <option value="Two-Tone" {{ old('color', $product->color) === 'Two-Tone' ? 'selected' : '' }}>Two-Tone</option>
                        <option value="Three-Tone" {{ old('color', $product->color) === 'Three-Tone' ? 'selected' : '' }}>Three-Tone</option>
                    </select>
                    @error('color')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Pattern --}}
                <div class="col-md-4">
                    <label class="form-label" for="prod-pattern">Purity / Carat</label>
                    <select id="prod-pattern" name="pattern" class="form-select @error('pattern') is-invalid @enderror">
                        <option value="">-- Select Purity --</option>
                        <option value="18K Gold" {{ old('pattern', $product->pattern) === '18K Gold' ? 'selected' : '' }}>18K Gold</option>
                        <option value="22K Gold" {{ old('pattern', $product->pattern) === '22K Gold' ? 'selected' : '' }}>22K Gold</option>
                        <option value="24K Gold" {{ old('pattern', $product->pattern) === '24K Gold' ? 'selected' : '' }}>24K Gold</option>
                        <option value="925 Sterling Silver" {{ old('pattern', $product->pattern) === '925 Sterling Silver' ? 'selected' : '' }}>925 Sterling Silver</option>
                        <option value="Platinum 950" {{ old('pattern', $product->pattern) === 'Platinum 950' ? 'selected' : '' }}>Platinum 950</option>
                    </select>
                    @error('pattern')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Occasion --}}
                <div class="col-md-4">
                    <label class="form-label" for="prod-occasion">Occasion</label>
                    <select id="prod-occasion" name="occasion" class="form-select @error('occasion') is-invalid @enderror">
                        <option value="">-- Select Occasion --</option>
                        <option value="Daily Wear" {{ old('occasion', $product->occasion) === 'Daily Wear' ? 'selected' : '' }}>Daily Wear</option>
                        <option value="Wedding" {{ old('occasion', $product->occasion) === 'Wedding' ? 'selected' : '' }}>Wedding</option>
                        <option value="Engagement" {{ old('occasion', $product->occasion) === 'Engagement' ? 'selected' : '' }}>Engagement</option>
                        <option value="Anniversary" {{ old('occasion', $product->occasion) === 'Anniversary' ? 'selected' : '' }}>Anniversary</option>
                        <option value="Festive" {{ old('occasion', $product->occasion) === 'Festive' ? 'selected' : '' }}>Festive</option>
                        <option value="Gifting" {{ old('occasion', $product->occasion) === 'Gifting' ? 'selected' : '' }}>Gifting</option>
                    </select>
                    @error('occasion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Metal / Material --}}
                <div class="col-md-4">
                    <label class="form-label" for="prod-fabric">Metal / Material</label>
                    <select id="prod-fabric" name="fabric" class="form-select @error('fabric') is-invalid @enderror">
                        <option value="">-- Select Metal --</option>
                        <option value="Gold" {{ old('fabric', $product->fabric) === 'Gold' ? 'selected' : '' }}>Gold</option>
                        <option value="Silver" {{ old('fabric', $product->fabric) === 'Silver' ? 'selected' : '' }}>Silver</option>
                        <option value="Rose Gold" {{ old('fabric', $product->fabric) === 'Rose Gold' ? 'selected' : '' }}>Rose Gold</option>
                        <option value="Platinum" {{ old('fabric', $product->fabric) === 'Platinum' ? 'selected' : '' }}>Platinum</option>
                        <option value="Brass / Alloy" {{ old('fabric', $product->fabric) === 'Brass / Alloy' ? 'selected' : '' }}>Brass / Alloy</option>
                    </select>
                    @error('fabric')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Gemstone --}}
                <div class="col-md-4">
                    <label class="form-label" for="prod-neckline">Gemstone</label>
                    <select id="prod-neckline" name="neckline" class="form-select @error('neckline') is-invalid @enderror">
                        <option value="">-- Select Gemstone --</option>
                        <option value="Diamond" {{ old('neckline', $product->neckline) === 'Diamond' ? 'selected' : '' }}>Diamond</option>
                        <option value="Emerald" {{ old('neckline', $product->neckline) === 'Emerald' ? 'selected' : '' }}>Emerald</option>
                        <option value="Ruby" {{ old('neckline', $product->neckline) === 'Ruby' ? 'selected' : '' }}>Ruby</option>
                        <option value="Sapphire" {{ old('neckline', $product->neckline) === 'Sapphire' ? 'selected' : '' }}>Sapphire</option>
                        <option value="Pearl" {{ old('neckline', $product->neckline) === 'Pearl' ? 'selected' : '' }}>Pearl</option>
                        <option value="Cubic Zirconia" {{ old('neckline', $product->neckline) === 'Cubic Zirconia' ? 'selected' : '' }}>Cubic Zirconia (CZ)</option>
                        <option value="No Gemstone" {{ old('neckline', $product->neckline) === 'No Gemstone' ? 'selected' : '' }}>No Gemstone</option>
                    </select>
                    @error('neckline')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <hr class="section-divider">
            <p class="section-title"><i class="fa-solid fa-star me-1"></i> Product Promotions / Flags</p>
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="prod-new-arrival" name="is_new_arrival" value="1" {{ old('is_new_arrival', $product->is_new_arrival) ? 'checked' : '' }}>
                        <label class="form-check-label fw-600" for="prod-new-arrival">New Arrival</label>
                        <small class="form-text text-muted d-block">Show this product in the "New Arrivals" list.</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="prod-trending" name="is_trending" value="1" {{ old('is_trending', $product->is_trending) ? 'checked' : '' }}>
                        <label class="form-check-label fw-600" for="prod-trending">Trending Product</label>
                        <small class="form-text text-muted d-block">Show this product in the "Trending Products" section on the homepage.</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="prod-featured" name="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                        <label class="form-check-label fw-600" for="prod-featured">Featured Product</label>
                        <small class="form-text text-muted d-block">Show this product in the "Featured Products" section on the homepage.</small>
                    </div>
                </div>
            </div>

            <hr class="section-divider">
            <p class="section-title"><i class="fa-solid fa-list-check me-1"></i> Custom Specifications (Key-Value)</p>
            <div id="specs-container" class="mb-3">
                @php $specs = $product->specifications ?? []; @endphp
                @foreach($specs as $idx => $spec)
                    <div class="row g-2 mb-2 spec-row">
                        <div class="col-md-5">
                            <input type="text" name="spec_names[]" class="form-control" placeholder="Specification Name (e.g. Weight, Material)" value="{{ $spec['name'] }}">
                        </div>
                        <div class="col-md-5">
                            <input type="text" name="spec_values[]" class="form-control" placeholder="Value (e.g. 280g, 100% Cotton)" value="{{ $spec['value'] }}">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger w-100 remove-spec-btn"><i class="fa-solid fa-trash"></i> Remove</button>
                        </div>
                    </div>
                @endforeach
            </div>
            <button type="button" class="btn btn-outline-success btn-sm rounded-pill mb-4" id="add-spec-btn">
                <i class="fa-solid fa-plus me-1"></i> Add Specification Field
            </button>

            <hr class="section-divider">
            
            <div class="card border border-light-subtle rounded-4 p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-700 text-dark mb-0"><i class="fa-solid fa-tags text-success me-2"></i> Product Variants</h5>
                    <button type="button" class="btn btn-outline-primary rounded-pill btn-sm px-3" id="add-variant-btn">
                        <i class="fa-solid fa-plus me-1"></i> Add Variant Row
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle border" id="variants-table">
                        <thead>
                            <tr class="bg-light">
                                <th style="width: 180px;">
                                    <div class="d-flex align-items-center gap-1">
                                        <input type="text" name="variant_name_1" class="form-control form-control-sm fw-700 text-dark bg-transparent border-0 border-bottom border-secondary-subtle" value="{{ old('variant_name_1', $product->variant_name_1 ?? 'Size') }}" placeholder="Variant 1 Name">
                                        <i class="fa-solid fa-pen-to-square text-muted" style="font-size: 0.8rem;"></i>
                                    </div>
                                    <select class="form-select form-select-sm mt-1 border-0 text-muted" style="font-size:0.75rem;" disabled>
                                        <option>Text Type</option>
                                    </select>
                                </th>
                                <th style="width: 180px;">
                                    <div class="d-flex align-items-center gap-1">
                                        <input type="text" name="variant_name_2" class="form-control form-control-sm fw-700 text-dark bg-transparent border-0 border-bottom border-secondary-subtle" value="{{ old('variant_name_2', $product->variant_name_2 ?? 'Color') }}" placeholder="Variant 2 Name">
                                        <i class="fa-solid fa-pen-to-square text-muted" style="font-size: 0.8rem;"></i>
                                    </div>
                                    <select class="form-select form-select-sm mt-1 border-0 text-muted" style="font-size:0.75rem;" disabled>
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
                            @foreach($product->variants as $index => $variant)
                                <tr class="variant-row">
                                    <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $variant->id }}">
                                    <input type="hidden" name="variants[{{ $index }}][old_image]" value="{{ $variant->image }}">
                                    <td>
                                        <input type="text" name="variants[{{ $index }}][value_1]" class="form-control form-control-sm" placeholder="e.g. S, M, L or 4g" value="{{ $variant->value_1 }}">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="color" class="form-control-color border-0 p-0" style="width: 30px; height: 30px; cursor: pointer; border-radius: 50%;" value="{{ str_starts_with($variant->value_2, '#') ? $variant->value_2 : '#000000' }}" onchange="document.getElementById('hex_val_{{ $index }}').value = this.value">
                                            <input type="text" id="hex_val_{{ $index }}" name="variants[{{ $index }}][value_2]" class="form-control form-control-sm" placeholder="#c63939" value="{{ $variant->value_2 }}">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="price-input-group">
                                            <span class="currency-symbol">₹</span>
                                            <input type="number" step="0.01" name="variants[{{ $index }}][price]" class="form-control form-control-sm" placeholder="2500.00" value="{{ $variant->price }}" required style="padding-left:1.5rem;">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="price-input-group">
                                            <span class="currency-symbol">₹</span>
                                            <input type="number" step="0.01" name="variants[{{ $index }}][sale_price]" class="form-control form-control-sm" placeholder="1899.00" value="{{ $variant->sale_price }}" style="padding-left:1.5rem;">
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" name="variants[{{ $index }}][stock]" class="form-control form-control-sm" placeholder="23" value="{{ $variant->stock }}" required>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-2">
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach($variant->images as $imgIndex => $img)
                                                    <div class="position-relative var-img-card" style="width: 40px; height: 40px;">
                                                        <img src="{{ filter_var($img, FILTER_VALIDATE_URL) ? $img : asset('storage/' . $img) }}" alt="var" class="rounded border" style="width: 40px; height: 40px; object-fit: cover;">
                                                        <input type="hidden" name="variants[{{ $index }}][existing_images][]" value="{{ $img }}">
                                                        <button type="button" class="btn btn-danger btn-xs position-absolute top-0 end-0 rounded-circle remove-var-img-btn" style="padding: 0px 4px; font-size: 0.65rem; border: none; transform: translate(30%, -30%);">&times;</button>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <input type="file" name="variants[{{ $index }}][images][]" class="form-control form-control-sm" accept="image/*" multiple>
                                        </div>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-outline-danger btn-sm border-0 remove-variant-row"><i class="fa-solid fa-trash"></i></button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <hr class="section-divider">
            <p class="section-title"><i class="fa-solid fa-image me-1"></i> Product Main Image</p>

            @if($product->image)
            <div class="current-image-badge">
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
                <div>
                    <p class="mb-0 fw-600" style="font-size:0.875rem;color:#1e3a5f;">Current Image</p>
                    <p class="mb-0 text-muted" style="font-size:0.8rem;">Upload a new image below to replace it.</p>
                </div>
            </div>
            @endif

            <div class="image-upload-zone mb-3">
                <input type="file" name="image" id="prod-image" accept="image/*" onchange="previewImage(this)">
                <div class="image-preview-wrap" id="preview-wrap" style="display:none;">
                    <img id="img-preview" src="" alt="Preview">
                </div>
                <i class="fa-solid fa-cloud-arrow-up fa-2x text-muted mb-2" id="upload-icon"></i>
                <p class="mb-1 fw-500 text-secondary">Click or drag new product image here</p>
                <p class="text-muted" style="font-size:0.8rem;">JPG, PNG, WEBP — max 3MB</p>
            </div>
            @error('image')<div class="text-danger mt-1 mb-3" style="font-size:0.82rem;">{{ $message }}</div>@enderror

            {{-- <hr class="section-divider">
            <p class="section-title"><i class="fa-solid fa-images me-1"></i> Product Gallery Images (Multiple)</p>

            @php $gallery = $product->gallery_images ?? []; @endphp
            @if(count($gallery) > 0)
            <div class="row g-3 mb-3">
                @foreach($gallery as $img)
                    <div class="col-6 col-md-2 position-relative gallery-preview-card" style="min-height: 100px;">
                        <img src="{{ asset('storage/' . $img) }}" alt="gallery" class="img-fluid rounded border" style="height: 100px; width: 100%; object-fit: cover;">
                        <input type="hidden" name="existing_gallery[]" value="{{ $img }}">
                        <button type="button" class="btn btn-danger btn-xs position-absolute top-0 end-0 m-1 rounded-circle remove-gallery-btn" style="padding: 2px 6px; font-size: 0.75rem; border: none;">&times;</button>
                    </div>
                @endforeach
            </div>
            @endif --}}

            <div class="image-upload-zone mb-3" style="border: 2px dashed rgba(10, 144, 81, 0.25);">
                <input type="file" name="gallery[]" id="prod-gallery" accept="image/*" multiple>
                <i class="fa-solid fa-images fa-2x text-muted mb-2"></i>
                <p class="mb-1 fw-500 text-secondary">Click or drag additional gallery images here</p>
                <p class="text-muted" style="font-size:0.8rem;">Select multiple files at once — JPG, PNG, WEBP</p>
            </div>
            @error('gallery')<div class="text-danger mt-1" style="font-size:0.82rem;">{{ $message }}</div>@enderror

            <div class="d-flex align-items-center gap-3 mt-4 pt-3 border-top">
                <button type="submit" class="btn btn-save px-4">
                    <i class="fa-solid fa-floppy-disk me-2"></i> Update Product
                </button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
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

    // AJAX Cascading Sub-Category Dropdown
    const categorySelect    = document.getElementById('prod-category');
    const subCategorySelect = document.getElementById('prod-subcategory');
    const loadingSpinner    = document.getElementById('subcat-loading');
    const currentSubCatId   = {{ $product->sub_category_id ?? 'null' }};

    categorySelect.addEventListener('change', function () {
        const categoryId = this.value;
        subCategorySelect.innerHTML = '<option value="">-- Loading... --</option>';
        loadingSpinner.style.display = 'inline';

        if (!categoryId) {
            subCategorySelect.innerHTML = '<option value="">-- None --</option>';
            loadingSpinner.style.display = 'none';
            return;
        }

        fetch(`{{ url('admin/sub-categories/by-category') }}/${categoryId}`)
            .then(res => res.json())
            .then(data => {
                loadingSpinner.style.display = 'none';
                if (data.length === 0) {
                    subCategorySelect.innerHTML = '<option value="">-- No Sub-Categories Available --</option>';
                } else {
                    subCategorySelect.innerHTML = '<option value="">-- None (Optional) --</option>';
                    data.forEach(sub => {
                        const selected = sub.id == currentSubCatId ? 'selected' : '';
                        subCategorySelect.innerHTML += `<option value="${sub.id}" ${selected}>${sub.name}</option>`;
                    });
                }
            })
            .catch(() => {
                loadingSpinner.style.display = 'none';
                subCategorySelect.innerHTML = '<option value="">-- Error loading --</option>';
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
    let variantIndex = {{ count($product->variants) }};

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
            if (e.target.closest('.remove-var-img-btn')) {
                e.target.closest('.var-img-card').remove();
            }
        });
    }

    // Dynamic Gallery removal
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-gallery-btn')) {
            e.target.closest('.gallery-preview-card').remove();
        }
    });
</script>
@endsection

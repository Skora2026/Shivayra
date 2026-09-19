@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="fw-700 text-dark mb-1">Create Settings</h2>
                <p class="text-muted mb-0">Website Settings Management</p>
            </div>
            <div>
                <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-secondary rounded-pill px-4 fw-600">
                    <i class="fa-solid fa-arrow-left me-2"></i> {{ config('button.back') }}
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

                <form action="{{ route('admin.settings.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label for="site_name" class="form-label fw-600 text-dark">Site Name</label>
                            <input type="text" name="site_name" id="site_name" class="form-control rounded-3"
                                value="{{ old('site_name') }}">
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label fw-600 text-dark">Email</label>
                            <input type="email" name="email" id="email" class="form-control rounded-3"
                                value="{{ old('email') }}">
                        </div>

                        <div class="col-md-6">
                            <label for="phone" class="form-label fw-600 text-dark">Phone</label>
                            <input type="text" name="phone" id="phone" class="form-control rounded-3"
                                value="{{ old('phone') }}">
                        </div>

                        <div class="col-md-6">
                            <label for="alternate_phone" class="form-label fw-600 text-dark">Alternate Phone</label>
                            <input type="text" name="alternate_phone" id="alternate_phone" class="form-control rounded-3"
                                value="{{ old('alternate_phone') }}">
                        </div>

                        <div class="col-md-6">
                            <label for="whatsapp" class="form-label fw-600 text-dark">Whatsapp</label>
                            <input type="text" name="whatsapp" id="whatsapp" class="form-control rounded-3"
                                value="{{ old('whatsapp') }}">
                        </div>

                        <div class="col-md-6">
                            <label for="facebook" class="form-label fw-600 text-dark">Facebook Link</label>
                            <input type="url" name="facebook" id="facebook" class="form-control rounded-3"
                                value="{{ old('facebook') }}">
                        </div>

                        <div class="col-md-6">
                            <label for="instagram" class="form-label fw-600 text-dark">Instagram Link</label>
                            <input type="url" name="instagram" id="instagram" class="form-control rounded-3"
                                value="{{ old('instagram') }}">
                        </div>

                        <div class="col-md-6">
                            <label for="twitter" class="form-label fw-600 text-dark">Twitter Link</label>
                            <input type="url" name="twitter" id="twitter" class="form-control rounded-3"
                                value="{{ old('twitter') }}">
                        </div>

                        <div class="col-md-6">
                            <label for="youtube" class="form-label fw-600 text-dark">Youtube Link</label>
                            <input type="url" name="youtube" id="youtube" class="form-control rounded-3"
                                value="{{ old('youtube') }}">
                        </div>

                        <div class="col-md-6">
                            <label for="logo" class="form-label fw-600 text-dark">Logo</label>
                            <input type="file" name="logo" id="logo" class="form-control rounded-3">
                        </div>

                        <div class="col-md-6">
                            <label for="favicon" class="form-label fw-600 text-dark">Favicon</label>
                            <input type="file" name="favicon" id="favicon" class="form-control rounded-3">
                        </div>

                        <div class="col-12 mt-4"><h5 class="fw-700 text-dark border-bottom pb-2 mb-3">Tax & Shipping Settings</h5></div>

                        <div class="col-md-4">
                            <label class="form-label fw-600 text-dark">GST / Tax Percentage (%)</label>
                            <input type="number" step="0.01" min="0" max="100"
                                   name="tax_percent"
                                   class="form-control rounded-3"
                                   placeholder="5.00"
                                   value="{{ old('tax_percent', 5.00) }}">
                            <small class="text-muted">GST or Tax rate applied to orders (e.g. 5 for 5%)</small>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-600 text-dark">Standard Delivery Fee (₹)</label>
                            <input type="number" step="0.01" min="0"
                                   name="delivery_fee"
                                   class="form-control rounded-3"
                                   placeholder="60.00"
                                   value="{{ old('delivery_fee', 60.00) }}">
                            <small class="text-muted">Shipping charge for orders below threshold</small>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-600 text-dark">Min. Order for Free Delivery (₹)</label>
                            <input type="number" step="0.01" min="0"
                                   name="min_order_for_free_delivery"
                                   class="form-control rounded-3"
                                   placeholder="1000.00"
                                   value="{{ old('min_order_for_free_delivery', 1000.00) }}">
                            <small class="text-muted">Minimum subtotal required to get Free Shipping</small>
                        </div>

                        <div class="col-12 mt-4"><h5 class="fw-700 text-dark border-bottom pb-2 mb-3">Payment Method Settings</h5></div>

                        <div class="col-md-12">
                            <div class="form-check form-switch p-3 border rounded-3 bg-light d-flex align-items-center justify-content-between">
                                <div>
                                    <label class="form-check-label fw-700 text-dark me-3" for="is_cod_enabled" style="cursor: pointer;">
                                        Cash on Delivery (COD) Option
                                    </label>
                                    <div class="text-muted small">Enable or disable Cash on Delivery payment option at checkout</div>
                                </div>
                                <input class="form-check-input ms-0 me-2 fs-4" type="checkbox" role="switch" id="is_cod_enabled" name="is_cod_enabled" value="1" {{ old('is_cod_enabled', 1) ? 'checked' : '' }}>
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="address" class="form-label fw-600 text-dark">Address</label>
                            <textarea name="address" id="address" rows="4" class="form-control rounded-3">{{ old('address') }}</textarea>
                        </div>

                    </div>
                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <a href="{{ route('admin.settings.index') }}"
                            class="btn btn-light rounded-pill px-4">{{ config('button.cancel') }}</a>
                        <button type="submit" class="btn btn-custom-primary px-5">{{ config('button.submit') }}</button>
                    </div>

                </form>



            </div>
        </div>
    </div>
@endsection

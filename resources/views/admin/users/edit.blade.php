@extends('admin.layouts.app')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="fw-700 text-dark mb-1">{{ __('labels.edit_user') }}</h2>
            <p class="text-muted mb-0">{{ __('labels.users') }}</p>
        </div>
        <div>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary rounded-pill px-4 fw-600">
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
            <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6 mb-3">
                        <label for="first_name" class="form-label fw-600 text-dark">{{ __('labels.first_name') }}</label>
                        <input type="text" name="first_name" id="first_name" class="form-control rounded-3" value="{{ old('first_name', $user->first_name) }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="last_name" class="form-label fw-600 text-dark">{{ __('labels.last_name') }}</label>
                        <input type="text" name="last_name" id="last_name" class="form-control rounded-3" value="{{ old('last_name', $user->last_name) }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label fw-600 text-dark">{{ __('labels.email') }}</label>
                        <input type="email" name="email" id="email" class="form-control rounded-3" value="{{ old('email', $user->email) }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label fw-600 text-dark">{{ __('labels.password') }}</label>
                        <input type="password" name="password" id="password" class="form-control rounded-3" placeholder="••••••••">
                        <small class="form-text text-muted">{{ __('labels.password_help') }}</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-600 text-dark">{{ __('labels.role') }}</label>
                        <input type="text" class="form-control rounded-3 bg-light" value="{{ $userRole?->name ?? __('labels.user') }}" readonly>
                        <small class="form-text text-muted">Roles are fixed — there is a single store owner.</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="status" class="form-label fw-600 text-dark">{{ __('labels.status') }}</label>
                        <select name="status" id="status" class="form-select rounded-3" required>
                            <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>{{ __('labels.active') }}</option>
                            <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>{{ __('labels.inactive') }}</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-end mt-4">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-light rounded-pill px-4">{{ config('button.cancel') }}</a>
                    <button type="submit" class="btn btn-custom-primary px-5">{{ config('button.submit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

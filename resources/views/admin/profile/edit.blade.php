@extends('admin.layouts.app')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="fw-700 text-dark mb-1">{{ __('labels.edit_profile') }}</h2>
            <p class="text-muted mb-0">{{ __('labels.profile') }}</p>
        </div>
        <div>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary rounded-pill px-4 fw-600">
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
            <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
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
                        <label for="profile_pic" class="form-label fw-600 text-dark">{{ __('labels.profile_picture') }}</label>
                        <input type="file" name="profile_pic" id="profile_pic" class="form-control rounded-3" accept="image/*"
                               data-dropzone data-preview="#profile-pic-preview">
                        <small data-dropzone-hint style="display:none;"></small>
                        <img id="profile-pic-preview" alt="New profile picture preview"
                             class="rounded-circle border mt-2" style="display:none; width:60px; height:60px; object-fit:cover;">
                        @if($user->profile_pic)
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $user->profile_pic) }}" alt="Profile Picture" class="rounded-circle border" style="width: 60px; height: 60px; object-fit: cover;">
                            </div>
                        @endif
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label fw-600 text-dark">{{ __('labels.password') }}</label>
                        <input type="password" name="password" id="password" class="form-control rounded-3" placeholder="••••••••">
                        <small class="form-text text-muted">{{ __('labels.password_help') }}</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="password_confirmation" class="form-label fw-600 text-dark">{{ __('labels.password') }} (Confirmation)</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control rounded-3" placeholder="••••••••">
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-end mt-4">
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-light rounded-pill px-4">{{ config('button.cancel') }}</a>
                    <button type="submit" class="btn btn-custom-primary px-5">{{ config('button.submit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@extends('admin.layouts.app')

@section('content')
@can('user-list')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-700 text-dark mb-1">{{ __('labels.users') }}</h2>
        <p class="text-muted mb-0">{{ __('labels.all_users') }}</p>
    </div>
    @can('user-create')
    <div>
        <a href="{{ route('admin.users.create') }}" class="btn btn-custom-primary rounded-pill font-sm">
            <i class="fa-solid fa-user-plus me-2"></i>{{ config('button.add_user') }}
        </a>
    </div>
    @endcan
</div>

<div class="col-md-12 dashboard-card-main-col">
    <div class="card border-0 rounded-4 shadow-sm">
        <div class="card-body p-4">
            {{ $dataTable->table(['class' => 'table table-hover align-middle w-100']) }}
        </div>
    </div>
</div>
@endcan
@endsection

@section('scripts')
{{ $dataTable->scripts(attributes: ['type' => 'module']) }}
@endsection

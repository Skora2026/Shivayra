@extends('admin.layouts.app')

@section('content')
<div class="container-fluid p-0">

    <div class="d-flex align-items-center justify-content-between mb-4">

        <div>
            <h2 class="fw-700 mb-1">Banner Management</h2>
            <p class="text-muted mb-0">Manage Homepage Banners</p>
        </div>

        <a href="{{ route('admin.banners.create') }}"
            class="btn btn-custom-primary">

            <i class="fa-solid fa-plus me-2"></i>
            Add Banner
        </a>

    </div>

    <div class="card border-0 shadow-sm rounded-4">

        <div class="card-body">

            {{ $dataTable->table() }}

        </div>

    </div>

</div>
@endsection

@section('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
@endsection
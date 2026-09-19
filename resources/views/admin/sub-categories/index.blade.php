@extends('admin.layouts.app')

@section('styles')
<style>
    .page-header-card {
        background: linear-gradient(135deg, #7F3100 0%, #FE914B 100%);
        border-radius: 1.5rem;
        padding: 2rem 2.5rem;
        color: #fff;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }
    .page-header-card::before {
        content: '';
        position: absolute;
        top: -40px; right: -40px;
        width: 180px; height: 180px;
        border-radius: 50%;
        background: rgba(255,255,255,0.06);
    }
    .page-header-card::after {
        content: '';
        position: absolute;
        bottom: -60px; right: 80px;
        width: 250px; height: 250px;
        border-radius: 50%;
        background: rgba(255,255,255,0.04);
    }
    .dt-card {
        background: #fff;
        border-radius: 1.25rem;
        box-shadow: 0 4px 24px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    .dt-card .dt-card-body { padding: 1.5rem; }
    .search-bar-wrapper { margin-bottom: 1.25rem; }
    .search-bar-wrapper .dataTables_filter label {
        display: flex; align-items: center; gap: 0.5rem; font-weight: 500;
    }
    .search-bar-wrapper .dataTables_filter input {
        border: 1.5px solid #e0e0e0;
        border-radius: 2rem;
        padding: 0.45rem 1rem;
        font-size: 0.9rem;
        outline: none;
        transition: border-color 0.2s;
        min-width: 240px;
    }
    .search-bar-wrapper .dataTables_filter input:focus { border-color: #FE914B; }
    .yajra-table-custom-class table { width: 100% !important; }
    .yajra-table-custom-class thead th {
        background: #f8f9fa;
        font-weight: 700;
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #495057;
        border-bottom: 2px solid #e9ecef;
        padding: 0.85rem 1rem;
        white-space: nowrap;
    }
    .yajra-table-custom-class tbody tr { transition: background 0.15s; }
    .yajra-table-custom-class tbody tr:hover { background: #fff8f5; }
    .yajra-table-custom-class tbody td { padding: 0.85rem 1rem; vertical-align: middle; }
    .pagination-wrapper { margin-top: 1.25rem; display: flex; justify-content: flex-end; }
    .dataTables_info { color: #6c757d; font-size: 0.875rem; margin-top: 1rem; }
</style>
@endsection

@section('content')
<div class="page-header-card">
    <div class="d-flex justify-content-between align-items-center position-relative" style="z-index:1;">
        <div>
            <h1 class="fw-800 mb-1" style="font-size:1.75rem;">
                <i class="fa-solid fa-sitemap me-2 opacity-75"></i>
                {{ __('labels.all_sub_categories') }}
            </h1>
            <p class="mb-0 opacity-75">Manage product sub-categories</p>
        </div>
        <a href="{{ route('admin.sub-categories.create') }}" 
           class="btn btn-light fw-600 rounded-pill px-4 py-2 d-flex align-items-center gap-2"
           style="color:#7F3100;">
            <i class="fa-solid fa-plus"></i>
            {{ __('labels.create_new_sub_category') }}
        </a>
    </div>
</div>

<div class="dt-card">
    <div class="dt-card-body">
        {{-- Custom Filter --}}
        <div class="row mb-4 align-items-center">
            <div class="col-md-3">
                <label class="form-label fw-600 mb-1" for="filter-category">Filter by Category</label>
                <select id="filter-category" class="form-select rounded-pill">
                    <option value="">-- All Categories --</option>
                    @foreach(\App\Models\Category::all() as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{ $dataTable->table(['class' => 'table table-hover align-middle']) }}
    </div>
</div>
@endsection

@section('scripts')
{{ $dataTable->scripts(attributes: ['type' => 'module']) }}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipEls = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipEls.map(el => new bootstrap.Tooltip(el));

        // Redraw Yajra DataTable on filter category change
        const filterCategory = document.getElementById('filter-category');
        if (filterCategory) {
            filterCategory.addEventListener('change', function() {
                if (window.LaravelDataTables && window.LaravelDataTables["sub-categories-table"]) {
                    window.LaravelDataTables["sub-categories-table"].draw();
                }
            });
        }
    });
</script>
@endsection

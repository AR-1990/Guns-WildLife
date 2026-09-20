@extends('admin.layouts.main')

@section('title', 'Products')
@section('pageHeading', 'Products')

@push('styles')
<style>
    .products-dashboard .products-filter-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: nowrap;
    }

    .products-dashboard .products-filter-actions .themeBtn,
    .products-dashboard .products-filter-actions .themeBtn--white {
        flex: 1 1 0;
        min-width: 0;
    }

    .products-dashboard .products-summary-card {
        min-height: 116px;
    }

    .products-dashboard .products-summary-card .metric-card__value {
        margin-bottom: 0;
    }

    @media (max-width: 991.98px) {
        .products-dashboard .products-filter-actions {
            flex-wrap: wrap;
        }

        .products-dashboard .products-filter-actions .themeBtn,
        .products-dashboard .products-filter-actions .themeBtn--white {
            flex-basis: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="products-dashboard">
    <div class="dashboard-content__head">
        <h2 class="boldHeading">Products</h2>

        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route(auth()->user()->dashboardRouteName()) }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Products</li>
            </ul>
        </nav>
    </div>
    
    <div class="dashboard-card">
        <ul class="nav nav-tabs products-tabs" id="productsTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab !== 'category' ? 'active' : '' }}" id="products-tab" data-bs-toggle="tab"
                    data-bs-target="#products-pane" type="button" role="tab" aria-controls="products-pane"
                    aria-selected="{{ $activeTab !== 'category' ? 'true' : 'false' }}">
                    Product
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'category' ? 'active' : '' }}" id="category-tab" data-bs-toggle="tab" data-bs-target="#category-pane" type="button"
                    role="tab" aria-controls="category" aria-selected="{{ $activeTab === 'category' ? 'true' : 'false' }}">
                    Category
                </button>
            </li>
        </ul>

        <div class="tab-content" id="productsTabContent">
            <div class="tab-pane fade {{ $activeTab !== 'category' ? 'show active' : '' }}" id="products-pane" role="tabpanel" aria-labelledby="products-tab">
                <div class="products-table-section">
                    <div class="dashboard-card mb-4 products-filter-card">
                        <form action="{{ route('admin.products.index') }}" method="GET">
                            <input type="hidden" name="tab" value="products">
                            <div class="row g-3 align-items-end">
                                <div class="col-lg-2 col-md-6">
                                    <label class="form-label">Product</label>
                                    <input type="text" name="search" class="form-control" value="{{ $productFilters['search'] }}" placeholder="Search product or code">
                                </div>
                                <div class="col-lg-2 col-md-6">
                                    <label class="form-label">Category</label>
                                    <select class="form-select js-select2" name="filter_category_id">
                                        <option value="">All Categories</option>
                                        @foreach ($productFilterCategories as $category)
                                            <option value="{{ $category->id }}" {{ (string) $productFilters['filter_category_id'] === (string) $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-6">
                                    <label class="form-label">Status</label>
                                    <select class="form-select js-select2" name="filter_status">
                                        <option value="">All Statuses</option>
                                        <option value="active" {{ $productFilters['filter_status'] === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ $productFilters['filter_status'] === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-6">
                                    <label class="form-label">From Date</label>
                                    <input type="date" name="stats_from_date" class="form-control" value="{{ $productFilters['stats_from_date'] }}">
                                </div>
                                <div class="col-lg-2 col-md-6">
                                    <label class="form-label">To Date</label>
                                    <input type="date" name="stats_to_date" class="form-control" value="{{ $productFilters['stats_to_date'] }}">
                                </div>
                                <div class="col-lg-2 col-md-6">
                                    <div class="products-filter-actions">
                                        <button type="submit" class="themeBtn">Search</button>
                                        <a href="{{ route('admin.products.index', ['tab' => 'products']) }}" class="themeBtn themeBtn--white text-center">Reset</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-lg-4 col-md-4">
                            <div class="account-card account-card--yellow h-100 products-summary-card">
                                <div class="account-card__content">
                                    <h3 class="account-card__title">Current Stock</h3>
                                    <p class="metric-card__value">{{ $summary['current']['total_stock'] }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-4">
                            <div class="account-card account-card--yellow h-100 products-summary-card">
                                <div class="account-card__content">
                                    <h3 class="account-card__title">Current Stock Value</h3>
                                    <p class="metric-card__value">Rs. {{ number_format((float) $summary['current']['total_stock_value'], 2) }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-4">
                            <div class="account-card account-card--yellow h-100 products-summary-card">
                                <div class="account-card__content">
                                    <h3 class="account-card__title">Current Total Sell</h3>
                                    <p class="metric-card__value">{{ $summary['current']['total_sell'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($hasStatsRange)
                        <div class="row g-3 mb-4">
                            <div class="col-lg-4 col-md-4">
                                <div class="account-card account-card--yellow h-100 products-summary-card">
                                    <div class="account-card__content">
                                        <h3 class="account-card__title">Date Range Stock</h3>
                                        <p class="metric-card__value">{{ $summary['range']['total_stock'] }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4">
                                <div class="account-card account-card--yellow h-100 products-summary-card">
                                    <div class="account-card__content">
                                        <h3 class="account-card__title">Date Range Stock Value</h3>
                                        <p class="metric-card__value">Rs. {{ number_format((float) $summary['range']['total_stock_value'], 2) }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4">
                                <div class="account-card account-card--yellow h-100 products-summary-card">
                                    <div class="account-card__content">
                                        <h3 class="account-card__title">Date Range Total Sell</h3>
                                        <p class="metric-card__value">{{ $summary['range']['total_sell'] }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="products-table__header">
                        <div class="products-table__header-left">
                            @if (auth()->user()?->isAdmin())
                                <a href="{{ route('admin.products.create') }}" class="btn btn-outline-secondary ms-2 filterBtn">
                                    <i class="fa-solid fa-plus me-1"></i> Add Product
                                </a>
                            @endif
                        </div>
                        <span class="products-table__header-right">Total Products: {{ $productCount }}</span>
                    </div>
                    <div class="table-responsive">
                        <table id="productsTable" data-laravel-pagination="true" class="table table-hover tables">
                            <thead>
                                <tr>
                                    <th>Product Code</th>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Unit</th>
                                    <th>Stock</th>
                                    <th>Stock Alert</th>
                                    <th>Purchase Price</th>
                                    <th>Sell Price</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($products as $product)
                                    @php($currentStock = $product->is_serialized ? $product->availableUnits->count() : (int) $product->stock_quantity)
                                    @php($stockThreshold = max(1, (int) ($product->min_stock_level ?: 5)))
                                    <tr>
                                        <td>{{ $product->product_code }}</td>
                                        <td>{{ $product->name }}</td>
                                        <td>{{ $product->category?->name }}</td>
                                        <td>{{ $product->unit }}</td>
                                        <td>{{ $currentStock }}</td>
                                        <td>
                                            <span class="badge {{ $currentStock <= 0 ? 'inActive' : ($currentStock <= $stockThreshold ? 'bg-warning text-dark' : 'active') }}">
                                                {{ $currentStock <= 0 ? 'Out of Stock' : ($currentStock <= $stockThreshold ? 'Low Stock' : 'In Stock') }}
                                            </span>
                                        </td>
                                        <td>Rs. {{ number_format((float) $product->purchase_price, 2) }}</td>
                                        <td>Rs. {{ number_format((float) $product->selling_price, 2) }}</td>
                                        <td>
                                            <span class="badge {{ $product->status === 'active' ? 'active' : 'inActive' }}">
                                                {{ ucfirst($product->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('admin.products.restock.create', $product) }}" class="btn btn-sm btn-outline-primary account-icon-btn" title="Restock Product" aria-label="Restock Product">
                                                    <i class="fas fa-plus"></i>
                                                </a>
                                                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline-secondary account-icon-btn" title="Edit Product" aria-label="Edit Product">
                                                    <i class="fas fa-pen"></i>
                                                </a>
                                                <form action="{{ route('admin.products.toggle', $product) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm {{ $product->status === 'active' ? 'btn-outline-danger' : 'btn-outline-success' }} account-icon-btn" title="{{ $product->status === 'active' ? 'Deactivate Product' : 'Activate Product' }}" aria-label="{{ $product->status === 'active' ? 'Deactivate Product' : 'Activate Product' }}">
                                                        <i class="fas {{ $product->status === 'active' ? 'fa-thumbs-down' : 'fa-thumbs-up' }}"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger account-icon-btn" title="Delete Product" aria-label="Delete Product">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">No products found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $products->appends(['tab' => 'products', 'category' => request('category')])->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>

            <div class="tab-pane fade {{ $activeTab === 'category' ? 'show active' : '' }}" id="category-pane" role="tabpanel" aria-labelledby="category-tab">
                <div class="products-table-section">
                    <div class="dashboard-card mb-4">
                        <form action="{{ $editingCategory ? route('admin.products.categories.update', $editingCategory) : route('admin.products.categories.store') }}"
                            method="POST" class="row g-3 align-items-end">
                            @csrf
                            @if ($editingCategory)
                                @method('PUT')
                            @endif

                            <div class="col-md-5">
                                <label class="form-label">Category Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control"
                                    value="{{ old('name', $editingCategory?->name) }}" placeholder="Enter category name" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="is_active" class="form-select">
                                    <option value="1" {{ old('is_active', $editingCategory?->is_active ?? 1) == 1 ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('is_active', $editingCategory?->is_active) == 0 ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="themeBtn themeBtn--primary">
                                        {{ $editingCategory ? 'Update Category' : 'Add Category' }}
                                    </button>
                                    @if ($editingCategory)
                                        <a href="{{ route('admin.products.index', ['tab' => 'category']) }}" class="themeBtn themeBtn--gray">
                                            Cancel
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="products-table__header">
                        <div class="products-table__header-left">
                            <span class="filterBtn">Database Categories</span>
                        </div>
                        <span class="products-table__header-right">Total Categories: {{ $categoryCount }}</span>
                    </div>
                    <div class="table-responsive">
                        <table id="categoryTable" data-laravel-pagination="true" class="table table-hover tables">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Total Products</th>
                                    <th>Active Products</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($categories as $category)
                                    <tr>
                                        <td>{{ $category->name }}</td>
                                        <td>{{ $category->products_count }}</td>
                                        <td>{{ $category->active_count }}</td>
                                        <td>
                                            <span class="badge {{ $category->is_active ? 'active' : 'inActive' }}">
                                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('admin.products.index', ['tab' => 'category', 'category' => $category->id]) }}"
                                                    class="btn btn-sm btn-outline-secondary account-icon-btn" title="Edit Category" aria-label="Edit Category">
                                                    <i class="fas fa-pen"></i>
                                                </a>
                                                <form action="{{ route('admin.products.categories.toggle', $category) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                        class="btn btn-sm {{ $category->is_active ? 'btn-outline-danger' : 'btn-outline-success' }} account-icon-btn"
                                                        title="{{ $category->is_active ? 'Deactivate Category' : 'Activate Category' }}"
                                                        aria-label="{{ $category->is_active ? 'Deactivate Category' : 'Activate Category' }}">
                                                        <i class="fas {{ $category->is_active ? 'fa-thumbs-down' : 'fa-thumbs-up' }}"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.products.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger account-icon-btn" title="Delete Category" aria-label="Delete Category">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No categories found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $categories->appends(['tab' => 'category', 'category' => request('category')])->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('admin.layouts.main')

@section('title', 'Dashboard')
@section('pageHeading', 'Dashboard')

@section('content')
<div class="inventory-dashboard">
    <div class="dashboard-content__head">
        <h2 class="boldHeading">Welcome to Guns & Wildlife</h2>
        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
            </ul>
        </nav>
    </div>

    <div class="dashboard-card mb-4">
        <div class="welcome-banner mb-4">
            <div class="row g-3 align-items-center">
                <div class="col-lg-8">
                    <h3 class="mb-2">{{ $isSalesmanView ? 'My Sales Dashboard' : 'Admin Overview' }}</h3>
                    <p class="mb-0 text-muted">
                        @if ($isSalesmanView)
                            Track your sales, daily totals, monthly revenue, and pending drafts at a glance.
                        @else
                            Monitor today's sales, monthly profit, expenses, low stock, and contacts from one place.
                        @endif
                    </p>
                </div>
                <div class="col-lg-4">
                    <div class="row g-2">
                        @foreach ($quickStats as $stat)
                            <div class="col-6">
                                <div class="dashboard-card h-100">
                                    <h6 class="mb-1">{{ $stat['label'] }}</h6>
                                    <div class="fw-bold mb-2">{{ $stat['value'] }}</div>
                                    <a href="{{ $stat['link'] }}" class="btn btn-sm btn-outline-primary">{{ $stat['link_label'] }}</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        @if (!$isSalesmanView)
            <div class="row g-3">
                <div class="col-lg-3 col-md-6">
                    <div class="metric-card">
                        <div class="metric-card__icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="metric-card__content">
                            <h3 class="metric-card__title">Total Products</h3>
                            <p class="metric-card__value">{{ $summary['products'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="metric-card">
                        <div class="metric-card__icon">
                            <i class="fas fa-barcode"></i>
                        </div>
                        <div class="metric-card__content">
                            <h3 class="metric-card__title">Serialized Available</h3>
                            <p class="metric-card__value">{{ $summary['serialized_available'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="metric-card">
                        <div class="metric-card__icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="metric-card__content">
                            <h3 class="metric-card__title">Low Stock</h3>
                            <p class="metric-card__value">{{ $summary['low_stock_count'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="metric-card">
                        <div class="metric-card__icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="metric-card__content">
                            <h3 class="metric-card__title">Completed Sales</h3>
                            <p class="metric-card__value">{{ $summary['completed_sales'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="metric-card">
                        <div class="metric-card__icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="metric-card__content">
                            <h3 class="metric-card__title">Salesmen</h3>
                            <p class="metric-card__value">{{ $summary['salesmen'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="metric-card">
                        <div class="metric-card__icon">
                            <i class="fas fa-sack-dollar"></i>
                        </div>
                        <div class="metric-card__content">
                            <h3 class="metric-card__title">Monthly Revenue</h3>
                            <p class="metric-card__value">{{ number_format((float) $summary['monthly_revenue'], 2) }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="metric-card">
                        <div class="metric-card__icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="metric-card__content">
                            <h3 class="metric-card__title">Contacts</h3>
                            <p class="metric-card__value">{{ $summary['contacts'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="row g-3">
                <div class="col-lg-3 col-md-6">
                    <div class="metric-card">
                        <div class="metric-card__icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="metric-card__content">
                            <h3 class="metric-card__title">Today Sales</h3>
                            <p class="metric-card__value">{{ $summary['today_sales_count'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="metric-card">
                        <div class="metric-card__icon">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <div class="metric-card__content">
                            <h3 class="metric-card__title">Draft Sales</h3>
                            <p class="metric-card__value">{{ $summary['draft_sales'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="metric-card">
                        <div class="metric-card__icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="metric-card__content">
                            <h3 class="metric-card__title">Low Stock</h3>
                            <p class="metric-card__value">{{ $summary['low_stock_count'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="metric-card">
                        <div class="metric-card__icon">
                            <i class="fas fa-ban"></i>
                        </div>
                        <div class="metric-card__content">
                            <h3 class="metric-card__title">Out of Stock</h3>
                            <p class="metric-card__value">{{ $summary['out_of_stock_count'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if (!$isSalesmanView)
        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="dashboard-card h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mb-0">Monthly Business Snapshot</h4>
                        <a href="{{ route('admin.reports.revenue') }}" class="themeBtn themeBtn--white">Open Reports</a>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="dashboard-card h-100">
                                <h6>Today Sales</h6>
                                <div class="fw-bold mb-1">{{ $summary['today_sales_count'] }}</div>
                                <small class="text-muted">{{ number_format((float) $summary['today_sales_total'], 2) }}</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="dashboard-card h-100">
                                <h6>Draft Sales</h6>
                                <div class="fw-bold mb-1">{{ $summary['draft_sales'] }}</div>
                                <small class="text-muted">Pending completion</small>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="dashboard-card h-100">
                                <h6>Expense</h6>
                                <div class="fw-bold">{{ number_format((float) $summary['monthly_expense'], 2) }}</div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="dashboard-card h-100">
                                <h6>Admin Profit</h6>
                                <div class="fw-bold">{{ number_format((float) $summary['monthly_admin_profit'], 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="dashboard-card h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mb-0">Low Stock Alert</h4>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline-primary">Manage</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Stock</th>
                                    <th>Min</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($lowStockProducts as $product)
                                    <tr>
                                        <td>
                                            {{ $product->name }}
                                            <br><small class="text-muted">{{ $product->category?->name ?: 'N/A' }}</small>
                                        </td>
                                        <td>{{ $product->current_stock }}</td>
                                        <td>{{ $product->stock_threshold }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">No low stock product found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="dashboard-card h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mb-0">Low Stock Alert</h4>
                        <a href="{{ route('admin.sales.index') }}" class="btn btn-sm btn-outline-primary">Open Sales</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Stock</th>
                                    <th>Min</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($lowStockProducts as $product)
                                    <tr>
                                        <td>
                                            {{ $product->name }}
                                            <br><small class="text-muted">{{ $product->category?->name ?: 'N/A' }}</small>
                                        </td>
                                        <td>{{ $product->current_stock }}</td>
                                        <td>{{ $product->stock_threshold }}</td>
                                        <td>
                                            <span class="badge {{ $product->current_stock <= 0 ? 'inActive' : 'active' }}">
                                                {{ $product->current_stock <= 0 ? 'Out of Stock' : 'Low Stock' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No low stock product found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="dashboard-card h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mb-0">Sales Snapshot</h4>
                        <a href="{{ route('admin.reports.sales') }}" class="btn btn-sm btn-outline-primary">My Report</a>
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="dashboard-card h-100">
                                <h6>Monthly Revenue</h6>
                                <div class="fw-bold mb-1">{{ number_format((float) $summary['monthly_revenue'], 2) }}</div>
                                <small class="text-muted">Completed sales only</small>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="dashboard-card h-100">
                                <h6>Total Revenue</h6>
                                <div class="fw-bold mb-1">{{ number_format((float) $summary['total_revenue'], 2) }}</div>
                                <small class="text-muted">Your all completed sales</small>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="dashboard-card h-100">
                                <h6>Drafts Pending</h6>
                                <div class="fw-bold mb-1">{{ $summary['draft_sales'] }}</div>
                                <small class="text-muted">Complete whenever customer confirms</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="dashboard-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">Recent Sales</h4>
                    <a href="{{ route('admin.sales.index') }}" class="themeBtn themeBtn--white">All Sales</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover tables">
                        <thead>
                            <tr>
                                <th>Receipt</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentSales as $sale)
                                <tr>
                                    <td>{{ $sale->receipt_no }}</td>
                                    <td>{{ $sale->customer_name }}</td>
                                    <td>{{ $sale->sale_date?->format('d-M-Y') }}</td>
                                    <td>
                                        <span class="badge {{ $sale->status === 'completed' ? 'active' : 'inActive' }}">
                                            {{ ucfirst($sale->status) }}
                                        </span>
                                    </td>
                                    <td>{{ number_format((float) $sale->grand_total, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No sales found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="dashboard-card h-100 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">Top Products This Month</h4>
                    <a href="{{ $isSalesmanView ? route('admin.reports.sales') : route('admin.reports.revenue') }}" class="btn btn-sm btn-outline-primary">{{ $isSalesmanView ? 'My Sales Report' : 'Revenue Report' }}</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($topProducts as $product)
                                <tr>
                                    <td>{{ $product['product_name'] }}</td>
                                    <td>{{ $product['qty'] }}</td>
                                    <td>{{ number_format((float) $product['revenue'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">No monthly sales found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

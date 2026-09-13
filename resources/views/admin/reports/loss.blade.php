@extends('admin.layouts.main')

@section('title', 'Loss Report')
@section('pageHeading', 'Loss Report')

@section('content')
<div class="sales-dashboard">
    <div class="dashboard-content__head">
        <h2 class="boldHeading">Loss Report</h2>
        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route(auth()->user()->dashboardRouteName()) }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Loss Report</li>
            </ul>
        </nav>
    </div>

    <div class="dashboard-card mb-4">
        <form action="{{ route('admin.reports.loss') }}" method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">From Date</label>
                    <input type="date" class="form-control" name="from_date" value="{{ $filters['from_date'] }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">To Date</label>
                    <input type="date" class="form-control" name="to_date" value="{{ $filters['to_date'] }}">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="themeBtn w-100">Search</button>
                    <a href="{{ route('admin.reports.loss') }}" class="themeBtn themeBtn--white w-100 text-center">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6"><div class="account-card account-card--yellow"><div class="account-card__content"><h3 class="account-card__title">Affected Sales</h3><p class="metric-card__value">{{ $summary['sales'] }}</p></div></div></div>
        <div class="col-lg-3 col-md-6"><div class="account-card account-card--teal"><div class="account-card__content"><h3 class="account-card__title">Loss Items</h3><p class="metric-card__value">{{ $summary['items'] }}</p></div></div></div>
        <div class="col-lg-3 col-md-6"><div class="account-card account-card--orange"><div class="account-card__content"><h3 class="account-card__title">Total Loss</h3><p class="metric-card__value">{{ number_format((float) $summary['loss_total'], 2) }}</p></div></div></div>
        <div class="col-lg-3 col-md-6"><div class="account-card account-card--red"><div class="account-card__content"><h3 class="account-card__title">Avg Loss</h3><p class="metric-card__value">{{ number_format((float) $summary['avg_loss'], 2) }}</p></div></div></div>
    </div>

    <div class="dashboard-card">
        <div class="sales-table-section">
            <div class="sales-table__header">
                <span class="fw-semibold">Loss Details</span>
                <a href="{{ route('admin.reports.loss.csv', request()->only('from_date', 'to_date')) }}" class="themeBtn themeBtn--green">
                    <i class="fas fa-file-csv me-1"></i> Export CSV
                </a>
            </div>
            <div class="table-responsive">
                <table id="lossTable" class="table table-hover tables">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Receipt</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Sale Total</th>
                            <th>Loss</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($lossItems as $item)
                            <tr>
                                <td>{{ $lossItems->firstItem() + $loop->index }}</td>
                                <td>{{ $item['receipt_no'] }}</td>
                                <td>{{ $item['sale_date'] }}</td>
                                <td>{{ $item['customer_name'] }}</td>
                                <td>{{ $item['product_name'] }}</td>
                                <td>{{ $item['quantity'] }}</td>
                                <td>{{ number_format((float) $item['sale_total'], 2) }}</td>
                                <td>{{ number_format(abs((float) $item['admin_profit']), 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center">No loss record found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $lossItems->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection

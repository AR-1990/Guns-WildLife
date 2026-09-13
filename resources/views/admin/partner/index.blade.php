@extends('admin.layouts.main')

@section('title', 'My Payouts')
@section('pageHeading', 'My Payouts')

@section('content')
<div class="accounts-dashboard">
    <div class="dashboard-content__head">
        <h2 class="boldHeading">{{ auth()->user()->company_name ?: auth()->user()->name }} Payout Report</h2>
        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route(auth()->user()->dashboardRouteName()) }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">My Payouts</li>
            </ul>
        </nav>
    </div>

    <div class="dashboard-card">
        <div class="row g-3 mb-4">
            <div class="col-lg-4 col-md-6">
                <div class="account-card account-card--teal">
                    <div class="account-card__content">
                        <h3 class="account-card__title">Total Paid To Me</h3>
                        <p class="metric-card__value">Rs. {{ number_format($summary['total_paid'], 2) }}</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="account-card account-card--yellow">
                    <div class="account-card__content">
                        <h3 class="account-card__title">Number Of Payouts</h3>
                        <p class="metric-card__value">{{ $summary['payout_count'] }}</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="account-card account-card--green">
                    <div class="account-card__content">
                        <h3 class="account-card__title">This Month Payouts</h3>
                        <p class="metric-card__value">Rs. {{ number_format($summary['this_month_paid'], 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="financial-overview">
            <h3 class="financial-overview__title">Payout History</h3>
            <div class="table-responsive">
                <table class="table table-hover tables">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Notes / Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($withdrawals as $w)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $w->withdrawal_date?->format('d M Y') ?: '-' }}</td>
                                <td><strong>Rs. {{ number_format((float) $w->amount, 2) }}</strong></td>
                                <td>
                                    @if (!empty($w->payment_method))
                                        <span class="badge bg-secondary">{{ ucfirst($w->payment_method) }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-muted small">{{ $w->notes ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    No payouts recorded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('admin.layouts.main')

@section('title', 'Cashbook')
@section('pageHeading', 'Cashbook')

@section('content')
<div class="cashbook-dashboard">
    <div class="dashboard-content__head">
        <h2 class="boldHeading">Cashbook</h2>

        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item">Accounts</li>
                <li class="breadcrumb-item active" aria-current="page">Cashbook</li>
            </ul>
        </nav>
    </div>

    <div class="dashboard-card">
        <div class="row g-3">
            <div class="col-lg-3 col-md-6">
                <div class="metric-card">
                    <div class="metric-card__icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="metric-card__content">
                        <h3 class="metric-card__title">Opening Balance</h3>
                        <p class="metric-card__value">{{ number_format((float) $openingBalance, 2) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="metric-card">
                    <div class="metric-card__icon metric-card__icon--green">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                    <div class="metric-card__content">
                        <h3 class="metric-card__title">Total Receipts (Debit)</h3>
                        <p class="metric-card__value text-success">+{{ number_format((float) $summary['receipts'], 2) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="metric-card">
                    <div class="metric-card__icon metric-card__icon--red">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                    <div class="metric-card__content">
                        <h3 class="metric-card__title">Total Payments (Credit)</h3>
                        <p class="metric-card__value text-danger">-{{ number_format((float) $summary['payments'], 2) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="metric-card">
                    <div class="metric-card__icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="metric-card__content">
                        <h3 class="metric-card__title">Closing Balance</h3>
                        <p class="metric-card__value">{{ number_format((float) $summary['closing'], 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="cashbook-filter-section">
            <form action="{{ route('admin.accounts.cashbook') }}" method="GET" class="cashbook-filter">
                <div class="cashbook-filter__dates">
                    <input type="date" class="form-control" name="from_date" value="{{ $filters['from_date'] }}">
                    <span class="cashbook-filter__to">to</span>
                    <input type="date" class="form-control" name="to_date" value="{{ $filters['to_date'] }}">
                    <button type="submit" class="themeBtn">
                        <i class="fas fa-search"></i> Apply
                    </button>
                    <a href="{{ route('admin.accounts.cashbook') }}" class="themeBtn themeBtn--white">
                        Reset
                    </a>
                </div>
                <a href="{{ route('admin.accounts.cashbook.csv', request()->only('from_date', 'to_date')) }}" class="themeBtn themeBtn--green">
                    <i class="fas fa-file-csv me-1"></i> Export CSV
                </a>
            </form>
        </div>

        <div class="transaction-ledger">
            <div class="transaction-ledger__header">
                <h3 class="transaction-ledger__title">Transaction Ledger</h3>
                <div class="transaction-ledger__controls">
                    <span class="badge blue">{{ $transactions->total() }} Records</span>
                </div>
            </div>
            <div class="table-responsive">
                <table id="cashbookTable" data-laravel-pagination="true" class="table table-hover tables">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Voucher #</th>
                            <th>Description</th>
                            <th>Method</th>
                            <th>Debit (In)</th>
                            <th>Credit (Out)</th>
                            <th>Type</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction['display_date'] ?: '-' }}</td>
                                <td>{{ $transaction['voucher'] }}</td>
                                <td>{{ $transaction['description'] }}</td>
                                <td>{{ $transaction['method'] }}</td>
                                <td class="debit-amount">
                                    {{ $transaction['debit'] > 0 ? number_format((float) $transaction['debit'], 2) : '-' }}
                                </td>
                                <td class="credit-amount">
                                    {{ $transaction['credit'] > 0 ? number_format((float) $transaction['credit'], 2) : '-' }}
                                </td>
                                <td>
                                    <span class="badge {{ $transaction['type'] === 'Sale' ? 'blue' : 'active' }}">
                                        {{ $transaction['type'] }}
                                    </span>
                                </td>
                                <td>{{ number_format((float) $transaction['balance'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No cashbook transactions found for selected dates.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="period-totals">
                            <td colspan="4"><strong>Period Totals</strong></td>
                            <td class="text-success"><strong>{{ number_format((float) $summary['receipts'], 2) }}</strong></td>
                            <td class="text-danger"><strong>{{ number_format((float) $summary['payments'], 2) }}</strong></td>
                            <td></td>
                            <td><strong>{{ number_format((float) $summary['closing'], 2) }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="mt-3">
                {{ $transactions->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection

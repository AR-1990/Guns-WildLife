@extends('admin.layouts.main')

@section('title', 'Partner Assignments')
@section('pageHeading', 'Partner Assignments')

@section('content')
<div class="accounts-dashboard">
    <div class="dashboard-content__head">
        <h2 class="boldHeading">Partner Product Assignments</h2>

        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.accounts.users') }}">Users</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $partner->name }}</li>
            </ul>
        </nav>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="account-card account-card--orange">
                <div class="account-card__content">
                    <h3 class="account-card__title">Actual Share</h3>
                    <p class="metric-card__value">{{ number_format((float) $summary['actual'], 2) }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="account-card account-card--teal">
                <div class="account-card__content">
                    <h3 class="account-card__title">Profit Earned</h3>
                    <p class="metric-card__value">{{ number_format((float) $summary['earned'], 2) }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="account-card account-card--red">
                <div class="account-card__content">
                    <h3 class="account-card__title">Deductions</h3>
                    <p class="metric-card__value">{{ number_format((float) $summary['deductions'], 2) }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="account-card account-card--yellow">
                <div class="account-card__content">
                    <h3 class="account-card__title">Available</h3>
                    <p class="metric-card__value">{{ number_format((float) $summary['available'], 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="account-ledger mb-4">
        <div class="account-ledger__header">
            <h3 class="account-ledger__title">Investment History</h3>
            <div class="account-ledger__controls">
                <span class="badge blue">{{ $entries->count() }} Records</span>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover tables account-data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Total Amount</th>
                        <th>Products</th>
                        <th>Notes</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($entries as $entry)
                        <tr>
                            <td>{{ $entry->investment_date?->format('d-M-Y') }}</td>
                            <td>{{ number_format((float) $entry->total_amount, 2) }}</td>
                            <td>
                                {{ $entry->items->map(fn ($item) => ($item->product?->name ?: 'Product') . ' (' . number_format((float) $item->amount, 2) . ')')->implode(', ') }}
                            </td>
                            <td>{{ $entry->notes ?: 'N/A' }}</td>
                            <td>
                                <div class="account-action-links">
                                    <a href="{{ route('admin.accounts.users.assignments.edit', ['user' => $partner, 'entry' => $entry->id]) }}" class="btn btn-sm btn-outline-primary account-icon-btn" title="Edit Investment" aria-label="Edit Investment">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form action="{{ route('admin.accounts.users.assignments.destroy', [$partner, $entry]) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this investment?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger account-icon-btn" title="Delete Investment" aria-label="Delete Investment">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No investment history found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="account-ledger">
        <div class="account-ledger__header">
            <h3 class="account-ledger__title">Deduction Log</h3>
            <div class="account-ledger__controls">
                <span class="badge blue">{{ $deductionLogs->count() }} Records</span>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover tables account-data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Reason</th>
                        <th>Amount</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($deductionLogs as $log)
                        <tr>
                            <td>{{ $log['date'] ? \Illuminate\Support\Carbon::parse($log['date'])->format('d-M-Y') : 'N/A' }}</td>
                            <td>{{ ucfirst($log['type']) }}</td>
                            <td>{{ $log['label'] }}</td>
                            <td>{{ number_format((float) $log['amount'], 2) }}</td>
                            <td>{{ $log['notes'] ?: 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No deduction log found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

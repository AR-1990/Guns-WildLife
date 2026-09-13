@extends('admin.layouts.main')

@section('title', 'Khatas')
@section('pageHeading', 'Khatas')

@section('content')
<div class="accounts-dashboard">
    <div class="dashboard-content__head">
        <h2 class="boldHeading">Khatas</h2>

        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item">Accounts</li>
                <li class="breadcrumb-item active" aria-current="page">Khatas</li>
            </ul>
        </nav>
    </div>

    <div class="dashboard-card mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h4 class="mb-0">Create Khata</h4>
                <p class="account-toolbar__text mb-0">Aik khata me multiple partners, unki amount, on hold aur product allocation manage karein.</p>
            </div>
        </div>

        <form action="{{ route('admin.accounts.khatas.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-lg-4">
                    <label class="form-label">Khata Name</label>
                    <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                </div>
                <div class="col-lg-8">
                    <label class="form-label">Notes</label>
                    <input type="text" class="form-control" name="notes" value="{{ old('notes') }}" placeholder="Optional note">
                </div>
                <div class="col-12">
                    <button type="submit" class="themeBtn">Create Khata</button>
                </div>
            </div>
        </form>
    </div>

    <div class="account-ledger">
        <div class="account-ledger__header">
            <h3 class="account-ledger__title">Khata List</h3>
            <div class="account-ledger__controls">
                <span class="badge blue">{{ $khataRows->count() }} Records</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover tables account-data-table">
                <thead>
                    <tr>
                        <th>Khata</th>
                        <th>Members</th>
                        <th>Entries</th>
                        <th>Total Amount</th>
                        <th>On Hold</th>
                        <th>Active</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($khataRows as $row)
                        <tr>
                            <td>
                                <strong>{{ $row['khata']->name }}</strong>
                                <div class="account-data-table__subtext">{{ $row['khata']->notes ?: 'No notes' }}</div>
                            </td>
                            <td>{{ $row['members_count'] }}</td>
                            <td>{{ $row['entries_count'] }}</td>
                            <td>{{ number_format((float) $row['total_amount'], 2) }}</td>
                            <td>{{ number_format((float) $row['on_hold_amount'], 2) }}</td>
                            <td>{{ number_format((float) $row['active_amount'], 2) }}</td>
                            <td>
                                <div class="d-flex gap-2 align-items-center">
                                    <a href="{{ route('admin.accounts.khatas.show', $row['khata']) }}" class="btn btn-sm btn-outline-primary">Open</a>
                                    <form action="{{ route('admin.accounts.khatas.destroy', $row['khata']) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete khata: {{ $row['khata']->name }}? All entries inside it will be deleted and partner assignments will be recalculated.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Khata" aria-label="Delete Khata">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No khata found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

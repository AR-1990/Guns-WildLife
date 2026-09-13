@extends('admin.layouts.main')

@section('title', 'Contacts')
@section('pageHeading', 'Contacts')

@section('content')
<div class="accounts-dashboard">
    <div class="dashboard-content__head">
        <h2 class="boldHeading">Website Contact Forms</h2>

        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Contacts</li>
            </ul>
        </nav>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="account-card account-card--yellow">
                <div class="account-card__content">
                    <h3 class="account-card__title">Total Requests</h3>
                    <p class="metric-card__value">{{ $summary['total'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="account-card account-card--teal">
                <div class="account-card__content">
                    <h3 class="account-card__title">Dealer</h3>
                    <p class="metric-card__value">{{ $summary['dealer'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="account-card account-card--orange">
                <div class="account-card__content">
                    <h3 class="account-card__title">Support</h3>
                    <p class="metric-card__value">{{ $summary['support'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="account-card account-card--red">
                <div class="account-card__content">
                    <h3 class="account-card__title">Other</h3>
                    <p class="metric-card__value">{{ $summary['other'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard-card mb-4">
        <form action="{{ route('admin.contacts.index') }}" method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Form Type</label>
                    <select class="form-select js-select2" name="form_type">
                        <option value="">All Forms</option>
                        @foreach ($formOptions as $value => $label)
                            <option value="{{ $value }}" {{ $filters['form_type'] === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" name="search" value="{{ $filters['search'] }}" placeholder="Name, email, phone">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="themeBtn w-100">Search</button>
                    <a href="{{ route('admin.contacts.index') }}" class="themeBtn themeBtn--white w-100 text-center">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <div class="account-ledger">
        <div class="account-ledger__header">
            <h3 class="account-ledger__title">Submitted Forms</h3>
            <div class="account-ledger__controls">
                <span class="badge blue">{{ $contacts->total() }} Records</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover tables account-data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Form</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Company</th>
                        <th>City</th>
                        <th>Details</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($contacts as $contact)
                        <tr>
                            <td>{{ $contacts->firstItem() + $loop->index }}</td>
                            <td>{{ $contact->created_at?->format('d-M-Y h:i A') }}</td>
                            <td>{{ $contact->form_label }}</td>
                            <td>{{ $contact->full_name }}</td>
                            <td>{{ $contact->email }}</td>
                            <td>{{ $contact->phone }}</td>
                            <td>{{ $contact->company_name ?: 'N/A' }}</td>
                            <td>{{ $contact->city ?: 'N/A' }}</td>
                            <td>{{ $contact->message ?: 'N/A' }}</td>
                            <td>
                                <div class="account-action-links">
                                    <a href="{{ route('admin.contacts.show', $contact) }}" class="btn btn-sm btn-outline-primary account-icon-btn" title="View Contact" aria-label="View Contact">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form action="{{ route('admin.contacts.destroy', $contact) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this contact?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger account-icon-btn" title="Delete Contact" aria-label="Delete Contact">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="account-empty-state">No contact form found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $contacts->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection

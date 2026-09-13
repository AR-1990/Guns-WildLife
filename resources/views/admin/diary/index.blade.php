@extends('admin.layouts.main')

@section('title', 'My Diary')
@section('pageHeading', 'My Diary')

@section('content')
<div class="accounts-dashboard">
    <div class="dashboard-content__head">
        <h2 class="boldHeading">My Diary</h2>

        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route(auth()->user()->dashboardRouteName()) }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">My Diary</li>
            </ul>
        </nav>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-4 col-md-6">
            <div class="account-card account-card--yellow">
                <div class="account-card__content">
                    <h3 class="account-card__title">Total Contacts</h3>
                    <p class="metric-card__value">{{ $summary['total'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="account-card account-card--teal">
                <div class="account-card__content">
                    <h3 class="account-card__title">Manual</h3>
                    <p class="metric-card__value">{{ $summary['manual'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="account-card account-card--orange">
                <div class="account-card__content">
                    <h3 class="account-card__title">From Sales</h3>
                    <p class="metric-card__value">{{ $summary['sale'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="account-toolbar">
        <div>
            <h3 class="account-toolbar__title">Contact Diary</h3>
            <p class="account-toolbar__text">Name aur phone required hain. Address, email aur note optional rahenge. Sales se aane wale contacts bhi yahin save honge.</p>
        </div>
        <div class="account-toolbar__group">
            <a href="{{ route('admin.diary.create') }}" class="themeBtn">Add Contact</a>
        </div>
    </div>

    <div class="dashboard-card mb-4">
        <form action="{{ route('admin.diary.index') }}" method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" name="search" value="{{ $filters['search'] }}" placeholder="Name, phone, email, address">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Source</label>
                    <select class="form-select js-select2" name="source">
                        <option value="">All Sources</option>
                        <option value="manual" {{ $filters['source'] === 'manual' ? 'selected' : '' }}>Manual</option>
                        <option value="sale" {{ $filters['source'] === 'sale' ? 'selected' : '' }}>Sale</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="themeBtn w-100">Search</button>
                    <a href="{{ route('admin.diary.index') }}" class="themeBtn themeBtn--white w-100 text-center">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <div class="account-ledger">
        <div class="account-ledger__header">
            <h3 class="account-ledger__title">Diary Contacts</h3>
            <div class="account-ledger__controls">
                <span class="badge blue">{{ $contacts->total() }} Records</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover tables account-data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Address</th>
                        <th>Note</th>
                        <th>Source</th>
                        <th>Added By</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($contacts as $contact)
                        <tr>
                            <td>{{ $contacts->firstItem() + $loop->index }}</td>
                            <td>{{ $contact->full_name }}</td>
                            <td>{{ $contact->phone }}</td>
                            <td>{{ $contact->email ?: 'N/A' }}</td>
                            <td>{{ $contact->address ?: 'N/A' }}</td>
                            <td>{{ $contact->notes ?: 'N/A' }}</td>
                            <td>
                                @if ($contact->source === 'sale')
                                    Sale{{ $contact->sale?->receipt_no ? ' - ' . $contact->sale->receipt_no : '' }}
                                @else
                                    Manual
                                @endif
                            </td>
                            <td>{{ $contact->creator?->name ?: 'N/A' }}</td>
                            <td>
                                <div class="account-action-links">
                                    <a href="{{ route('admin.diary.edit', $contact) }}" class="btn btn-sm btn-outline-primary account-icon-btn" title="Edit Contact" aria-label="Edit Contact">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form action="{{ route('admin.diary.destroy', $contact) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this contact?');">
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
                            <td colspan="9" class="account-empty-state">No diary contact found.</td>
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

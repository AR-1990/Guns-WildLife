@extends('admin.layouts.main')

@section('title', 'Users')
@section('pageHeading', 'Users')

@section('content')
<div class="accounts-dashboard">
    <div class="dashboard-content__head">
        <h2 class="boldHeading">Salesmen</h2>

        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item">Accounts</li>
                <li class="breadcrumb-item active" aria-current="page">Users</li>
            </ul>
        </nav>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-4 col-md-6">
            <div class="account-card account-card--yellow">
                <div class="account-card__content">
                    <h3 class="account-card__title">Total Users</h3>
                    <p class="metric-card__value">{{ $summary['total'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="account-card account-card--teal">
                <div class="account-card__content">
                    <h3 class="account-card__title">Salesmen</h3>
                    <p class="metric-card__value">{{ $summary['salesmen'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="account-toolbar">
        <div>
            <h3 class="account-toolbar__title">Salesman Records</h3>
            <p class="account-toolbar__text">Separate create and edit pages with simple professional listing.</p>
        </div>
        <div class="account-toolbar__group">
            <a href="{{ route('admin.accounts.users.create') }}" class="themeBtn">Add User</a>
        </div>
    </div>

    <div class="account-ledger">
        <div class="account-ledger__header">
            <h3 class="account-ledger__title">User Ledger</h3>
            <div class="account-ledger__controls">
                <span class="badge blue">{{ $users->total() }} Records</span>
            </div>
        </div>

        <div class="table-responsive">
            <table id="usersTable" class="table table-hover tables account-data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $managedUser)
                        <tr>
                            <td>{{ $users->firstItem() + $loop->index }}</td>
                            <td>{{ $managedUser->name }}</td>
                            <td>{{ $managedUser->email }}</td>
                            <td>
                                <span class="badge active">
                                    {{ $managedUser->roleLabel() }}
                                </span>
                            </td>
                            <td>{{ $managedUser->created_at?->format('d-M-Y') }}</td>
                            <td>
                                <div class="account-action-links">
                                    <a href="{{ route('admin.accounts.users.edit', $managedUser) }}" class="btn btn-sm btn-outline-secondary account-icon-btn" title="Edit User" aria-label="Edit User">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form action="{{ route('admin.accounts.users.destroy', $managedUser) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger account-icon-btn" title="Delete User" aria-label="Delete User">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="account-empty-state">No salesman found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $users->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection

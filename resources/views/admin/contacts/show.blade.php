@extends('admin.layouts.main')

@section('title', 'Contact Details')
@section('pageHeading', 'Contact Details')

@section('content')
<div class="accounts-dashboard">
    <div class="dashboard-content__head">
        <h2 class="boldHeading">Contact Request Detail</h2>

        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.contacts.index') }}">Contacts</a></li>
                <li class="breadcrumb-item active" aria-current="page">View</li>
            </ul>
        </nav>
    </div>

    <div class="dashboard-card">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h3 class="mb-1">{{ $contact->form_label }}</h3>
                <p class="account-toolbar__text mb-0">Submitted on {{ $contact->created_at?->format('d-M-Y h:i A') }}</p>
            </div>
            <a href="{{ route('admin.contacts.index') }}" class="themeBtn themeBtn--white">Back to List</a>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="salary-loan-chip">
                    <span>Full Name</span>
                    <strong>{{ $contact->full_name }}</strong>
                </div>
            </div>
            <div class="col-md-6">
                <div class="salary-loan-chip">
                    <span>Email</span>
                    <strong>{{ $contact->email }}</strong>
                </div>
            </div>
            <div class="col-md-6">
                <div class="salary-loan-chip">
                    <span>Phone</span>
                    <strong>{{ $contact->phone }}</strong>
                </div>
            </div>
            <div class="col-md-6">
                <div class="salary-loan-chip">
                    <span>Company</span>
                    <strong>{{ $contact->company_name ?: 'N/A' }}</strong>
                </div>
            </div>
            <div class="col-md-6">
                <div class="salary-loan-chip">
                    <span>City</span>
                    <strong>{{ $contact->city ?: 'N/A' }}</strong>
                </div>
            </div>
            <div class="col-md-6">
                <div class="salary-loan-chip">
                    <span>Form Type</span>
                    <strong>{{ $contact->form_label }}</strong>
                </div>
            </div>
            <div class="col-12">
                <label class="form-label">Details</label>
                <div class="form-control" style="min-height: 140px;">{{ $contact->message ?: 'N/A' }}</div>
            </div>
        </div>
    </div>
</div>
@endsection

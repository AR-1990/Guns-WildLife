@extends('admin.layouts.main')

@section('title', 'Receipt Settings')
@section('pageHeading', 'Receipt Settings')

@section('content')
@php
    $logoUrl = $settings->logo_path ? asset('storage/' . $settings->logo_path) : null;
@endphp

<div class="settings-dashboard">
    <div class="dashboard-content__head">
        <h2 class="boldHeading">Receipt Settings</h2>
        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Settings</li>
            </ul>
        </nav>
    </div>

    <div class="account-toolbar">
        <div>
            <h3 class="account-toolbar__title">Business Receipt Profile</h3>
            <p class="account-toolbar__text">Manage the company details that appear on printed receipts and downloaded PDF invoices.</p>
        </div>
        <div class="account-toolbar__group">
            <span class="settings-status-pill">
                <i class="fas fa-receipt"></i>
                Live On Receipt
            </span>
            <a href="{{ route('admin.sales.index') }}" class="themeBtn themeBtn--gray">View Sales</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-4 col-md-6">
            <div class="settings-summary-card">
                <div class="settings-summary-card__icon">
                    <i class="fas fa-building"></i>
                </div>
                <div class="settings-summary-card__content">
                    <span class="settings-summary-card__label">Company Name</span>
                    <h3 class="settings-summary-card__value">{{ $settings->company_name ?: 'Not set yet' }}</h3>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="settings-summary-card">
                <div class="settings-summary-card__icon">
                    <i class="fas fa-phone-alt"></i>
                </div>
                <div class="settings-summary-card__content">
                    <span class="settings-summary-card__label">Phone & Email</span>
                    <h3 class="settings-summary-card__value">{{ $settings->phone ?: 'Not set yet' }}</h3>
                    <p class="settings-summary-card__meta">{{ $settings->email ?: 'No email added' }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12">
            <div class="settings-summary-card">
                <div class="settings-summary-card__icon">
                    <i class="fas fa-image"></i>
                </div>
                <div class="settings-summary-card__content">
                    <span class="settings-summary-card__label">Receipt Logo</span>
                    <h3 class="settings-summary-card__value">{{ $settings->logo_path ? 'Uploaded' : 'Pending' }}</h3>
                    <p class="settings-summary-card__meta">Used on print receipt and PDF invoice.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="cashbook-transaction-form settings-form-shell">
            <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" id="settingsForm">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-lg-4">
                        <div class="settings-panel">
                            <div class="settings-panel__head">
                                <h3 class="settings-panel__title">Logo & Preview</h3>
                                <p class="settings-panel__text">Keep receipt branding clean and readable.</p>
                            </div>

                            <div class="settings-logo-preview">
                                @if ($logoUrl)
                                    <img src="{{ $logoUrl }}" alt="Company Logo" class="settings-logo-preview__image">
                                @else
                                    <div class="settings-logo-preview__placeholder">
                                        <i class="fas fa-image"></i>
                                        <span>No logo uploaded yet</span>
                                    </div>
                                @endif
                            </div>

                            <div class="mb-0">
                                <label class="form-label">Company Logo</label>
                                <input type="file" class="file-upload-input" name="logo" id="profilePicUpload" accept="image/*" hidden>
                                <label for="profilePicUpload" class="file-upload-area settings-upload-area">
                                    <div class="file-upload-icon">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                    </div>
                                    <p class="file-upload-text">
                                        Upload receipt logo or
                                        <strong class="text-primary">Browse file</strong>
                                    </p>
                                    <p class="file-upload-hint">Supports JPG, PNG, GIF up to 2MB</p>
                                </label>
                            </div>

                            <div class="settings-preview-note">
                                <i class="fas fa-circle-info"></i>
                                This logo will appear on print receipts and PDF downloads.
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="settings-panel">
                            <div class="settings-panel__head">
                                <h3 class="settings-panel__title">Business Information</h3>
                                <p class="settings-panel__text">These details are used across receipts, invoices, and customer-facing print documents.</p>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Company Name</label>
                                        <input type="text" name="company_name" class="form-control"
                                            value="{{ old('company_name', $settings->company_name) }}" placeholder="Enter company name" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Phone Number</label>
                                        <input type="text" name="phone" class="form-control"
                                            value="{{ old('phone', $settings->phone) }}" placeholder="Enter contact number" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Email Address</label>
                                        <input type="email" name="email" class="form-control"
                                            value="{{ old('email', $settings->email) }}" placeholder="Enter email address" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Receipt Label</label>
                                        <input type="text" class="form-control" value="Sales Receipt / Invoice" readonly>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="mb-0">
                                        <label class="form-label">Address</label>
                                        <textarea name="address" class="form-control" rows="5" placeholder="Enter complete business address" required>{{ old('address', $settings->address) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-12">
                        <div class="settings-panel">
                            <div class="settings-panel__head">
                                <h3 class="settings-panel__title">Change Password</h3>
                                <p class="settings-panel__text">Leave these fields empty if you only want to update receipt settings.</p>
                            </div>

                            <div class="row g-3">
                                <div class="col-lg-4 col-md-6">
                                    <div class="mb-0">
                                        <label class="form-label">Current Password</label>
                                        <input type="password" name="current_password" class="form-control"
                                            placeholder="Enter current password">
                                    </div>
                                </div>

                                <div class="col-lg-4 col-md-6">
                                    <div class="mb-0">
                                        <label class="form-label">New Password</label>
                                        <input type="password" name="new_password" class="form-control"
                                            placeholder="Enter new password">
                                    </div>
                                </div>

                                <div class="col-lg-4 col-md-12">
                                    <div class="mb-0">
                                        <label class="form-label">Confirm Password</label>
                                        <input type="password" name="new_password_confirmation" class="form-control"
                                            placeholder="Confirm new password">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-3 flex-wrap">
                    <button type="submit" class="themeBtn themeBtn--primary">Save Settings</button>
                    <a href="{{ route('admin.dashboard') }}" class="themeBtn themeBtn--gray">Back to Dashboard</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

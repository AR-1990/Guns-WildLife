<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Guns and Wildlife') | Dashboard</title>
    <link rel="icon" type="image/png" href="{{ asset('admin-assets/images/logo.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/vendor/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/vendor/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/vendor/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/css/style.css') }}" />
    <link rel="stylesheet" href="{{ asset('admin-assets/css/responsive.css') }}" />
    @stack('styles')
</head>

<body>
    @php($user = auth()->user())

    <div class="dashboard-wrapper">
        <div class="dashboard-sidebar">
            <a href="{{ $user ? route($user->dashboardRouteName()) : route('admin.login') }}" class="dashboard-sidebar__logo">
                <img src="{{ asset('admin-assets/images/logo.png') }}" alt="image" class="img-fluid">
            </a>
            <ul class="dashboard-sidebar__list">

                <li>
                    <a href="{{ route($user->dashboardRouteName()) }}" class="{{ request()->routeIs($user->dashboardRouteName()) ? 'active' : '' }}">
                        <i class="fas fa-home"></i>
                        Dashboard
                    </a>
                </li>

                @if ($user->isAdmin() || $user->isSalesman())
                    <li class="sidebar-divider"></li>

                    <li>
                        <a href="{{ route('admin.sales.index') }}" class="{{ request()->routeIs('admin.sales.*') ? 'active' : '' }}">
                            <i class="fas fa-chart-line"></i>
                            Sales
                        </a>
                    </li>
                @endif

                @if ($user->isAdmin())
                    <li class="sidebar-divider"></li>

                    <li>
                        <a href="{{ route('admin.products.index') }}"
                            class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                            <i class="fas fa-box-open"></i>
                            Products
                        </a>
                    </li>
                @endif

                @if ($user->isAdmin() || $user->isSalesman())
                    @php($accountsOpen = request()->routeIs('admin.accounts.*'))
                    @php($reportsOpen = request()->routeIs('admin.reports.*'))

                    <li>
                        <a href="#adminAccountsMenu" data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ $accountsOpen ? 'true' : 'false' }}" aria-controls="adminAccountsMenu"
                            class="dashboard-sidebar__toggle {{ $accountsOpen ? 'active' : '' }}">
                            <i class="fas fa-wallet"></i>
                            Accounts
                            <i class="fas fa-chevron-down ms-auto"></i>
                        </a>
                        <ul class="dashboard-sidebar__submenu collapse {{ $accountsOpen ? 'show' : '' }}"
                            id="adminAccountsMenu">
                            @if ($user->isAdmin())
                                {{-- <li>
                                    <a href="{{ route('admin.accounts.cashbook') }}"
                                        class="dashboard-sidebar__submenu-link {{ request()->routeIs('admin.accounts.cashbook') ? 'active' : '' }}">
                                        <i class="fas fa-book"></i>
                                        Cashbook
                                    </a>
                                </li> --}}
                            @endif
                            <li>
                                <a href="{{ route('admin.accounts.expenses') }}"
                                    class="dashboard-sidebar__submenu-link {{ request()->routeIs('admin.accounts.expenses*') ? 'active' : '' }}">
                                    <i class="fas fa-receipt"></i>
                                    Expenses
                                </a>
                            </li>
                            @if ($user->isAdmin())
                                <li>
                                    <a href="{{ route('admin.accounts.users') }}"
                                        class="dashboard-sidebar__submenu-link {{ request()->routeIs('admin.accounts.users*') && !request()->routeIs('admin.accounts.users.assignments*') ? 'active' : '' }}">
                                        <i class="fas fa-user-cog"></i>
                                        Users
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>

                    <li>
                        <a href="#adminReportsMenu" data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ $reportsOpen ? 'true' : 'false' }}" aria-controls="adminReportsMenu"
                            class="dashboard-sidebar__toggle {{ $reportsOpen ? 'active' : '' }}">
                            <i class="fas fa-file-alt"></i>
                            Reports
                            <i class="fas fa-chevron-down ms-auto"></i>
                        </a>
                        <ul class="dashboard-sidebar__submenu collapse {{ $reportsOpen ? 'show' : '' }}"
                            id="adminReportsMenu">
                            <li>
                                <a href="{{ route('admin.reports.sales') }}"
                                    class="dashboard-sidebar__submenu-link {{ request()->routeIs('admin.reports.sales') ? 'active' : '' }}">
                                    <i class="fas fa-chart-line"></i>
                                    Sales
                                </a>
                            </li>
                            @if ($user->isAdmin())
                                <li>
                                    <a href="{{ route('admin.reports.revenue') }}"
                                        class="dashboard-sidebar__submenu-link {{ request()->routeIs('admin.reports.revenue') ? 'active' : '' }}">
                                        <i class="fas fa-sack-dollar"></i>
                                        Revenue
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.reports.loss') }}"
                                        class="dashboard-sidebar__submenu-link {{ request()->routeIs('admin.reports.loss') ? 'active' : '' }}">
                                        <i class="fas fa-chart-pie"></i>
                                        Loss
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>

                    @if ($user->isAdmin())
                        <li class="sidebar-divider"></li>

                        <li>
                            <a href="{{ route('admin.contacts.index') }}"
                                class="{{ request()->routeIs('admin.contacts.*') ? 'active' : '' }}">
                                <i class="fas fa-address-book"></i>
                                Contacts
                            </a>
                        </li>
                    @endif

                    <li>
                        <a href="{{ route('admin.diary.index') }}"
                            class="{{ request()->routeIs('admin.diary.*') ? 'active' : '' }}">
                            <i class="fas fa-address-book"></i>
                            My Diary
                        </a>
                    </li>

                    @if ($user->isAdmin())
                        <li>
                            <a href="{{ route('admin.settings.index') }}"
                                class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                                <i class="fas fa-sliders-h"></i>
                                Settings
                            </a>
                        </li>
                    @endif
                @endif

            </ul>
        </div>
        <div class="dashboard-content">
            <header class="header">
                <h1 class="secHeading">@yield('pageHeading', __('Dashboard'))</h1>
                <div class="header-links">
                    <div class="header-links__profile">
                        <h3>
                            {{ $user->name }}
                            <span>{{ $user->roleLabel() }}</span>
                        </h3>
                        <img src="{{ asset('admin-assets/images/profile.png') }}" alt="image" class="img-fluid">
                    </div>
                    <form action="{{ route('admin.logout') }}" method="POST" class="ms-3">
                        @csrf
                        <button type="submit" class="themeBtn themeBtn--primary">Logout</button>
                    </form>
                </div>
            </header>

            @if (session('status'))
                <div class="alert alert-success mt-3 mb-0">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger mt-3 mb-0">{{ $errors->first() }}</div>
            @endif

            <!-- Main content starts -->
            <main class="main-content">

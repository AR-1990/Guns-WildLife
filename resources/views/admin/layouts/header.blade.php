<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Guns and Wildlife') | Dashboard</title>
    <link rel="icon" type="image/png" href="{{ asset('admin-assets/images/logo.png') }}?v=gwl-20260920-01">
    <link rel="stylesheet" href="{{ asset('assets/css/fontawesome.min.css') }}?v=gwl-20260920-01">
    <link rel="stylesheet" href="{{ asset('admin-assets/vendor/css/bootstrap.min.css') }}?v=gwl-20260920-01">
    <link rel="stylesheet" href="{{ asset('admin-assets/vendor/css/dataTables.bootstrap5.min.css') }}?v=gwl-20260920-01">
    <link rel="stylesheet" href="{{ asset('admin-assets/vendor/css/select2.min.css') }}?v=gwl-20260920-01">
    <link rel="stylesheet" href="{{ asset('admin-assets/css/style.css') }}?v=gwl-20260920-01" />
    <link rel="stylesheet" href="{{ asset('admin-assets/css/responsive.css') }}?v=gwl-20260920-01" />
    @stack('styles')
</head>

<body>
    <script>
        try {
            if (window.localStorage.getItem('admin-sidebar-collapsed') === '1') {
                document.documentElement.classList.add('sidebar-collapsed');
            }
        } catch (error) {
            console.warn('Sidebar preference could not be restored.', error);
        }
    </script>
    @php($user = auth()->user())

    <div class="dashboard-wrapper">
        <div class="dashboard-sidebar">
            <div class="dashboard-sidebar__head">
                <a href="{{ $user ? route($user->dashboardRouteName()) : route('admin.login') }}" class="dashboard-sidebar__logo" aria-label="Dashboard home">
                    <img src="{{ asset('admin-assets/images/logo.png') }}" alt="image" class="img-fluid">
                </a>
                <button type="button" class="dashboard-sidebar__collapse" id="dashboardSidebarCollapse"
                    aria-label="Collapse sidebar" aria-expanded="true" title="Collapse sidebar">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <ul class="dashboard-sidebar__list">

                <li>
                    <a href="{{ route($user->dashboardRouteName()) }}" class="{{ request()->routeIs($user->dashboardRouteName()) ? 'active' : '' }}"
                        data-sidebar-label="Dashboard" title="Dashboard">
                        <i class="fas fa-home dashboard-sidebar__icon"></i>
                        <span class="dashboard-sidebar__label">Dashboard</span>
                    </a>
                </li>

                @if ($user->isAdmin() || $user->isSalesman())
                    <li class="sidebar-divider"></li>

                    <li>
                        <a href="{{ route('admin.sales.index') }}" class="{{ request()->routeIs('admin.sales.*') ? 'active' : '' }}"
                            data-sidebar-label="Sales" title="Sales">
                            <i class="fas fa-chart-line dashboard-sidebar__icon"></i>
                            <span class="dashboard-sidebar__label">Sales</span>
                        </a>
                    </li>
                @endif

                @if ($user->isAdmin())
                    <li class="sidebar-divider"></li>

                    <li>
                        <a href="{{ route('admin.products.index') }}"
                            class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}"
                            data-sidebar-label="Products" title="Products">
                            <i class="fas fa-box-open dashboard-sidebar__icon"></i>
                            <span class="dashboard-sidebar__label">Products</span>
                        </a>
                    </li>
                @endif

                @if ($user->isAdmin() || $user->isSalesman())
                    @php($accountsOpen = request()->routeIs('admin.accounts.*'))
                    @php($reportsOpen = request()->routeIs('admin.reports.*'))

                    <li class="dashboard-sidebar__has-submenu">
                        <a href="#adminAccountsMenu" data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ $accountsOpen ? 'true' : 'false' }}" aria-controls="adminAccountsMenu"
                            class="dashboard-sidebar__toggle {{ $accountsOpen ? 'active' : '' }}"
                            data-sidebar-label="Accounts" title="Accounts">
                            <span class="dashboard-sidebar__toggle-main">
                                <i class="fas fa-wallet dashboard-sidebar__icon"></i>
                                <span class="dashboard-sidebar__label">Accounts</span>
                            </span>
                            <i class="fas fa-chevron-down dashboard-sidebar__caret"></i>
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
                                    class="dashboard-sidebar__submenu-link {{ request()->routeIs('admin.accounts.expenses*') ? 'active' : '' }}"
                                    data-sidebar-label="Expenses" title="Expenses">
                                    <i class="fas fa-receipt dashboard-sidebar__icon"></i>
                                    <span class="dashboard-sidebar__label">Expenses</span>
                                </a>
                            </li>
                            @if ($user->isAdmin())
                                <li>
                                    <a href="{{ route('admin.accounts.users') }}"
                                        class="dashboard-sidebar__submenu-link {{ request()->routeIs('admin.accounts.users*') && !request()->routeIs('admin.accounts.users.assignments*') ? 'active' : '' }}"
                                        data-sidebar-label="Users" title="Users">
                                        <i class="fas fa-user-cog dashboard-sidebar__icon"></i>
                                        <span class="dashboard-sidebar__label">Users</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>

                    <li class="dashboard-sidebar__has-submenu">
                        <a href="#adminReportsMenu" data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ $reportsOpen ? 'true' : 'false' }}" aria-controls="adminReportsMenu"
                            class="dashboard-sidebar__toggle {{ $reportsOpen ? 'active' : '' }}"
                            data-sidebar-label="Reports" title="Reports">
                            <span class="dashboard-sidebar__toggle-main">
                                <i class="fas fa-file-alt dashboard-sidebar__icon"></i>
                                <span class="dashboard-sidebar__label">Reports</span>
                            </span>
                            <i class="fas fa-chevron-down dashboard-sidebar__caret"></i>
                        </a>
                        <ul class="dashboard-sidebar__submenu collapse {{ $reportsOpen ? 'show' : '' }}"
                            id="adminReportsMenu">
                            <li>
                                <a href="{{ route('admin.reports.sales') }}"
                                    class="dashboard-sidebar__submenu-link {{ request()->routeIs('admin.reports.sales') ? 'active' : '' }}"
                                    data-sidebar-label="Sales Report" title="Sales Report">
                                    <i class="fas fa-chart-line dashboard-sidebar__icon"></i>
                                    <span class="dashboard-sidebar__label">Sales</span>
                                </a>
                            </li>
                            @if ($user->isAdmin())
                                <li>
                                    <a href="{{ route('admin.reports.revenue') }}"
                                        class="dashboard-sidebar__submenu-link {{ request()->routeIs('admin.reports.revenue') ? 'active' : '' }}"
                                        data-sidebar-label="Revenue" title="Revenue">
                                        <i class="fas fa-sack-dollar dashboard-sidebar__icon"></i>
                                        <span class="dashboard-sidebar__label">Revenue</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.reports.loss') }}"
                                        class="dashboard-sidebar__submenu-link {{ request()->routeIs('admin.reports.loss') ? 'active' : '' }}"
                                        data-sidebar-label="Loss" title="Loss">
                                        <i class="fas fa-chart-pie dashboard-sidebar__icon"></i>
                                        <span class="dashboard-sidebar__label">Loss</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>

                    @if ($user->isAdmin())
                        <li class="sidebar-divider"></li>

                        <li>
                            <a href="{{ route('admin.contacts.index') }}"
                                class="{{ request()->routeIs('admin.contacts.*') ? 'active' : '' }}"
                                data-sidebar-label="Contacts" title="Contacts">
                                <i class="fas fa-address-book dashboard-sidebar__icon"></i>
                                <span class="dashboard-sidebar__label">Contacts</span>
                            </a>
                        </li>
                    @endif

                    <li>
                        <a href="{{ route('admin.diary.index') }}"
                            class="{{ request()->routeIs('admin.diary.*') ? 'active' : '' }}"
                            data-sidebar-label="My Diary" title="My Diary">
                            <i class="fas fa-address-book dashboard-sidebar__icon"></i>
                            <span class="dashboard-sidebar__label">My Diary</span>
                        </a>
                    </li>

                    @if ($user->isAdmin())
                        <li>
                            <a href="{{ route('admin.settings.index') }}"
                                class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"
                                data-sidebar-label="Settings" title="Settings">
                                <i class="fas fa-sliders-h dashboard-sidebar__icon"></i>
                                <span class="dashboard-sidebar__label">Settings</span>
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

            <script>
                (function () {
                    try {
                        var storageKey = 'admin-sidebar-collapsed';
                        var root = document.documentElement;
                        var btn = document.getElementById('dashboardSidebarCollapse');

                        if (!btn) return;
                        if (btn.getAttribute('data-sidebar-toggle-bound') === '1') return;

                        function applyState(collapsed) {
                            if (collapsed) root.classList.add('sidebar-collapsed');
                            else root.classList.remove('sidebar-collapsed');
                            btn.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
                            btn.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
                            btn.setAttribute('title', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
                        }

                        applyState(root.classList.contains('sidebar-collapsed'));

                        btn.addEventListener('click', function (e) {
                            e.preventDefault();
                            e.stopPropagation();
                            var collapsed = !root.classList.contains('sidebar-collapsed');
                            applyState(collapsed);
                            try { window.localStorage.setItem(storageKey, collapsed ? '1' : '0'); } catch (err) {}
                        });

                        btn.setAttribute('data-sidebar-toggle-bound', '1');
                    } catch (e) {
                        try { console.warn('Sidebar inline init failed.', e); } catch (ignore) {}
                    }
                })();
            </script>

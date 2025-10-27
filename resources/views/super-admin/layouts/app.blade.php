<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Super Admin') - {{ config('app.name') }}</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('logo2.png') }}">

    <style>
        :root {
            --sidebar-width: 260px;
            --brand-gradient: linear-gradient(180deg, #1e3a8a 0%, #1e40af 100%);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #0f172a;
            min-height: 100vh;
        }

        .admin-layout {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .sidebar-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
            z-index: 1030;
        }

        .sidebar-backdrop.visible {
            opacity: 1;
            visibility: visible;
        }

        .sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            height: 100vh;
            width: min(82vw, var(--sidebar-width));
            background: var(--brand-gradient);
            color: #ffffff;
            transform: translateX(-100%);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            z-index: 1040;
            display: flex;
            flex-direction: column;
            padding: 1.5rem 0;
            box-shadow: 0 0 0 rgba(0, 0, 0, 0);
        }

        .sidebar.open {
            transform: translateX(0);
            box-shadow: 20px 0 45px -25px rgba(15, 23, 42, 0.45);
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem 1rem;
        }

        .sidebar-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.25rem;
            font-weight: 700;
        }

        .sidebar-close {
            background: transparent;
            border: 0;
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.5rem;
            line-height: 1;
            padding: 0.25rem;
            border-radius: 0.5rem;
        }

        .sidebar-close:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.12);
        }

        .sidebar nav {
            flex: 1;
            overflow-y: auto;
            padding: 0.5rem 0;
        }

        .sidebar .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1.5rem;
            transition: background 0.2s ease, color 0.2s ease;
            font-weight: 500;
        }

        .sidebar .nav-link i {
            width: 1.25rem;
            text-align: center;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.12);
        }

        .sidebar hr {
            border-color: rgba(255, 255, 255, 0.15);
            margin: 1.5rem 1.5rem 0.75rem;
        }

        .layout-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin-left: 0;
            padding: 1.5rem 1rem 3rem;
            transition: margin-left 0.3s ease;
        }

        .top-bar {
            background: #ffffff;
            border-radius: 1rem;
            padding: 1rem 1.25rem;
            box-shadow: 0 10px 30px -15px rgba(15, 23, 42, 0.2);
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            position: sticky;
            top: 1rem;
            z-index: 10;
        }

        .top-bar-main {
            display: flex;
            align-items: center;
            gap: 1rem;
            min-width: 0;
        }

        .sidebar-toggle {
            background: #1e3a8a;
            border: 0;
            color: #ffffff;
            width: 44px;
            height: 44px;
            border-radius: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 15px 30px -15px rgba(30, 58, 138, 0.8);
        }

        .sidebar-toggle:focus-visible,
        .sidebar-close:focus-visible {
            outline: 2px solid rgba(255, 255, 255, 0.7);
            outline-offset: 2px;
        }

        .top-bar-titles h4 {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 700;
            color: #0f172a;
        }

        .top-bar-titles small {
            display: block;
            color: #64748b;
            margin-top: 0.25rem;
        }

        .user-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #f1f5f9;
            color: #1e293b;
            padding: 0.55rem 1rem;
            border-radius: 9999px;
            font-weight: 600;
            white-space: nowrap;
            align-self: flex-start;
            margin-left: 3.25rem;
            box-shadow: 0 18px 38px -26px rgba(30, 58, 138, 0.9);
            border: 1px solid rgba(15, 23, 42, 0.05);
        }

        .user-chip i {
            color: #1d4ed8;
        }

        .main-shell {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .alert {
            border-radius: 0.9rem;
            padding: 0.9rem 1rem;
        }

        .alert i {
            margin-right: 0.5rem;
        }

        body.sidebar-open {
            overflow: hidden;
        }

        @media (min-width: 576px) {
            .layout-content {
                padding: 2rem 1.5rem 3.5rem;
            }
        }

        @media (min-width: 768px) {
            .top-bar {
                padding: 1.25rem 1.5rem;
            }

            .top-bar-titles h4 {
                font-size: 1.35rem;
            }
        }

        @media (min-width: 992px) {
            .admin-layout {
                flex-direction: row;
            }

            .sidebar {
                width: var(--sidebar-width);
                transform: none;
                box-shadow: none;
            }

            .sidebar-backdrop {
                display: none;
            }

            .layout-content {
                margin-left: var(--sidebar-width);
                padding: 2rem 3rem;
            }

            .top-bar {
                position: static;
                margin-bottom: 2rem;
                gap: 1rem;
            }

            .user-chip {
                margin-left: 0;
            }

            .sidebar-toggle,
            .sidebar-close {
                display: none;
            }
        }
    </style>

    @yield('styles')
</head>

<body>
    <div class="admin-layout">
        <div class="sidebar-backdrop" id="sidebarBackdrop" hidden></div>

        <aside class="sidebar" id="sidebar" aria-hidden="true">
            <div class="sidebar-header">
                <div class="sidebar-title">
                    <i class="fas fa-crown"></i>
                    <span>Super Admin</span>
                </div>

                <button class="sidebar-close" id="sidebarClose" type="button" aria-label="Fechar menu">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <nav class="nav flex-column">
                <a class="nav-link {{ request()->routeIs('super-admin.dashboard') ? 'active' : '' }}"
                    href="{{ route('super-admin.dashboard') }}">
                    <i class="fas fa-home"></i>
                    Dashboard
                </a>
                <a class="nav-link {{ request()->routeIs('super-admin.empresas*') ? 'active' : '' }}"
                    href="{{ route('super-admin.empresas') }}">
                    <i class="fas fa-building"></i>
                    Empresas
                </a>
                <a class="nav-link {{ request()->routeIs('super-admin.planos*') ? 'active' : '' }}"
                    href="{{ route('super-admin.planos') }}">
                    <i class="fas fa-tags"></i>
                    Planos
                </a>
                <a class="nav-link {{ request()->routeIs('super-admin.relatorios') ? 'active' : '' }}"
                    href="{{ route('super-admin.relatorios') }}">
                    <i class="fas fa-chart-line"></i>
                    Relatórios
                </a>

                <hr>

                <a class="nav-link" href="{{ route('dashboard') }}">
                    <i class="fas fa-arrow-left"></i>
                    Voltar ao Sistema
                </a>
                <a class="nav-link" href="{{ route('logout') }}"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt"></i>
                    Sair
                </a>
            </nav>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </aside>

        <div class="layout-content">
            <header class="top-bar">
                <div class="user-chip">
                    <i class="fas fa-user-circle"></i>
                    <span>{{ auth()->user()->name }}</span>
                </div>

                <div class="top-bar-main">
                    <button class="sidebar-toggle" id="sidebarToggle" type="button" aria-label="Abrir menu"
                        aria-expanded="false">
                        <i class="fas fa-bars"></i>
                    </button>

                    <div class="top-bar-titles">
                        <h4>@yield('page-title', 'Dashboard')</h4>
                        <small>@yield('page-subtitle', '')</small>
                    </div>
                </div>
            </header>

            <main class="main-shell">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        (function () {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarClose = document.getElementById('sidebarClose');
            const sidebarBackdrop = document.getElementById('sidebarBackdrop');
            const navLinks = Array.from(sidebar?.querySelectorAll('a.nav-link') || []);
            const desktopQuery = window.matchMedia('(min-width: 992px)');

            const openSidebar = () => {
                sidebar?.classList.add('open');
                sidebarBackdrop?.classList.add('visible');
                sidebarBackdrop?.removeAttribute('hidden');
                document.body.classList.add('sidebar-open');
                sidebarToggle?.setAttribute('aria-expanded', 'true');
                sidebar?.setAttribute('aria-hidden', 'false');
            };

            const closeSidebar = () => {
                sidebar?.classList.remove('open');
                sidebarBackdrop?.classList.remove('visible');
                sidebarBackdrop?.setAttribute('hidden', 'hidden');
                document.body.classList.remove('sidebar-open');
                sidebarToggle?.setAttribute('aria-expanded', 'false');
                sidebar?.setAttribute('aria-hidden', 'true');
            };

            const handleViewportChange = () => {
                if (desktopQuery.matches) {
                    sidebar?.classList.add('open');
                    sidebar?.setAttribute('aria-hidden', 'false');
                    document.body.classList.remove('sidebar-open');
                    sidebarBackdrop?.classList.remove('visible');
                    sidebarBackdrop?.setAttribute('hidden', 'hidden');
                    sidebarToggle?.setAttribute('aria-expanded', 'false');
                } else {
                    closeSidebar();
                }
            };

            sidebarToggle?.addEventListener('click', () => {
                if (sidebar?.classList.contains('open')) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            });

            sidebarClose?.addEventListener('click', closeSidebar);
            sidebarBackdrop?.addEventListener('click', closeSidebar);

            navLinks.forEach((link) => {
                link.addEventListener('click', () => {
                    if (!desktopQuery.matches) {
                        closeSidebar();
                    }
                });
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeSidebar();
                }
            });

            handleViewportChange();
            desktopQuery.addEventListener('change', handleViewportChange);
        })();
    </script>

    @yield('scripts')
</body>

</html>

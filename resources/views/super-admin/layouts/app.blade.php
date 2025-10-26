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
    <link rel="alternate icon" type="image/png" href="{{ asset('logo2.png') }}">

    <style>
        :root {
            --sidebar-width: 260px;
            --header-height: 60px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #1e3a8a 0%, #1e40af 100%);
            padding-top: 20px;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .sidebar .logo {
            padding: 20px;
            text-align: center;
            color: white;
            font-size: 24px;
            font-weight: bold;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 25px;
            margin: 5px 15px;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            min-height: 100vh;
        }

        .top-bar {
            background: white;
            padding: 15px 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card .icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }

        .stat-card.primary .icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .stat-card.success .icon {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .stat-card.warning .icon {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        .stat-card.danger .icon {
            background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);
        }

        .stat-card .value {
            font-size: 32px;
            font-weight: bold;
            margin-top: 10px;
        }

        .stat-card .label {
            color: #6c757d;
            font-size: 14px;
        }

        .badge-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-status.ativo {
            background-color: #d1fae5;
            color: #065f46;
        }

        .badge-status.vencido {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .badge-status.bloqueado {
            background-color: #e5e7eb;
            color: #374151;
        }
    </style>

    @yield('styles')
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <i class="fas fa-crown"></i> Super Admin
        </div>

        <nav class="nav flex-column">
            <a class="nav-link {{ request()->routeIs('super-admin.dashboard') ? 'active' : '' }}"
                href="{{ route('super-admin.dashboard') }}">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a class="nav-link {{ request()->routeIs('super-admin.empresas*') ? 'active' : '' }}"
                href="{{ route('super-admin.empresas') }}">
                <i class="fas fa-building"></i> Empresas
            </a>
            <a class="nav-link {{ request()->routeIs('super-admin.planos*') ? 'active' : '' }}"
                href="{{ route('super-admin.planos') }}">
                <i class="fas fa-tags"></i> Planos
            </a>
            <a class="nav-link {{ request()->routeIs('super-admin.relatorios') ? 'active' : '' }}"
                href="{{ route('super-admin.relatorios') }}">
                <i class="fas fa-chart-line"></i> Relatórios
            </a>

            <hr style="border-color: rgba(255,255,255,0.1); margin: 20px 15px;">

            <a class="nav-link" href="{{ route('dashboard') }}">
                <i class="fas fa-arrow-left"></i> Voltar ao Sistema
            </a>
            <a class="nav-link" href="{{ route('logout') }}"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fas fa-sign-out-alt"></i> Sair
            </a>
        </nav>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div>
                <h4 class="mb-0">@yield('page-title', 'Dashboard')</h4>
                <small class="text-muted">@yield('page-subtitle', '')</small>
            </div>
            <div>
                <span class="text-muted">
                    <i class="fas fa-user-circle"></i> {{ auth()->user()->name }}
                </span>
            </div>
        </div>

        <!-- Alerts -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Page Content -->
        @yield('content')
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @yield('scripts')
</body>

</html>

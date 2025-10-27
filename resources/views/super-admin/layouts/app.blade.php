<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Super Admin') - {{ config('app.name') }}</title>

    <link rel="icon" type="image/png" href="{{ asset('logo2.png') }}">

    <!-- Scripts -->
    @env('local')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        @php
            $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
        @endphp

        <link rel="stylesheet" href="{{ asset('build/' . $manifest['resources/css/app.css']['file']) }}">
        <script type="module" src="{{ asset('build/' . $manifest['resources/js/app.js']['file']) }}"></script>
    @endenv

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    @yield('styles')
</head>

<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
    <div class="min-h-screen lg:flex">
        <div id="sidebarBackdrop"
            class="fixed inset-0 z-30 bg-slate-900/60 opacity-0 transition-opacity duration-200 ease-in-out pointer-events-none lg:hidden">
        </div>

        <aside id="sidebar"
            class="fixed inset-y-0 left-0 z-40 flex w-72 transform flex-col overflow-hidden bg-gradient-to-b from-indigo-900 via-indigo-800 to-indigo-700 text-white shadow-2xl transition-transform duration-200 ease-in-out lg:static lg:h-auto lg:translate-x-0">
            <div class="flex items-center justify-between px-6 pt-6 pb-4">
                <div class="flex items-center gap-3 text-lg font-semibold">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 text-white">
                        <i class="fa-solid fa-crown"></i>
                    </span>
                    <div class="leading-tight">
                        <p class="text-sm uppercase tracking-wide text-white/60">Área</p>
                        <p>Super Admin</p>
                    </div>
                </div>

                <button id="sidebarClose" type="button"
                    class="rounded-xl p-2 text-white/70 transition hover:bg-white/10 hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white lg:hidden"
                    aria-label="Fechar menu">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 pb-6">
                @php
                    $navLinkBase =
                        'flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition-colors duration-150';
                @endphp

                <a href="{{ route('super-admin.dashboard') }}"
                    class="{{ $navLinkBase }} {{ request()->routeIs('super-admin.dashboard') ? 'bg-white/15 text-white shadow-lg' : 'text-white/70 hover:bg-white/10 hover:text-white' }}"
                    data-nav>
                    <i class="fa-solid fa-home w-5 text-center"></i>
                    Dashboard
                </a>

                <a href="{{ route('super-admin.empresas') }}"
                    class="{{ $navLinkBase }} {{ request()->routeIs('super-admin.empresas*') ? 'bg-white/15 text-white shadow-lg' : 'text-white/70 hover:bg-white/10 hover:text-white' }}"
                    data-nav>
                    <i class="fa-solid fa-building w-5 text-center"></i>
                    Empresas
                </a>

                <a href="{{ route('super-admin.planos') }}"
                    class="{{ $navLinkBase }} {{ request()->routeIs('super-admin.planos*') ? 'bg-white/15 text-white shadow-lg' : 'text-white/70 hover:bg-white/10 hover:text-white' }}"
                    data-nav>
                    <i class="fa-solid fa-tags w-5 text-center"></i>
                    Planos
                </a>

                <a href="{{ route('super-admin.relatorios') }}"
                    class="{{ $navLinkBase }} {{ request()->routeIs('super-admin.relatorios') ? 'bg-white/15 text-white shadow-lg' : 'text-white/70 hover:bg-white/10 hover:text-white' }}"
                    data-nav>
                    <i class="fa-solid fa-chart-line w-5 text-center"></i>
                    Relatórios
                </a>

                <div class="my-6 border-t border-white/10"></div>

                <a href="{{ route('dashboard') }}"
                    class="{{ $navLinkBase }} text-white/70 hover:bg-white/10 hover:text-white" data-nav>
                    <i class="fa-solid fa-arrow-left w-5 text-center"></i>
                    Voltar ao sistema
                </a>

                <a href="{{ route('logout') }}"
                    class="{{ $navLinkBase }} text-white/70 hover:bg-white/10 hover:text-white"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();" data-nav>
                    <i class="fa-solid fa-sign-out-alt w-5 text-center"></i>
                    Sair
                </a>
            </nav>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                @csrf
            </form>
        </aside>

        <div class="flex-1 lg:ml-72">
            <header
                class="sticky top-0 z-30 mx-4 mt-4 flex flex-col gap-3 rounded-3xl bg-white/80 p-4 shadow-xl backdrop-blur lg:mx-8 lg:mt-8 lg:flex-row lg:items-center lg:justify-between lg:gap-6">
                <div class="flex flex-col gap-3">
                    <div
                        class="inline-flex w-fit items-center gap-3 rounded-full bg-indigo-100 px-4 py-2 text-sm font-semibold text-indigo-700 shadow-sm ring-1 ring-indigo-200">
                        <span
                            class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600 text-white shadow-inner">
                            <i class="fa-solid fa-user text-base"></i>
                        </span>
                        <span>{{ auth()->user()->name }}</span>
                    </div>

                    <div class="flex items-start gap-3">
                        <button id="sidebarToggle" type="button"
                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-600 text-white shadow-lg shadow-indigo-600/40 transition hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 lg:hidden"
                            aria-label="Abrir menu" aria-expanded="false">
                            <i class="fa-solid fa-bars text-lg"></i>
                        </button>

                        <div>
                            <h1 class="text-2xl font-bold text-slate-900 lg:text-3xl">
                                @yield('page-title', 'Dashboard')
                            </h1>
                            @php
                                $subtitle = trim($__env->yieldContent('page-subtitle', ''));
                            @endphp
                            @if ($subtitle !== '')
                                <p class="text-sm font-medium uppercase tracking-wide text-slate-400">
                                    {{ $subtitle }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="hidden items-center gap-3 lg:flex">
                    <button id="sidebarToggleDesktop" type="button"
                        class="hidden h-11 w-11 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-700 shadow-inner ring-1 ring-indigo-200 lg:flex lg:hover:bg-indigo-100"
                        aria-label="Alternar menu">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                </div>
            </header>

            <main class="mx-4 mb-10 mt-4 flex flex-col gap-6 lg:mx-8 lg:mt-8">
                @if (session('success'))
                    <div
                        class="rounded-2xl border border-emerald-200 bg-emerald-50/70 p-4 text-sm text-emerald-800 shadow-sm shadow-emerald-500/10 backdrop-blur">
                        <div class="flex items-start gap-3">
                            <span
                                class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-500 text-white shadow-emerald-500/40">
                                <i class="fa-solid fa-check"></i>
                            </span>
                            <div class="flex-1">
                                <p class="font-semibold">Sucesso</p>
                                <p>{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div
                        class="rounded-2xl border border-rose-200 bg-rose-50/70 p-4 text-sm text-rose-800 shadow-sm shadow-rose-500/10 backdrop-blur">
                        <div class="flex items-start gap-3">
                            <span
                                class="flex h-9 w-9 items-center justify-center rounded-full bg-rose-500 text-white shadow-rose-500/40">
                                <i class="fa-solid fa-exclamation-triangle"></i>
                            </span>
                            <div class="flex-1">
                                <p class="font-semibold">Ops!</p>
                                <p>{{ session('error') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarToggleDesktop = document.getElementById('sidebarToggleDesktop');
            const sidebarClose = document.getElementById('sidebarClose');
            const sidebarBackdrop = document.getElementById('sidebarBackdrop');
            const navLinks = Array.from(sidebar.querySelectorAll('[data-nav]'));
            const desktopQuery = window.matchMedia('(min-width: 1024px)');

            const openSidebar = () => {
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                sidebarBackdrop.classList.remove('pointer-events-none');
                sidebarBackdrop.classList.remove('opacity-0');
                sidebarBackdrop.classList.add('opacity-100');
                sidebarToggle?.setAttribute('aria-expanded', 'true');
            };

            const closeSidebar = () => {
                sidebar.classList.add('-translate-x-full');
                sidebar.classList.remove('translate-x-0');
                sidebarBackdrop.classList.add('pointer-events-none');
                sidebarBackdrop.classList.add('opacity-0');
                sidebarBackdrop.classList.remove('opacity-100');
                sidebarToggle?.setAttribute('aria-expanded', 'false');
            };

            const syncForViewport = () => {
                if (desktopQuery.matches) {
                    sidebar.classList.add('translate-x-0');
                    sidebar.classList.remove('-translate-x-full');
                    sidebarBackdrop.classList.add('pointer-events-none');
                    sidebarBackdrop.classList.add('opacity-0');
                    sidebarBackdrop.classList.remove('opacity-100');
                    sidebarToggle?.setAttribute('aria-expanded', 'false');
                } else {
                    closeSidebar();
                }
            };

            sidebarToggle?.addEventListener('click', () => {
                if (sidebar.classList.contains('-translate-x-full')) {
                    openSidebar();
                } else {
                    closeSidebar();
                }
            });

            sidebarToggleDesktop?.addEventListener('click', () => {
                if (sidebar.classList.contains('-translate-x-full')) {
                    openSidebar();
                } else {
                    closeSidebar();
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

            desktopQuery.addEventListener('change', syncForViewport);
            syncForViewport();
        });
    </script>

    @yield('scripts')
</body>

</html>



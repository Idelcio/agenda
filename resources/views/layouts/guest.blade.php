<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicons/logo2.png') }}">

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

    <style>
        body {
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #25D366, #128C7E, #075E54);
        }

        .guest-layout-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .guest-card {
            background: #25D366;
            color: #fff;
            box-shadow: 0 25px 50px -12px rgba(18, 140, 126, 0.55);
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(16px);
        }

        .guest-card label,
        .guest-card span,
        .guest-card p,
        .guest-card a,
        .guest-card h1,
        .guest-card h2,
        .guest-card h3,
        .guest-card .text-gray-600,
        .guest-card .text-gray-700,
        .guest-card .text-gray-900,
        .guest-card .text-indigo-600,
        .guest-card .text-indigo-900 {
            color: #fff !important;
        }

        .guest-card a {
            text-decoration: underline;
        }

        .guest-card a:hover {
            color: rgba(255, 255, 255, 0.85) !important;
        }

        .guest-card input {
            background-color: rgba(255, 255, 255, 0.96);
            color: #0f172a;
        }

        .guest-card input::placeholder {
            color: rgba(15, 23, 42, 0.6);
        }

        .guest-card input:focus {
            border-color: rgba(255, 255, 255, 0.75);
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.35);
        }

        .guest-card input[type="checkbox"] {
            accent-color: #ffffff;
        }

        .guest-card .text-green-600 {
            color: #e6ffe8 !important;
        }
    </style>
</head>

<body class="font-sans text-gray-900 antialiased">
    <div class="guest-layout-wrapper min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
        <div>
            <a href="/">
                <img src="{{ asset('logo2.png') }}" alt="{{ config('app.name', 'Agendoo') }} logo"
                    style="width: 110px; height: auto;" class="block object-contain">
            </a>

        </div>

        <div class="guest-card w-full sm:max-w-md mt-6 px-6 py-4 shadow-md overflow-hidden sm:rounded-lg">
            {{ $slot }}
        </div>
    </div>
</body>

</html>

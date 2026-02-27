{{-- resources/views/layouts/guest.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'BlueSpot') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-white text-gray-900">
    <div class="min-h-screen">

        {{-- Top header (matches screenshot) --}}
        <header class="h-20 flex items-center border-b border-indigo-100/70">
            <div class="max-w-7xl mx-auto w-full px-8 flex items-center justify-between">
                <a href="{{ url('/') }}" class="flex items-center">
                     <x-application-logo class="h-8 w-auto" />
                </a>

                <div class="flex items-center gap-4">
                    <a
                        href="{{ route('login') }}"
                        class="px-6 py-3 rounded-xl border border-indigo-200 text-gray-900 font-semibold hover:bg-gray-50 transition shadow-sm"
                    >
                        Bejelentkezés
                    </a>

                    <a
                        href="{{ route('register') }}"
                        class="px-6 py-3 rounded-xl bg-sky-500 text-white font-semibold hover:bg-sky-600 transition shadow-sm"
                    >
                        Regisztráció
                    </a>
                </div>
            </div>
        </header>

        {{-- Page content --}}
        <main class="max-w-7xl mx-auto px-8">
            {{ $slot }}
        </main>
    </div>

    @stack('scripts')
</body>
</html>
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
<body class="antialiased bg-white text-gray-900" x-data="{ open: false }">
    <div class="min-h-screen">

        {{-- Top header - Full width konténerrel --}}
        <header class="bg-white border-b border-indigo-100/70 sticky top-0 z-50">
            <div class="w-full px-4 sm:px-6 lg:px-8"> {{-- Full width konténer --}}
                <div class="flex justify-between h-20"> {{-- Fix 20-as magasság --}}
                    
                    {{-- Logo szekció --}}
                    <div class="flex">
                        <div class="shrink-0 flex items-center">
                            <a href="{{ url('/') }}" class="flex items-center">
                                <x-application-logo class="h-8 w-auto" />
                            </a>
                        </div>
                    </div>

                    {{-- Desktop Menu --}}
                    <div class="hidden md:flex md:items-center md:ms-6">
                        <div class="flex items-center gap-4">
                            <a href="{{ route('login') }}" class="px-6 py-3 rounded-xl border border-indigo-200 text-gray-900 font-semibold hover:bg-gray-50 transition shadow-sm">
                                Bejelentkezés
                            </a>

                            <a href="{{ route('register') }}" class="px-6 py-3 rounded-xl bg-sky-500 text-white font-semibold hover:bg-sky-600 transition shadow-sm">
                                Regisztráció
                            </a>
                        </div>
                    </div>

                    {{-- Hamburger Gomb - Pontosan ugyanaz a stílus mint a naptárnál --}}
                    <div class="-me-2 flex items-center md:hidden">
                        <button @click="open = ! open" 
                                class="inline-flex items-center justify-center p-2.5 rounded-xl text-gray-900 hover:bg-gray-50 transition-all focus:outline-none">
                            <svg class="h-8 w-8" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </header>

        {{-- Mobile Menu Dropdown --}}
        <div 
            x-show="open" 
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="md:hidden bg-white border-b border-indigo-50 shadow-lg relative z-40"
        >
            <div class="px-6 py-6 space-y-4">
                <a href="{{ route('login') }}" class="block w-full px-6 py-4 rounded-xl border border-indigo-100 text-center font-semibold text-gray-900">
                    Bejelentkezés
                </a>
                <a href="{{ route('register') }}" class="block w-full px-6 py-4 rounded-xl bg-sky-500 text-center font-semibold text-white">
                    Regisztráció
                </a>
            </div>
        </div>

{{-- Page content --}}
        <main class="max-w-7xl mx-auto px-6 lg:px-8 py-8">
            {{ $slot }}
        </main>
    </div>

    @stack('scripts')
</body>
</html>
{{-- resources/views/layouts/navigation.blade.php --}}
<nav x-data="{ open: false }" class="bg-white border-b border-indigo-100/70 sticky top-0 z-50">
    <div class="w-full px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('time.index') }}" class="flex items-center">
                        <x-application-logo class="h-8 w-auto" />
                    </a>
                </div>
            </div>

            {{-- Desktop Menu --}}
            <div class="hidden md:flex md:items-center md:ms-6">
                <div class="flex items-center gap-4">
                    {{-- Felhasználói rész: Avatar + Email --}}
                    <div class="flex items-center gap-3">
                        {{-- Avatar - Teljes kör alakú --}}
                        <div class="w-10 h-10 rounded-full bg-gray-50 border border-gray-100 flex items-center justify-center text-gray-400 shadow-sm overflow-hidden">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 2a5 5 0 00-5 5c0 2.761 2.239 5 5 5s5-2.239 5-5a5 5 0 00-5-5zM3 18a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        
                        {{-- Email cím --}}
                        <div class="text-sm font-medium text-gray-500">
                            {{ Auth::user()->email }}
                        </div>
                    </div>

                    {{-- Elválasztó vonal --}}
                    <div class="h-8 w-[1px] bg-gray-100 mx-1"></div>

                    {{-- Kijelentkezés gomb --}}
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="p-2.5 rounded-xl text-red-500 hover:bg-red-50 transition-all duration-200 group flex items-center justify-center" title="Kijelentkezés">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M9.707 2.409C9 3.036 9 4.183 9 6.476V17.524C9 19.817 9 20.964 9.707 21.591C10.414 22.218 11.495 22.03 13.657 21.653L15.987 21.247C18.381 20.829 19.578 20.62 20.289 19.742C21 18.863 21 17.593 21 15.052V8.948C21 6.408 21 5.138 20.29 4.259C19.578 3.381 18.38 3.172 15.986 2.755L13.658 2.348C11.496 1.971 10.415 1.783 9.708 2.41M12 10.169C12.414 10.169 12.75 10.52 12.75 10.953V13.047C12.75 13.48 12.414 13.831 12 13.831C11.586 13.831 11.25 13.48 11.25 13.047V10.953C11.25 10.52 11.586 10.169 12 10.169Z" fill="#CE2625"/>
                                <path d="M7.547 4.5C5.489 4.503 4.416 4.548 3.732 5.232C3 5.964 3 7.142 3 9.5V14.5C3 16.857 3 18.035 3.732 18.768C4.416 19.451 5.489 19.497 7.547 19.5C7.5 18.876 7.5 18.156 7.5 17.377V6.623C7.5 5.843 7.5 5.123 7.547 4.5Z" fill="#CE2625"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Hamburger Gomb (Mobil) --}}
            <div class="-me-2 flex items-center md:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2.5 rounded-xl text-gray-900 hover:bg-gray-50 transition-all focus:outline-none">
                    <svg class="h-8 w-8" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobil Menü --}}
    <div x-show="open" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="md:hidden bg-white border-t border-gray-100 shadow-xl relative z-40">
        
        <div class="pt-6 pb-8 px-6">
            <div class="flex items-center gap-4 mb-8 p-5 bg-gray-50 rounded-xl border border-gray-100">
                <div class="w-14 h-14 rounded-full bg-white border border-gray-100 flex items-center justify-center text-gray-400 shadow-sm">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <div class="overflow-hidden">
                    <div class="font-bold text-gray-900 text-lg truncate">{{ Auth::user()->name ?? 'Felhasználó' }}</div>
                    <div class="text-sm text-gray-500 truncate">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-3">
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button type="submit" class="w-full rounded-xl py-4 px-6 bg-red-50 text-red-600 font-bold border border-red-100 flex items-center justify-center gap-2 active:scale-95 transition-transform">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                        </svg>
                        Kijelentkezés
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
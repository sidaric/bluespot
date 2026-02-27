<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <div class="flex items-center gap-4">

                    <!-- Avatar -->
                    <div class="w-9 h-9 rounded-full bg-gray-200 flex items-center justify-center">
                        <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 2a5 5 0 00-5 5c0 2.761 2.239 5 5 5s5-2.239 5-5a5 5 0 00-5-5zM3 18a7 7 0 1114 0H3z"
                                clip-rule="evenodd"/>
                        </svg>
                    </div>

                    <!-- Email -->
                    <div class="text-sm font-medium text-gray-700">
                        {{ Auth::user()->email }}
                    </div>

                    <!-- Logout -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button type="submit" class="hover:opacity-80 transition">

                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M9.707 2.409C9 3.036 9 4.183 9 6.476V17.524C9 19.817 9 20.964 9.707 21.591C10.414 22.218 11.495 22.03 13.657 21.653L15.987 21.247C18.381 20.829 19.578 20.62 20.289 19.742C21 18.863 21 17.593 21 15.052V8.948C21 6.408 21 5.138 20.29 4.259C19.578 3.381 18.38 3.172 15.986 2.755L13.658 2.348C11.496 1.971 10.415 1.783 9.708 2.41M12 10.169C12.414 10.169 12.75 10.52 12.75 10.953V13.047C12.75 13.48 12.414 13.831 12 13.831C11.586 13.831 11.25 13.48 11.25 13.047V10.953C11.25 10.52 11.586 10.169 12 10.169Z"
                                fill="#CE2625"/>
                                <path
                                d="M7.547 4.5C5.489 4.503 4.416 4.548 3.732 5.232C3 5.964 3 7.142 3 9.5V14.5C3 16.857 3 18.035 3.732 18.768C4.416 19.451 5.489 19.497 7.547 19.5C7.5 18.876 7.5 18.156 7.5 17.377V6.623C7.5 5.843 7.5 5.123 7.547 4.5Z"
                                fill="#CE2625"/>
                                </svg>

                        </button>
                    </form>

                </div>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>

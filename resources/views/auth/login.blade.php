{{-- resources/views/auth/login.blade.php --}}
<x-guest-layout>
    <div class="py-24">
        <div class="max-w-xl mx-auto text-center">
            <h1 class="text-4xl font-semibold tracking-tight">Bejelentkezés</h1>

            <div class="mt-10 max-w-md mx-auto">
                {{-- Session status / errors --}}
                @if (session('status'))
                    <div class="mb-6 text-sm text-green-700 bg-green-50 border border-green-100 rounded-xl px-4 py-3 text-left">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 text-sm text-red-700 bg-red-50 border border-red-100 rounded-xl px-4 py-3 text-left">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    {{-- Email --}}
                    <div>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="Email"
                            class="w-full h-14 px-5 rounded-2xl bg-gray-100 border border-transparent focus:border-gray-200 focus:ring-2 focus:ring-gray-900/10 outline-none text-base"
                        />
                    </div>

                    {{-- Password --}}
                    <div>
                        <div class="relative">
                            <input
                                id="password"
                                type="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="Jelszó"
                                class="w-full h-14 px-5 pr-14 rounded-2xl bg-gray-100 border border-transparent focus:border-gray-200 focus:ring-2 focus:ring-gray-900/10 outline-none text-base"
                            />

                            {{-- Toggle visibility --}}
                            <button
                                type="button"
                                id="togglePassword"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-600 hover:text-gray-900 transition"
                                aria-label="Jelszó megjelenítése"
                            >
                                {{-- eye (default) --}}
                                <svg id="iconEye" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" stroke="currentColor" stroke-width="1.8"/>
                                    <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="1.8"/>
                                </svg>
                                {{-- eye-off (hidden initially) --}}
                                <svg id="iconEyeOff" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" viewBox="0 0 24 24" fill="none">
                                    <path d="M3 12s3.5-7 9-7c2.2 0 4.1.8 5.7 2" stroke="currentColor" stroke-width="1.8"/>
                                    <path d="M21 12s-3.5 7-9 7c-2.2 0-4.1-.8-5.7-2" stroke="currentColor" stroke-width="1.8"/>
                                    <path d="M10 10.2a3 3 0 0 0 3.8 3.8" stroke="currentColor" stroke-width="1.8"/>
                                    <path d="M2 2l20 20" stroke="currentColor" stroke-width="1.8"/>
                                </svg>
                            </button>
                        </div>

                        <div class="mt-3 text-right">
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-sm text-gray-400 hover:text-gray-600 transition">
                                    Elfelejtett jelszó ?
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="pt-2">
                        <button
                            type="submit"
                            class="w-full h-16 rounded-2xl bg-gray-900 text-white font-semibold text-base shadow-[0_20px_45px_-20px_rgba(90,80,255,0.45)] hover:bg-gray-800 transition"
                        >
                            Bejelentkezés
                        </button>
                    </div>

                    {{-- Register link --}}
                    <div class="pt-2">
                        <a href="{{ route('register') }}" class="block text-center font-semibold text-gray-900 hover:opacity-80 transition">
                            Regisztráció
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Password visibility toggle
            (function () {
                const btn = document.getElementById('togglePassword');
                const input = document.getElementById('password');
                const eye = document.getElementById('iconEye');
                const eyeOff = document.getElementById('iconEyeOff');

                if (!btn || !input || !eye || !eyeOff) return;

                btn.addEventListener('click', () => {
                    const isHidden = input.type === 'password';
                    input.type = isHidden ? 'text' : 'password';
                    eye.classList.toggle('hidden', isHidden);
                    eyeOff.classList.toggle('hidden', !isHidden);
                });
            })();
        </script>
    @endpush
</x-guest-layout>
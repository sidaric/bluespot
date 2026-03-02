<x-guest-layout>
    <div class="py-24">
        <div class="max-w-xl mx-auto text-center">
            <h1 class="text-4xl font-semibold tracking-tight">
                Regisztráció
            </h1>

            <div class="mt-10 max-w-md mx-auto">
                {{-- Errors --}}
                @if ($errors->any())
                    <div class="mb-6 text-sm text-red-700 bg-red-50 border border-red-100 rounded-xl px-4 py-3 text-left">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" class="space-y-6">
                    @csrf

                    {{-- Email --}}
                    <div>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
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
                                placeholder="Jelszó"
                                class="w-full h-14 px-5 pr-14 rounded-2xl bg-gray-100 border border-transparent focus:border-gray-200 focus:ring-2 focus:ring-gray-900/10 outline-none text-base"
                            />

                            <button
                                type="button"
                                id="togglePassword"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-600 hover:text-gray-900 transition"
                            >
                                <svg id="iconEye1" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" stroke="currentColor" stroke-width="1.8"/>
                                    <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="1.8"/>
                                </svg>

                                <svg id="iconEyeOff1" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" viewBox="0 0 24 24" fill="none">
                                    <path d="M3 12s3.5-7 9-7c2.2 0 4.1.8 5.7 2" stroke="currentColor" stroke-width="1.8"/>
                                    <path d="M21 12s-3.5 7-9 7c-2.2 0-4.1-.8-5.7-2" stroke="currentColor" stroke-width="1.8"/>
                                    <path d="M10 10.2a3 3 0 0 0 3.8 3.8" stroke="currentColor" stroke-width="1.8"/>
                                    <path d="M2 2l20 20" stroke="currentColor" stroke-width="1.8"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Confirm Password --}}
                    <div>
                        <div class="relative">
                            <input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                required
                                placeholder="Jelszó megerősítése"
                                class="w-full h-14 px-5 pr-14 rounded-2xl bg-gray-100 border border-transparent focus:border-gray-200 focus:ring-2 focus:ring-gray-900/10 outline-none text-base"
                            />

                            <button
                                type="button"
                                id="togglePassword2"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-600 hover:text-gray-900 transition"
                            >
                                <svg id="iconEye2" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" stroke="currentColor" stroke-width="1.8"/>
                                    <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="1.8"/>
                                </svg>

                                <svg id="iconEyeOff2" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" viewBox="0 0 24 24" fill="none">
                                    <path d="M3 12s3.5-7 9-7c2.2 0 4.1.8 5.7 2" stroke="currentColor" stroke-width="1.8"/>
                                    <path d="M21 12s-3.5 7-9 7c-2.2 0-4.1-.8-5.7-2" stroke="currentColor" stroke-width="1.8"/>
                                    <path d="M10 10.2a3 3 0 0 0 3.8 3.8" stroke="currentColor" stroke-width="1.8"/>
                                    <path d="M2 2l20 20" stroke="currentColor" stroke-width="1.8"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="pt-2">
                        <button
                            type="submit"
                            class="w-full h-16 rounded-2xl bg-gray-900 text-white font-semibold text-base shadow-[0_20px_45px_-20px_rgba(0,0,0,0.3)] hover:bg-gray-800 transition"
                        >
                            Regisztráció
                        </button>
                    </div>

                    {{-- Login link --}}
                    <div class="pt-2">
                        <a href="{{ route('login') }}" class="block text-center font-semibold text-gray-900 hover:opacity-80 transition">
                            Bejelentkezés
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const setupPasswordToggle = (btnId, inputId, eyeId, eyeOffId) => {
            const btn = document.getElementById(btnId);
            const input = document.getElementById(inputId);
            const eye = document.getElementById(eyeId);
            const eyeOff = document.getElementById(eyeOffId);

            btn.addEventListener('click', () => {
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                eye.classList.toggle('hidden', isPassword);
                eyeOff.classList.toggle('hidden', !isPassword);
            });
        };

        setupPasswordToggle('togglePassword', 'password', 'iconEye1', 'iconEyeOff1');
        setupPasswordToggle('togglePassword2', 'password_confirmation', 'iconEye2', 'iconEyeOff2');
    </script>
</x-guest-layout>
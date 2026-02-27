{{-- resources/views/auth/forgot-password.blade.php --}}
<x-guest-layout>

    <div class="py-24">
        <div class="max-w-xl mx-auto text-center">

            <h1 class="text-4xl font-semibold tracking-tight">
                Elfelejtett jelszó
            </h1>

            <div class="mt-10 max-w-md mx-auto">

                {{-- Status --}}
                @if (session('status'))
                    <div class="mb-6 text-sm text-green-700 bg-green-50 border border-green-100 rounded-xl px-4 py-3 text-left">
                        {{ session('status') }}
                    </div>
                @endif

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


                <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                    @csrf

                    {{-- Email --}}
                    <div>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            placeholder="Email cím"
                            class="w-full h-14 px-5 rounded-2xl bg-gray-100 border border-transparent focus:border-gray-200 focus:ring-2 focus:ring-gray-900/10 outline-none text-base"
                        />
                    </div>

                    {{-- Submit --}}
                    <div class="pt-2">
                        <button
                            type="submit"
                            class="w-full h-16 rounded-2xl bg-gray-900 text-white font-semibold text-base shadow-[0_20px_45px_-20px_rgba(90,80,255,0.45)] hover:bg-gray-800 transition"
                        >
                            Jelszó visszaállítása
                        </button>
                    </div>

                    {{-- Back to login --}}
                    <div class="pt-2">
                        <a
                            href="{{ route('login') }}"
                            class="block text-center font-semibold text-gray-900 hover:opacity-80 transition"
                        >
                            Bejelentkezés
                        </a>
                    </div>

                </form>

            </div>
        </div>
    </div>

</x-guest-layout>
<x-app-layout>
    <div class="max-w-5xl mx-auto p-6 space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold">Időnyilvántartás</h1>
                <p class="text-sm text-gray-600">Havi bontás, napi bejegyzések.</p>
            </div>

            <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded border">Dashboard</a>
        </div>

        <div class="rounded border p-6">
            <div class="text-gray-600 text-sm">
                (Következő lépésben jön a dinamikus lista/naptár, AJAX és toast.)
            </div>
        </div>
    </div>
</x-app-layout>

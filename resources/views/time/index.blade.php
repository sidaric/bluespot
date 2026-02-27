<x-app-layout>
    <div class="max-w-5xl mx-auto p-6 space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold">Időnyilvántartás</h1>
                <p class="text-sm text-gray-600">Havi bontás, napi bejegyzések.</p>
            </div>

            <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded border">Dashboard</a>
        </div>



<div class="rounded border p-4 flex items-center justify-between">
    <div>
        <div class="text-sm text-gray-600">Kiválasztott hónap</div>
        <div class="font-semibold" id="monthLabel">—</div>
    </div>

    <div class="text-right">
        <div class="text-sm text-gray-600">Havi összes óra</div>
        <div class="text-xl font-semibold" id="totalHours">—</div>
    </div>
</div>

<div class="rounded border">
    <div class="p-4 border-b font-medium">Bejegyzések</div>
    <div id="entries" class="divide-y"></div>
</div>

@vite(['resources/js/time.js'])


    </div>

</x-app-layout>

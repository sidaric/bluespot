<x-app-layout>
    <div class="max-w-5xl mx-auto p-6 space-y-6">

<div class="flex items-center gap-2">
    <button id="prevMonth" class="px-3 py-2 rounded border">◀</button>

    <input id="monthInput" type="month" class="px-3 py-2 rounded border" />

    <button id="nextMonth" class="px-3 py-2 rounded border">▶</button>
</div>

        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold">Időnyilvántartás</h1>
   	 <button
        		id="addEntryBtn"
        		class="px-4 py-2 bg-black text-white rounded">
       		 + Új bejegyzés
   	 </button>

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
<div id="modalBackdrop" class="fixed inset-0 bg-black/40 hidden"></div>

<div id="entryModal"
     class="fixed inset-0 hidden flex items-center justify-center">

    <div class="bg-white rounded shadow p-6 w-full max-w-md space-y-4">

        <div class="text-lg font-semibold">
            Új bejegyzés
        </div>

        <div>
            <label class="text-sm">Dátum</label>

            <input
                id="entryDate"
                type="date"
                class="w-full border rounded px-3 py-2">
        </div>

        <div>
            <label class="text-sm">Óra</label>

            <input
                id="entryHours"
                type="number"
                step="0.25"
                min="0.25"
                class="w-full border rounded px-3 py-2">
        </div>

        <div>
            <label class="text-sm">Leírás</label>

            <textarea
                id="entryDescription"
                class="w-full border rounded px-3 py-2"></textarea>
        </div>

        <div class="flex justify-end gap-2">

            <button
                id="cancelBtn"
                class="px-4 py-2 border rounded">
                Mégse
            </button>

            <button
                id="saveBtn"
                class="px-4 py-2 bg-black text-white rounded">
                Mentés
            </button>

        </div>

    </div>

<div
    id="toast"
    class="fixed bottom-6 right-6 hidden px-4 py-3 rounded shadow bg-black text-white max-w-sm">
</div>

</div>
</x-app-layout>

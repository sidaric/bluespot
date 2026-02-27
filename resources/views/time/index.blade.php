{{-- resources/views/time/index.blade.php --}}
<x-app-layout>
    <div class="min-h-[calc(100vh-64px)] bg-gray-50">
        <div class="max-w-6xl mx-auto px-6 py-10 space-y-6">

            {{-- Header --}}
            <div class="flex items-start justify-between gap-6">
                <div class="space-y-3">
                    <h1 class="text-2xl font-semibold text-gray-900">Időnyilvántartás</h1>


                </div>
            </div>

            {{-- Month navigation --}}
            <div class="flex items-center gap-3">

                <button
                    id="prevMonth"
                    type="button"
                    class="h-10 w-10 grid place-items-center rounded-lg border border-gray-200 bg-white hover:bg-gray-50 transition shadow-sm"
                    aria-label="Előző hónap"
                    title="Előző hónap"
                >
                    {{-- minimal left arrow --}}
                    <svg viewBox="0 0 24 24" class="w-5 h-5 text-gray-700" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M15 6L9 12L15 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>

                <div class="relative">
                    <input
                        id="monthInput"
                        type="text"
                        inputmode="none"
                        class="h-10 w-[190px] rounded-lg border border-gray-200 bg-white px-3 pr-10 text-gray-900 shadow-sm cursor-pointer"
                        placeholder="Válassz hónapot"
                    />
                    <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <svg viewBox="0 0 24 24" class="w-5 h-5" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M7 10l5 5 5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </div>

                <button
                    id="nextMonth"
                    type="button"
                    class="h-10 w-10 grid place-items-center rounded-lg border border-gray-200 bg-white hover:bg-gray-50 transition shadow-sm"
                    aria-label="Következő hónap"
                    title="Következő hónap"
                >
                    {{-- minimal right arrow --}}
                    <svg viewBox="0 0 24 24" class="w-5 h-5 text-gray-700" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 6L15 12L9 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>

                <div class="ml-auto flex items-center gap-8">
                    <div class="text-sm text-gray-600">
                        Kiválasztott időszak:
                        <span class="font-semibold text-gray-900" id="monthLabel">—</span>
                    </div>

                    <div class="text-sm text-gray-600">
                        Havi összes óra:
                        <span class="font-semibold text-gray-900" id="totalHours">—</span>
                    </div>

                    <div class="flex items-center gap-3">
                        <button
                            id="addEntryBtn"
                            type="button"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-gray-800 transition shadow-sm"
                        >
                            <span class="text-lg leading-none">+</span>
                            <span>Új óra</span>
                        </button>
                    </div>
                </div>

            </div>

            {{-- Timesheet --}}
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">

                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-[980px] w-full table-fixed border-separate border-spacing-0">
                        <colgroup>
                            {{-- 5 equal weekday columns --}}
                            <col class="w-[18%]">
                            <col class="w-[18%]">
                            <col class="w-[18%]">
                            <col class="w-[18%]">
                            <col class="w-[18%]">
                            {{-- totals --}}
                            <col class="w-[140px]">
                            <col class="w-[90px]">
                        </colgroup>

                        <thead class="bg-gray-50 text-sm text-gray-600">
                        <tr class="[&>th]:px-4 [&>th]:py-3 [&>th]:text-left [&>th]:font-medium">
                            <th class="border-b border-gray-200 text-[12px] text-[#202224]">Hétfő</th>
                            <th class="border-b border-gray-200 text-[12px] text-[#202224]">Kedd</th>
                            <th class="border-b border-gray-200 text-[12px] text-[#202224]">Szerda</th>
                            <th class="border-b border-gray-200 text-[12px] text-[#202224]">Csütörtök</th>
                            <th class="border-b border-gray-200 text-[12px] text-[#202224]">Péntek</th>
                            <th class="border-b border-gray-200 text-[12px] text-[#202224]">Összeg</th>
                            <th class="border-b border-gray-200 text-[12px] text-[#202224]">Hét</th>
                        </tr>
                        </thead>

                        <tbody id="timesheetBody" class="text-sm text-gray-800">
                            <tr>
                                <td class="px-4 py-10 text-sm text-gray-600 border-b border-gray-200" colspan="7">
                                    Betöltés...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            @vite(['resources/js/time.js'])
        </div>
    </div>

    {{-- Backdrop --}}
    <div id="modalBackdrop" class="fixed inset-0 bg-black/40 hidden"></div>

    {{-- Modal --}}
    <div id="entryModal" class="fixed inset-0 hidden flex items-center justify-center p-4">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-gray-200 p-6 space-y-4">
            <div class="text-lg font-semibold text-gray-900" id="modalTitle">Új bejegyzés</div>

            <input id="entryId" type="hidden" value="">

            <div class="space-y-1">
                <label class="text-sm text-gray-700">Dátum</label>
                <input
                    id="entryDate"
                    type="text"
                    inputmode="none"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gray-900/10"
                    placeholder="YYYY-MM-DD"
                >
                <div class="text-xs text-gray-500">Csak hétköznap (H–P) választható.</div>
            </div>

            <div class="space-y-1">
                <label class="text-sm text-gray-700">Óra</label>
                <input
                    id="entryHours"
                    type="number"
                    step="0.25"
                    min="0.25"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gray-900/10"
                >
            </div>

            <div class="space-y-1">
                <label class="text-sm text-gray-700">Leírás</label>
                <textarea
                    id="entryDescription"
                    rows="4"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gray-900/10"
                ></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button
                    id="cancelBtn"
                    type="button"
                    class="px-4 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition"
                >Mégse</button>

                <button
                    id="deleteBtn"
                    type="button"
                    class="px-4 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition hidden"
                >Törlés</button>

                <button
                    id="saveBtn"
                    type="button"
                    class="px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-gray-800 transition"
                >Mentés</button>
            </div>
        </div>

        {{-- Toast --}}
        <div
            id="toast"
            class="fixed bottom-6 right-6 hidden px-4 py-3 rounded-lg shadow bg-gray-900 text-white max-w-sm"
        ></div>
    </div>
</x-app-layout>
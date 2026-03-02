{{-- resources/views/time/index.blade.php --}}
<x-app-layout>
    <div class="min-h-[calc(100vh-80px)] bg-gray-50/50 font-sans">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-10 space-y-6">

            {{-- Header szekció --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Időnyilvántartás</h1>
                    <p class="text-sm text-gray-500 mt-1">Kövesd nyomon a ledolgozott óráidat.</p>
                </div>
                
                {{-- Új óra gomb --}}
                <div class="flex justify-end">
                    <button
                        id="addEntryBtn"
                        type="button"
                        class="inline-flex items-center gap-2 px-6 py-3.5 rounded-[8px] bg-[#2B2A2A] text-white font-bold hover:bg-black transition-all shadow-lg shadow-gray-200 active:scale-95"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Új óra</span>
                    </button>
                </div>
            </div>

            {{-- Navigációs és Statisztikai sáv --}}
            <div class="bg-white p-4 lg:p-2 rounded-xl border border-gray-100 shadow-sm flex flex-col lg:flex-row lg:items-center gap-4">
                
                {{-- Időszak választó --}}
                <div class="flex items-center justify-between sm:justify-start gap-2 bg-gray-50 lg:bg-transparent p-2 rounded-xl lg:p-0">
                    <button
                        id="prevMonth"
                        type="button"
                        class="h-11 w-11 grid place-items-center rounded-xl border border-gray-200 bg-white hover:bg-gray-50 transition shadow-sm active:scale-90"
                    >
                        <svg viewBox="0 0 24 24" class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M15 18l-6-6 6-6"/>
                        </svg>
                    </button>

                    <div class="relative flex-1 sm:flex-none">
                        <input
                            id="monthInput"
                            type="text"
                            inputmode="none"
                            class="h-11 w-full sm:w-[200px] rounded-xl border-gray-200 bg-white px-4 pr-10 text-gray-900 font-bold focus:ring-2 focus:ring-[#3CA8F0]/20 focus:border-[#3CA8F0] transition-all cursor-pointer text-sm outline-none"
                            placeholder="Válassz hónapot"
                        />
                        <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                            <svg viewBox="0 0 24 24" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M7 10l5 5 5-5"/>
                            </svg>
                        </div>
                    </div>

                    <button
                        id="nextMonth"
                        type="button"
                        class="h-11 w-11 grid place-items-center rounded-xl border border-gray-200 bg-white hover:bg-gray-50 transition shadow-sm active:scale-90"
                    >
                        <svg viewBox="0 0 24 24" class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 18l6-6-6-6"/>
                        </svg>
                    </button>
                </div>

                {{-- Statisztikák --}}
                <div class="grid grid-cols-2 gap-4 lg:flex lg:items-center lg:gap-8 lg:ml-auto px-4 py-2 lg:py-0">
                    <div class="flex flex-col">
                        <span class="text-[10px] uppercase tracking-widest text-gray-400 font-black">Időszak</span>
                        <span class="font-bold text-gray-900 text-sm lg:text-base" id="monthLabel">—</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[10px] uppercase tracking-widest text-gray-400 font-black">Havi összesen</span>
                        <span class="font-bold text-[#3CA8F0] text-sm lg:text-base" id="totalHours">—</span>
                    </div>
                </div>
            </div>

            {{-- Táblázat Konténer --}}
            <div class="rounded-xl border border-gray-100 bg-white shadow-sm overflow-hidden">
                <div class="overflow-x-auto scrollbar-thin scrollbar-thumb-gray-200 py-6 px-1">
                    <table class="min-w-[1000px] w-full table-fixed border-separate border-spacing-0">
                            <thead>
                                <tr class="bg-[#EAECF0] [&>th]:px-6 [&>th]:py-4 [&>th]:text-left">
                                    {{-- Bal oldal: fent és lent kerekítve --}}
                                    <th class="rounded-tl-[8px] rounded-bl-[8px] border-b border-gray-100 text-[12px] text-[#202224] tracking-widest w-[16%]">Hétfő</th>
                                    
                                    <th class="border-b border-gray-100 text-[12px] text-[#202224] tracking-widest w-[16%]">Kedd</th>
                                    <th class="border-b border-gray-100 text-[12px] text-[#202224] tracking-widest w-[16%]">Szerda</th>
                                    <th class="border-b border-gray-100 text-[12px] text-[#202224] tracking-widest w-[16%]">Csütörtök</th>
                                    <th class="border-b border-gray-100 text-[12px] text-[#202224] tracking-widest w-[16%]">Péntek</th>
                                    <th class="border-b border-gray-100 text-[12px] text-[#202224] tracking-widest w-[120px]">Összeg</th>
                                    
                                    {{-- Jobb oldal: fent és lent kerekítve --}}
                                    <th class="rounded-tr-[8px] rounded-br-[8px] border-b border-gray-100 text-[12px] text-[#202224] tracking-widest w-[70px] text-center">Hét</th>
                                </tr>
                            </thead>
                        <tbody id="timesheetBody" class="text-sm text-gray-800">
                            <tr>
                                <td class="px-6 py-24 text-center text-gray-400" colspan="7">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="w-10 h-10 border-4 border-[#3CA8F0]/20 border-t-[#3CA8F0] rounded-full animate-spin"></div>
                                        <span class="font-medium text-gray-500">Adatok szinkronizálása...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            @vite(['resources/js/time.js'])
        </div>
    </div>

    {{-- Modal Rendszer --}}
    <div id="modalBackdrop" class="fixed inset-0 bg-gray-900/40 backdrop-blur-md hidden z-[60] transition-opacity duration-300"></div>

    <div id="entryModal" class="fixed inset-0 hidden flex items-center justify-center p-4 z-[70]">
        <div class="w-full max-w-md bg-white rounded-xl shadow-2xl border border-gray-100 p-8 space-y-6 transform transition-all relative">
            
            {{-- Bezárás gomb (X) --}}
            <button onclick="document.getElementById('cancelBtn').click()" class="absolute right-6 top-6 text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            <h2 class="text-xl font-bold text-gray-900" id="modalTitle">Új bejegyzés</h2>

            <input id="entryId" type="hidden" value="">

            <div class="space-y-5">
                {{-- Dátum --}}
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-gray-700 ml-1">Dátum</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <input id="entryDate" type="text" inputmode="none" class="w-full border border-gray-200 bg-white rounded-xl pl-11 pr-4 py-3.5 focus:ring-2 focus:ring-[#3CA8F0]/20 focus:border-[#3CA8F0] outline-none transition-all placeholder:text-gray-400 text-gray-900" placeholder="Choose date">
                    </div>
                </div>

                {{-- Óra --}}
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-gray-700 ml-1">Óra</label>
                    <div class="relative">
                        <div id="hourIconWrapper" class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <input id="entryHours" type="number" step="0.25" class="w-full border border-gray-200 bg-white rounded-xl pl-11 pr-4 py-3.5 focus:ring-2 focus:ring-[#3CA8F0]/20 focus:border-[#3CA8F0] outline-none transition-all text-gray-900" placeholder="8.0">
                    </div>
                    <p id="hourError" class="hidden mt-1.5 text-xs font-medium text-[#CE2625]">Hibás formátum</p>
                </div>

                {{-- Megjegyzés --}}
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-gray-700 ml-1">Megjegyzés</label>
                    <textarea id="entryDescription" rows="3" class="w-full border border-gray-200 bg-white rounded-xl px-4 py-3.5 focus:ring-2 focus:ring-[#3CA8F0]/20 focus:border-[#3CA8F0] outline-none transition-all resize-none placeholder:text-gray-400 text-gray-900" placeholder="Megjegyzés"></textarea>
                </div>
            </div>

            {{-- Alsó akciógombok --}}
            <div class="grid grid-cols-2 gap-4 pt-2">
                <button id="saveBtn" type="button" class="bg-[#2B2A2A] hover:bg-black text-white font-bold py-4 rounded-xl transition-all active:scale-95 shadow-sm">
                    Mentés
                </button>
                <button id="cancelBtn" type="button" class="bg-[#CE2625] hover:bg-[#B0201F] text-white font-bold py-4 rounded-xl transition-all active:scale-95 shadow-sm">
                    Mégse
                </button>
                {{-- Törlés gomb (szerkesztésnél jelenik meg) --}}
                <button id="deleteBtn" type="button" class="col-span-2 py-2 text-[#CE2625] font-bold text-sm hover:underline hidden">
                    Bejegyzés törlése
                </button>
            </div>
        </div>
    </div>

    {{-- Értesítési rendszer (Toasts) --}}
    <div id="toastContainer" class="fixed top-8 right-8 z-[100] space-y-4 pointer-events-none">
        {{-- Sikeres Toast --}}
        <div id="toastSuccess" class="hidden pointer-events-auto flex items-center gap-3 bg-[#00C851] text-white px-6 py-4 rounded-xl shadow-xl min-w-[320px] animate-slide-in">
            <div class="bg-white rounded-full p-1 flex-shrink-0">
                <svg class="w-4 h-4 text-[#00C851]" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
            </div>
            <span class="font-bold flex-1">Az adatok mentése sikeres!</span>
            <button onclick="this.parentElement.classList.add('hidden')" class="hover:opacity-70 transition-opacity">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-width="2.5"/></svg>
            </button>
        </div>

        {{-- Hiba Toast --}}
        <div id="toastError" class="hidden pointer-events-auto flex items-center gap-3 bg-[#CE2625] text-white px-6 py-4 rounded-xl shadow-xl min-w-[320px] animate-slide-in">
            <div class="bg-white rounded-full p-1 flex-shrink-0">
                <svg class="w-4 h-4 text-[#CE2625]" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </div>
            <span class="font-bold flex-1">Az adatok mentése sikertelen!</span>
            <button onclick="this.parentElement.classList.add('hidden')" class="hover:opacity-70 transition-opacity">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-width="2.5"/></svg>
            </button>
        </div>
    </div>
</x-app-layout>
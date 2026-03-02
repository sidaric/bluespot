import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";
import monthSelectPlugin from "flatpickr/dist/plugins/monthSelect/index";
import "flatpickr/dist/plugins/monthSelect/style.css";

// ---- Segédfüggvények ----
function minutesToHoursString(totalMinutes) {
    const hours = (totalMinutes || 0) / 60;
    return Number(hours.toFixed(2)).toString();
}

function escapeHtml(str) {
    return String(str ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
}

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";
}


function showToast(message, isError = false) {
    const elToast = document.getElementById("toast");
    if (!elToast) return;

    const successIcon = `<svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>`;
    const errorIcon = `<svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>`;
    const closeIcon = `<svg class="w-5 h-5 text-white/50 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>`;

    // Tartalom beállítása (innerHTML-t használunk a korábbi textContent helyett)
    elToast.innerHTML = `
        <div class="flex items-center gap-4 min-w-[300px] w-full">
            <div class="flex-shrink-0 bg-white/20 p-1.5 rounded-full">${isError ? errorIcon : successIcon}</div>
            <div class="flex-grow font-bold text-white text-[15px]">${message}</div>
            <button id="closeToastBtn" class="group p-1 -mr-1 outline-none">
                ${closeIcon}
            </button>
        </div>
    `;

    // Stílusok és megjelenítés
    elToast.classList.remove("hidden");
    elToast.style.display = "flex";
    elToast.style.backgroundColor = isError ? "#DC2626" : "#22C55E"; 

    // Bezárás gomb funkció (közvetlen eseménykezelővel)
    const btnClose = elToast.querySelector("#closeToastBtn");
    if (btnClose) {
        btnClose.onclick = () => {
            elToast.classList.add("hidden");
            elToast.style.display = "none";
        };
    }

    // Automatikus eltűnés (4 másodperc után)
    clearTimeout(window.__toastTimer);
    window.__toastTimer = setTimeout(() => { 
        elToast.classList.add("hidden");
        elToast.style.display = "none";
    }, 4000);
}


function hoursToMinutes(hours) {
    const val = Number(hours);
    return (!Number.isFinite(val) || val <= 0) ? 0 : Math.round(val * 60);
}

function pad2(n) { return String(n).padStart(2, "0"); }
function toISODate(d) { return `${d.getFullYear()}-${pad2(d.getMonth() + 1)}-${pad2(d.getDate())}`; }

function parseISODate(yyyy_mm_dd) {
    const [y, m, d] = String(yyyy_mm_dd).split("-").map(Number);
    const dt = new Date(y, (m || 1) - 1, d || 1);
    dt.setHours(0, 0, 0, 0);
    return dt;
}

function getISODayIndexMon0(date) {
    const day = date.getDay();
    return (day === 0 ? 7 : day) - 1;
}

function getISOWeekNumber(date) {
    const d = new Date(date.getTime());
    d.setHours(0, 0, 0, 0);
    d.setDate(d.getDate() + 3 - ((d.getDay() + 6) % 7));
    const week1 = new Date(d.getFullYear(), 0, 4);
    return (1 + Math.round(((d.getTime() - week1.getTime()) / 86400000 - 3 + ((week1.getDay() + 6) % 7)) / 7));
}

function getWeekStartMonday(date) {
    const d = new Date(date.getTime());
    d.setDate(d.getDate() - getISODayIndexMon0(d));
    d.setHours(0, 0, 0, 0);
    return d;
}

function addDays(date, days) {
    const d = new Date(date.getTime());
    d.setDate(d.getDate() + days);
    d.setHours(0, 0, 0, 0);
    return d;
}

function sameMonth(date, y, m1to12) {
    return date.getFullYear() === y && date.getMonth() === (m1to12 - 1);
}

function formatEntryHours(minutes) { return `${minutesToHoursString(minutes)} óra`; }
function formatCellDateMMDD(date) { return `${pad2(date.getMonth() + 1)}. ${pad2(date.getDate())}.`; }
function getMonthNow() { const d = new Date(); return `${d.getFullYear()}-${pad2(d.getMonth() + 1)}`; }

function shiftMonth(month, delta) {
    const [y, m] = (month || getMonthNow()).split("-").map(Number);
    const d = new Date(y, (m || 1) - 1, 1);
    d.setMonth(d.getMonth() + delta);
    return `${d.getFullYear()}-${pad2(d.getMonth() + 1)}`;
}

function getMonthBounds(month) {
    const [y, m] = String(month).split("-").map(Number);
    const first = new Date(y, (m || 1) - 1, 1);
    const last = new Date(y, (m || 1), 0);
    first.setHours(0, 0, 0, 0); last.setHours(0, 0, 0, 0);
    return { y, m, first, last };
}

function isWeekendDate(dateObj) {
    const day = dateObj.getDay();
    return day === 0 || day === 6;
}

function firstWeekStartForMonth(firstDay) {
    const d = new Date(firstDay.getTime());
    const dow = d.getDay();
    if (dow === 6) return addDays(d, 2);
    if (dow === 0) return addDays(d, 1);
    return getWeekStartMonday(d);
}

// ---- State ----
let currentMonth = null;
let lastEntriesById = new Map();
let fpDate = null, fpMonth = null;

const el = (id) => document.getElementById(id);
const openModal = () => { el("entryModal")?.classList.remove("hidden"); el("modalBackdrop")?.classList.remove("hidden"); };
const closeModal = () => { el("entryModal")?.classList.add("hidden"); el("modalBackdrop")?.classList.add("hidden"); };

const monthNamesHU = ["Január", "Február", "Március", "Április", "Május", "Június", "Július", "Augusztus", "Szeptember", "Október", "November", "December"];

// ---- Render ----
function render(data) {
    const month = data?.month ?? currentMonth ?? getMonthNow();
    const days = data?.days ?? {};
    const entriesByDate = new Map();
    lastEntriesById.clear();

    Object.keys(days).forEach(dateKey => {
        entriesByDate.set(dateKey, days[dateKey]);
        days[dateKey].forEach(e => lastEntriesById.set(String(e.id), e));
    });

    const { y, m, first, last } = getMonthBounds(month);
    let monthTotalMinutes = 0;
    entriesByDate.forEach((arr, dateKey) => {
        const d = parseISODate(dateKey);
        if (sameMonth(d, y, m) && !isWeekendDate(d)) arr.forEach(e => monthTotalMinutes += Number(e.minutes || 0));
    });

    if (el("monthLabel")) el("monthLabel").textContent = `${y}. ${monthNamesHU[m - 1]}`;
    if (el("totalHours")) el("totalHours").textContent = `${minutesToHoursString(monthTotalMinutes)} óra`;

    const tbody = el("timesheetBody");
    if (!tbody) return;

    let weekStart = firstWeekStartForMonth(first);
    const tableEndWeekStart = getWeekStartMonday(last);
    let html = "";

    while (weekStart <= tableEndWeekStart) {
        const weekNumber = getISOWeekNumber(weekStart);
        let weekTotalMinutes = 0, dayCellsHtml = "";

        for (let i = 0; i < 5; i++) {
            const dayDate = addDays(weekStart, i);
            const dayIso = toISODate(dayDate);
            const inMonth = sameMonth(dayDate, y, m);
            const dayEntries = inMonth ? (entriesByDate.get(dayIso) || []) : [];
            if (inMonth) dayEntries.forEach(e => weekTotalMinutes += Number(e.minutes || 0));

            const entriesHtml = dayEntries.map(e => `
              <button type="button" data-entry-id="${e.id}" 
                  class="w-full text-left bg-white p-3 hover:bg-gray-50 transition-all block overflow-hidden group">
                  <div class="font-bold text-gray-900 text-[15px] group-hover:text-[#3CA8F0] transition-colors">${formatEntryHours(e.minutes)}</div>
                  <div class="text-[13px] text-gray-500 truncate mt-0.5 font-medium">${escapeHtml(e.description || 'Nincs leírás')}</div>
              </button>
            `).join("");

            dayCellsHtml += `
                <td class="border-b border-[#EAECF0] py-8 px-3 align-top relative ${inMonth ? 'bg-white' : 'bg-gray-50/50'}">
                    <div class="min-h-[80px]">${entriesHtml}</div>
                    <div class="absolute bottom-2 right-2 text-[11px] font-bold ${inMonth ? 'text-gray-400' : 'text-gray-300'} pointer-events-none uppercase tracking-tighter">
                        ${formatCellDateMMDD(dayDate)}
                    </div>
                </td>
            `;
        }

        html += `<tr>${dayCellsHtml}
            <td class="border border-gray-100 bg-gray-50/30 p-4 align-middle font-bold text-gray-900 text-center">${minutesToHoursString(weekTotalMinutes)} óra</td>
            <td class="border border-gray-100 p-4 align-middle text-center font-bold text-gray-300 text-xs">${weekNumber}</td>
        </tr>`;
        weekStart = addDays(weekStart, 7);
    }
    tbody.innerHTML = html;

    tbody.querySelectorAll("button[data-entry-id]").forEach(btn => {
        btn.addEventListener("click", () => {
            const entry = lastEntriesById.get(String(btn.dataset.entryId));
            if (entry) openEdit(entry);
        });
    });
}

// ---- API & Inits ----
async function loadMonth(month) {
    currentMonth = month;
    if (fpMonth) fpMonth.setDate(month, false);
    try {
        const res = await fetch(`/api/time-entries?month=${encodeURIComponent(month)}`, { headers: { Accept: "application/json" } });
        render(await res.json());
    } catch (e) { showToast("Hiba a betöltéskor", true); }
}

function initFlatpickrs() {
    fpDate = flatpickr("#entryDate", { dateFormat: "Y-m-d", disable: [d => (d.getDay() === 0 || d.getDay() === 6)] });
    fpMonth = flatpickr("#monthInput", {
        plugins: [new monthSelectPlugin({ shorthand: false, dateFormat: "Y-m", altFormat: "Y. F" })],
        defaultDate: currentMonth || getMonthNow(),
        onChange: (dates) => loadMonth(`${dates[0].getFullYear()}-${pad2(dates[0].getMonth() + 1)}`)
    });
}

function openCreate() {
    el("modalTitle").textContent = "Új bejegyzés";
    el("entryId").value = ""; el("entryHours").value = "1"; el("entryDescription").value = "";
    let d = new Date(); while(isWeekendDate(d)) d = addDays(d, 1);
    if(fpDate) fpDate.setDate(d);
    el("deleteBtn")?.classList.add("hidden"); openModal();
}

function openEdit(entry) {
    el("modalTitle").textContent = "Szerkesztés";
    el("entryId").value = entry.id;
    if(fpDate) fpDate.setDate(entry.entry_date);
    el("entryHours").value = entry.minutes / 60;
    el("entryDescription").value = entry.description || "";
    el("deleteBtn")?.classList.remove("hidden"); openModal();
}

function wireEvents() {
    el("addEntryBtn")?.addEventListener("click", openCreate);
    el("cancelBtn")?.addEventListener("click", closeModal);
    el("modalBackdrop")?.addEventListener("click", closeModal);
    el("prevMonth")?.addEventListener("click", () => loadMonth(shiftMonth(currentMonth, -1)));
    el("nextMonth")?.addEventListener("click", () => loadMonth(shiftMonth(currentMonth, 1)));

    el("saveBtn")?.addEventListener("click", async () => {
        const id = el("entryId").value;
        const payload = { entry_date: el("entryDate").value, minutes: hoursToMinutes(el("entryHours").value), description: el("entryDescription").value };
        try {
            const res = await fetch(id ? `/api/time-entries/${id}` : "/api/time-entries", {
                method: id ? "PUT" : "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": getCsrfToken() },
                body: JSON.stringify(payload)
            });
            if (!res.ok) throw new Error();
            showToast(id ? "Sikeresen módosítva!" : "Az adatok mentése sikeres!", false);
            closeModal(); loadMonth(currentMonth);
        } catch (e) { showToast("Az adatok mentése sikertelen!", true); }
    });

    el("deleteBtn")?.addEventListener("click", async () => {
        if (!confirm("Biztosan törlöd?")) return;
        try {
            const res = await fetch(`/api/time-entries/${el("entryId").value}`, { method: "DELETE", headers: { "X-CSRF-TOKEN": getCsrfToken() } });
            if (!res.ok) throw new Error();
            showToast("Bejegyzés törölve", false);
            closeModal(); loadMonth(currentMonth);
        } catch (e) { showToast("Törlési hiba", true); }
    });
}

document.addEventListener("DOMContentLoaded", () => {
    currentMonth = getMonthNow();
    initFlatpickrs(); wireEvents(); loadMonth(currentMonth);
});
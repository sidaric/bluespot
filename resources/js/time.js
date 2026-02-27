// resources/js/time.js
import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";

/**
 * Time Tracker UI (month view -> weekly timesheet table + CRUD via AJAX)
 *
 * Key behaviors:
 * - Full month shown in ONE table
 * - Each row = a week (Mon-Fri columns)
 * - Multiple entries per day stack in the same cell
 * - Week total counts ONLY entries that belong to the selected month AND Mon-Fri
 * - Flatpickr: weekends disabled
 * - UI: fixed column widths, bordered cells, consistent row height, date label bottom-right
 * - Special rule: if month starts on weekend (1st or 2nd are weekend), do NOT render an extra leading row
 */

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
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.getAttribute("content") : "";
}

function showToast(message, isError = false) {
  const elToast = document.getElementById("toast");
  if (!elToast) return;

  elToast.textContent = message || (isError ? "Hiba történt." : "Sikeres művelet.");
  elToast.classList.remove("hidden");
  elToast.style.backgroundColor = isError ? "#b91c1c" : "#111827";

  clearTimeout(window.__toastTimer);
  window.__toastTimer = setTimeout(() => {
    elToast.classList.add("hidden");
  }, 2500);
}

function hoursToMinutes(hours) {
  const val = Number(hours);
  if (!Number.isFinite(val) || val <= 0) return 0;
  return Math.round(val * 60);
}

function pad2(n) {
  return String(n).padStart(2, "0");
}

function toISODate(d) {
  return `${d.getFullYear()}-${pad2(d.getMonth() + 1)}-${pad2(d.getDate())}`;
}

function parseISODate(yyyy_mm_dd) {
  const [y, m, d] = String(yyyy_mm_dd).split("-").map(Number);
  return new Date(y, (m || 1) - 1, d || 1);
}

function getISODayIndexMon0(date) {
  const day = date.getDay();
  const iso = day === 0 ? 7 : day;
  return iso - 1; // Mon=0..Sun=6
}

function getISOWeekNumber(date) {
  const d = new Date(date.getTime());
  d.setHours(0, 0, 0, 0);

  d.setDate(d.getDate() + 3 - ((d.getDay() + 6) % 7));
  const week1 = new Date(d.getFullYear(), 0, 4);

  return (
    1 +
    Math.round(
      ((d.getTime() - week1.getTime()) / 86400000 - 3 + ((week1.getDay() + 6) % 7)) / 7
    )
  );
}

function getWeekStartMonday(date) {
  const d = new Date(date.getTime());
  const mon0 = getISODayIndexMon0(d);
  d.setDate(d.getDate() - mon0);
  d.setHours(0, 0, 0, 0);
  return d;
}

function addDays(date, days) {
  const d = new Date(date.getTime());
  d.setDate(d.getDate() + days);
  return d;
}

function sameMonth(date, y, m1to12) {
  return date.getFullYear() === y && date.getMonth() === (m1to12 - 1);
}

function formatEntryHours(minutes) {
  return `${minutesToHoursString(minutes)} óra`;
}

function formatCellDateMMDD(date) {
  // "02. 27."
  const mm = pad2(date.getMonth() + 1);
  const dd = pad2(date.getDate());
  return `${mm}. ${dd}.`;
}

function getMonthNow() {
  const d = new Date();
  return d.getFullYear() + "-" + pad2(d.getMonth() + 1);
}

function shiftMonth(month, delta) {
  const [y, m] = (month || getMonthNow()).split("-").map(Number);
  const d = new Date(y, (m || 1) - 1, 1);
  d.setMonth(d.getMonth() + delta);
  return d.getFullYear() + "-" + pad2(d.getMonth() + 1);
}

function getMonthBounds(month) {
  const [y, m] = String(month).split("-").map(Number); // m: 1..12
  const first = new Date(y, (m || 1) - 1, 1);
  const last = new Date(y, (m || 1), 0);
  first.setHours(0, 0, 0, 0);
  last.setHours(0, 0, 0, 0);
  return { y, m, first, last };
}

function isWeekendDate(dateObj) {
  const day = dateObj.getDay(); // Sun=0, Sat=6
  return day === 0 || day === 6;
}

function firstWeekStartForMonth(firstDay) {
  // Special rule:
  // If the 1st (or 2nd) day falls on weekend, don't render an extra leading row.
  //
  // Practical interpretation:
  // - If month starts Sat: first visible week should start on next Monday (1st+2 days)
  // - If month starts Sun: first visible week should start on next Monday (1st+1 day)
  // - Otherwise: start from Monday of that week (can show previous month days disabled)
  const d = new Date(firstDay.getTime());
  const dow = d.getDay(); // Sun=0..Sat=6

  if (dow === 6) {
    // Sat
    return addDays(d, 2); // next Monday
  }
  if (dow === 0) {
    // Sun
    return addDays(d, 1); // next Monday
  }

  return getWeekStartMonday(d);
}

// ---- State ----
let currentMonth = null;
let lastEntriesById = new Map();
let fp = null;

// ---- DOM helpers ----
function el(id) {
  return document.getElementById(id);
}

const modal = el("entryModal");
const backdrop = el("modalBackdrop");

function openModal() {
  modal?.classList.remove("hidden");
  backdrop?.classList.remove("hidden");
}

function closeModal() {
  modal?.classList.add("hidden");
  backdrop?.classList.add("hidden");
}

// ---- Render ----
function render(data) {
  const month = data?.month ?? currentMonth ?? "—";
  const totalMinutes = data?.totalMinutes ?? 0;
  const days = data?.days ?? {};

  el("monthLabel").textContent = month;
  el("totalHours").textContent = `${minutesToHoursString(totalMinutes)} óra`;

  const tbody = el("timesheetBody");
  if (!tbody) return;

  const entriesByDate = new Map(); // YYYY-MM-DD -> entry[]
  lastEntriesById = new Map();

  for (const dateKey of Object.keys(days)) {
    const items = Array.isArray(days[dateKey]) ? days[dateKey] : [];
    const normalized = items
      .map((e) => ({ ...e, entry_date: e?.entry_date ?? dateKey }))
      .filter((e) => !!e.entry_date);

    entriesByDate.set(dateKey, normalized);
    for (const e of normalized) {
      if (e?.id != null) lastEntriesById.set(String(e.id), e);
    }
  }

  const { y, m, first, last } = getMonthBounds(month);

  // Apply special rule for table start:
  const tableStart = firstWeekStartForMonth(first);

  // End week start remains Monday of the last week, but if the tableStart was moved
  // to next Monday, that's still fine.
  const tableEndWeekStart = getWeekStartMonday(last);

  const weekStarts = [];
  for (let ws = new Date(tableStart.getTime()); ws.getTime() <= tableEndWeekStart.getTime(); ws = addDays(ws, 7)) {
    weekStarts.push(ws);
  }

  if (!weekStarts.length) {
    tbody.innerHTML = `
      <tr>
        <td class="px-4 py-10 text-sm text-gray-600 border-b border-gray-200" colspan="7">
          Nincs bejegyzés ebben a hónapban.
        </td>
      </tr>
    `;
    return;
  }

  // UI classes
  const cellBase = "align-top border-b border-gray-200 border-r border-gray-200 p-0";
  const firstCellLeftBorder = "border-l border-gray-200";
  const cellInnerBase = "min-h-[120px] px-4 py-4 flex flex-col justify-between";

  tbody.innerHTML = weekStarts
    .map((weekStart) => {
      const weekNumber = getISOWeekNumber(weekStart);
      let weekTotalMinutes = 0;

      const cells = [];

      for (let i = 0; i < 5; i++) {
        const dayDate = addDays(weekStart, i);
        const dayIso = toISODate(dayDate);
        const inThisMonth = sameMonth(dayDate, y, m);
        const dateLabel = formatCellDateMMDD(dayDate);

        const dayEntries = inThisMonth ? entriesByDate.get(dayIso) || [] : [];

        if (inThisMonth) {
          for (const e of dayEntries) weekTotalMinutes += Number(e?.minutes || 0);
        }

        const disabledCell = !inThisMonth;
        const cellBg = disabledCell ? "bg-gray-50" : "bg-white";
        const cellText = disabledCell ? "text-gray-400" : "text-gray-900";
        const entriesOpacity = disabledCell ? "opacity-40 pointer-events-none" : "";

        // Keep it tiny so it never "centers" content visually.
        let content = `<div class="h-1"></div>`;

        if (inThisMonth && dayEntries.length) {
          content = dayEntries
            .map((e) => {
              const id = e?.id != null ? String(e.id) : "";
              const hoursLabel = formatEntryHours(e?.minutes || 0);
              const desc = e?.description
                ? escapeHtml(e.description)
                : '<span class="text-gray-400">Nincs leírás</span>';

              return `
                <button
                  type="button"
                  class="w-full text-left rounded-lg border border-gray-200 bg-white px-3 py-2 hover:bg-gray-50 transition"
                  data-entry-id="${escapeHtml(id)}"
                >
                  <div class="font-medium text-gray-900">${escapeHtml(hoursLabel)}</div>
                  <div class="text-xs text-gray-500 mt-0.5 truncate">${desc}</div>
                </button>
              `;
            })
            .join(`<div class="h-2"></div>`);
        }

        const leftBorder = i === 0 ? firstCellLeftBorder : "";
        cells.push(`
          <td class="${cellBase} ${leftBorder} ${cellBg}">
            <div class="${cellInnerBase} ${cellText}">
              <div class="space-y-2 ${entriesOpacity}">
                ${content}
              </div>

              <div class="mt-3 text-right text-xs ${disabledCell ? "text-gray-300" : "text-gray-400"}">
                ${escapeHtml(dateLabel)}
              </div>
            </div>
          </td>
        `);
      }

      const weekTotalHours = `${minutesToHoursString(weekTotalMinutes)} óra`;

      const totalCell = `
        <td class="${cellBase} bg-gray-50">
          <div class="${cellInnerBase}">
            <div class="font-semibold text-gray-900">${escapeHtml(weekTotalHours)}</div>
            <div class="text-xs text-gray-400 text-right">&nbsp;</div>
          </div>
        </td>
      `;

      const weekCell = `
        <td class="align-top border-b border-gray-200 bg-gray-50 p-0">
          <div class="${cellInnerBase}">
            <div class="font-semibold text-gray-900">${escapeHtml(String(weekNumber))}</div>
            <div class="text-xs text-gray-400 text-right">&nbsp;</div>
          </div>
        </td>
      `;

      return `
        <tr>
          ${cells.join("")}
          ${totalCell}
          ${weekCell}
        </tr>
      `;
    })
    .join("");

  // Bind edit click handlers
  tbody.querySelectorAll("button[data-entry-id]").forEach((btn) => {
    btn.addEventListener("click", () => {
      const id = btn.getAttribute("data-entry-id");
      if (!id) return;

      const entry = lastEntriesById.get(String(id));
      if (!entry) {
        showToast("Nem találom ezt a bejegyzést.", true);
        return;
      }

      openEdit(entry);
    });
  });
}

// ---- API ----
async function loadMonth(month) {
  currentMonth = month;

  const monthInput = el("monthInput");
  if (monthInput) monthInput.value = month;

  const response = await fetch(`/api/time-entries?month=${encodeURIComponent(month)}`, {
    headers: { Accept: "application/json" },
    credentials: "same-origin",
  });

  const data = await response.json().catch(() => ({}));
  if (!response.ok) throw new Error(data?.message ?? "Betöltés sikertelen.");

  render(data);
  return data;
}

// ---- Flatpickr ----
function initFlatpickr() {
  const input = el("entryDate");
  if (!input) return;

  if (fp) {
    fp.destroy();
    fp = null;
  }

  fp = flatpickr(input, {
    dateFormat: "Y-m-d",
    allowInput: false,
    disableMobile: true,
    disable: [
      function (date) {
        const day = date.getDay();
        return day === 0 || day === 6;
      },
    ],
  });
}

function setFlatpickrDate(iso) {
  if (fp) fp.setDate(iso, true, "Y-m-d");
  else el("entryDate").value = iso;
}

// ---- Modal logic ----
function openCreate() {
  el("modalTitle").textContent = "Új bejegyzés";
  el("entryId").value = "";

  // Default: today, but if weekend -> next Monday
  const today = new Date();
  today.setHours(0, 0, 0, 0);

  let safe = today;
  while (isWeekendDate(safe)) safe = addDays(safe, 1);

  setFlatpickrDate(toISODate(safe));

  el("entryHours").value = "1";
  el("entryDescription").value = "";

  el("deleteBtn")?.classList.add("hidden");
  openModal();
}

function openEdit(entry) {
  el("modalTitle").textContent = "Bejegyzés szerkesztése";

  el("entryId").value = entry?.id ?? "";
  setFlatpickrDate(entry?.entry_date ?? "");

  el("entryHours").value = entry?.minutes != null ? (entry.minutes / 60).toString() : "1";
  el("entryDescription").value = entry?.description ?? "";

  el("deleteBtn")?.classList.remove("hidden");
  openModal();
}

// ---- Event wiring ----
function wireEvents() {
  initFlatpickr();

  el("addEntryBtn")?.addEventListener("click", openCreate);

  el("cancelBtn")?.addEventListener("click", closeModal);
  backdrop?.addEventListener("click", closeModal);

  el("prevMonth")?.addEventListener("click", () => {
    loadMonth(shiftMonth(currentMonth, -1)).catch((e) => showToast(e.message, true));
  });

  el("nextMonth")?.addEventListener("click", () => {
    loadMonth(shiftMonth(currentMonth, +1)).catch((e) => showToast(e.message, true));
  });

  el("monthInput")?.addEventListener("change", (e) => {
    if (e.target?.value) {
      loadMonth(e.target.value).catch((err) => showToast(err.message, true));
    }
  });

  el("saveBtn")?.addEventListener("click", async () => {
    const id = el("entryId")?.value?.trim();
    const entry_date = el("entryDate")?.value;
    const hours = el("entryHours")?.value;
    const description = el("entryDescription")?.value;

    if (!entry_date) {
      showToast("A dátum kötelező.", true);
      return;
    }

    // Hard block weekends as well
    if (isWeekendDate(parseISODate(entry_date))) {
      showToast("Szombat/vasárnap nem rögzíthető. Válassz hétköznapot (H–P).", true);
      return;
    }

    const minutes = hoursToMinutes(hours);
    if (minutes <= 0) {
      showToast("Adj meg érvényes óraszámot.", true);
      return;
    }

    const payload = { entry_date, minutes, description };

    try {
      const url = id ? `/api/time-entries/${id}` : "/api/time-entries";
      const method = id ? "PUT" : "POST";

      const response = await fetch(url, {
        method,
        headers: {
          Accept: "application/json",
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": getCsrfToken(),
        },
        credentials: "same-origin",
        body: JSON.stringify(payload),
      });

      const data = await response.json().catch(() => ({}));
      if (!response.ok) throw new Error(data?.message ?? "Mentés sikertelen.");

      showToast(data?.message ?? "Sikeres mentés.");
      closeModal();
      await loadMonth(currentMonth);
    } catch (e) {
      showToast(e?.message ?? "Hiba történt.", true);
    }
  });

  el("deleteBtn")?.addEventListener("click", async () => {
    const id = el("entryId")?.value?.trim();
    if (!id) return;

    if (!confirm("Biztosan törlöd ezt a bejegyzést?")) return;

    try {
      const response = await fetch(`/api/time-entries/${id}`, {
        method: "DELETE",
        headers: {
          Accept: "application/json",
          "X-CSRF-TOKEN": getCsrfToken(),
        },
        credentials: "same-origin",
      });

      const data = await response.json().catch(() => ({}));
      if (!response.ok) throw new Error(data?.message ?? "Törlés sikertelen.");

      showToast(data?.message ?? "Sikeres törlés.");
      closeModal();
      await loadMonth(currentMonth);
    } catch (e) {
      showToast(e?.message ?? "Hiba történt.", true);
    }
  });
}

// ---- Init ----
(function init() {
  wireEvents();

  const initial = getMonthNow();
  loadMonth(initial).catch((e) => {
    console.error(e);
    showToast(e?.message ?? "Betöltési hiba.", true);
  });
})();
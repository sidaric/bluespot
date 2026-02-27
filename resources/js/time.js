/**
 * Time Tracker UI (month view -> weekly timesheet table + CRUD via AJAX)
 *
 * Requires these elements in the Blade view:
 * - #monthLabel, #totalHours, #timesheetBody
 * - #monthInput, #prevMonth, #nextMonth
 * - #addEntryBtn
 * - Modal: #entryModal, #modalBackdrop, #modalTitle, #entryId, #entryDate, #entryHours, #entryDescription
 * - Buttons: #cancelBtn, #saveBtn, #deleteBtn
 * - Toast: #toast
 *
 * API contract (same as your current backend):
 * GET  /api/time-entries?month=YYYY-MM -> { month, totalMinutes, days: { "YYYY-MM-DD": [ {id, entry_date, minutes, description}, ... ] } }
 * POST /api/time-entries              -> { message, ... }
 * PUT  /api/time-entries/:id          -> { message, ... }
 * DELETE /api/time-entries/:id        -> { message, ... }
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

  // keep it simple – we only set background color dynamically
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
  // JS: Sun=0..Sat=6 -> ISO: Mon=1..Sun=7
  const day = date.getDay();
  const iso = day === 0 ? 7 : day;
  return iso - 1; // Mon=0..Sun=6
}

function getISOWeekNumber(date) {
  // ISO week number based on Thursday
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

function formatEntryHours(minutes) {
  return `${minutesToHoursString(minutes)} óra`;
}

// ---- State ----
let currentMonth = null;
let lastEntriesById = new Map(); // id -> entry (for editing from the table)

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

// ---- Render (weekly timesheet table) ----
function render(data) {
  const month = data?.month ?? "—";
  const totalMinutes = data?.totalMinutes ?? 0;
  const days = data?.days ?? {};

  const monthLabel = el("monthLabel");
  const totalHours = el("totalHours");

  if (monthLabel) monthLabel.textContent = month;
  if (totalHours) totalHours.textContent = `${minutesToHoursString(totalMinutes)} óra`;

  const tbody = el("timesheetBody");
  if (!tbody) return;

  // Flatten entries + keep quick lookup by id
  lastEntriesById = new Map();

  const flat = [];
  for (const date of Object.keys(days)) {
    const items = Array.isArray(days[date]) ? days[date] : [];
    for (const entry of items) {
      // Ensure date is present for grouping, even if backend uses "entry_date"
      const normalized = {
        ...entry,
        entry_date: entry?.entry_date ?? date,
      };
      if (normalized?.id != null) lastEntriesById.set(String(normalized.id), normalized);
      flat.push(normalized);
    }
  }

  if (flat.length === 0) {
    tbody.innerHTML = `
      <tr class="border-t border-gray-100">
        <td class="px-6 py-10 text-sm text-gray-600" colspan="7">
          Nincs bejegyzés ebben a hónapban.
        </td>
      </tr>
    `;
    return;
  }

  // Group into ISO weeks (Mon-Fri only, matching the design)
  // weekKey (YYYY-MM-DD Monday) -> { weekNumber, days: [[],[],[],[],[]], weekTotalMinutes }
  const weeks = new Map();

  for (const e of flat) {
    const dateStr = e?.entry_date;
    if (!dateStr) continue;

    const d = parseISODate(dateStr);
    const dayIdx = getISODayIndexMon0(d); // Mon=0

    // Only Monday-Friday in this layout
    if (dayIdx < 0 || dayIdx > 4) continue;

    const weekStart = getWeekStartMonday(d);
    const weekKey = toISODate(weekStart);
    const weekNumber = getISOWeekNumber(d);

    if (!weeks.has(weekKey)) {
      weeks.set(weekKey, {
        weekKey,
        weekNumber,
        days: [[], [], [], [], []],
        weekTotalMinutes: 0,
      });
    }

    const w = weeks.get(weekKey);
    w.days[dayIdx].push(e);
    w.weekTotalMinutes += Number(e?.minutes || 0);
  }

  const sortedWeeks = Array.from(weeks.values()).sort((a, b) => a.weekKey.localeCompare(b.weekKey));

  tbody.innerHTML = sortedWeeks
    .map((w) => {
      const dayCells = w.days.map((list) => {
        if (!list.length) {
          return `<div class="h-12"></div>`;
        }

        // Multiple entries in one day cell
        return list
          .map((e) => {
            const id = e?.id != null ? String(e.id) : "";
            const hoursLabel = formatEntryHours(e?.minutes || 0);
            const desc = e?.description ? escapeHtml(e.description) : '<span class="text-gray-400">Nincs leírás</span>';

            return `
              <button
                type="button"
                class="w-full text-left rounded-lg border border-gray-200 px-3 py-2 hover:bg-gray-50 transition"
                data-entry-id="${escapeHtml(id)}"
              >
                <div class="font-medium text-gray-900">${escapeHtml(hoursLabel)}</div>
                <div class="text-xs text-gray-500 mt-0.5 truncate">${desc}</div>
              </button>
            `;
          })
          .join(`<div class="h-2"></div>`);
      });

      const weekTotalHours = `${minutesToHoursString(w.weekTotalMinutes)} óra`;

      return `
        <tr class="border-t border-gray-100 align-top">
          ${dayCells
            .map(
              (cell) => `
                <td class="px-6 py-5">
                  <div class="space-y-2">${cell}</div>
                </td>
              `
            )
            .join("")}

          <td class="px-6 py-5 bg-gray-50/60">
            <div class="font-semibold text-gray-900">${escapeHtml(weekTotalHours)}</div>
          </td>

          <td class="px-6 py-5 bg-gray-50/60">
            <div class="font-semibold text-gray-900">${escapeHtml(String(w.weekNumber))}</div>
          </td>
        </tr>
      `;
    })
    .join("");

  // Bind edit click handlers after render (single pass)
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

  if (!response.ok) {
    throw new Error(data?.message ?? "Betöltés sikertelen.");
  }

  render(data);
  return data;
}

// ---- Modal logic ----
function openCreate() {
  el("modalTitle").textContent = "Új bejegyzés";
  el("entryId").value = "";

  el("entryDate").value = new Date().toISOString().slice(0, 10);
  el("entryHours").value = "1";
  el("entryDescription").value = "";

  el("deleteBtn")?.classList.add("hidden");

  openModal();
}

function openEdit(entry) {
  el("modalTitle").textContent = "Bejegyzés szerkesztése";

  el("entryId").value = entry?.id ?? "";
  el("entryDate").value = entry?.entry_date ?? "";
  el("entryHours").value = entry?.minutes != null ? (entry.minutes / 60).toString() : "1";
  el("entryDescription").value = entry?.description ?? "";

  el("deleteBtn")?.classList.remove("hidden");

  openModal();
}

// ---- Event wiring ----
function wireEvents() {
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

    const minutes = hoursToMinutes(hours);

    if (!entry_date) {
      showToast("A dátum kötelező.", true);
      return;
    }
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
      if (!response.ok) {
        throw new Error(data?.message ?? "Mentés sikertelen.");
      }

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
      if (!response.ok) {
        throw new Error(data?.message ?? "Törlés sikertelen.");
      }

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
function minutesToHoursString(totalMinutes) {
  const hours = totalMinutes / 60;
  return Number(hours.toFixed(2)).toString();
}

function escapeHtml(str) {
  return (str ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");
}

function render(data) {
  const month = data?.month ?? "—";
  const totalMinutes = data?.totalMinutes ?? 0;
  const days = data?.days ?? {};

  document.getElementById("monthLabel").textContent = month;
  document.getElementById("totalHours").textContent = `${minutesToHoursString(totalMinutes)} óra`;

  const container = document.getElementById("entries");
  container.innerHTML = "";

  const dates = Object.keys(days).sort();

  if (dates.length === 0) {
    container.innerHTML = `<div class="p-4 text-sm text-gray-600">Nincs bejegyzés ebben a hónapban.</div>`;
    return;
  }

  for (const date of dates) {
    const items = days[date] || [];
    const dayTotal = items.reduce((sum, e) => sum + (e.minutes || 0), 0);

    const row = document.createElement("div");
    row.className = "p-4 space-y-2";

    row.innerHTML = `
      <div class="flex items-center justify-between">
        <div class="font-medium">${date}</div>
        <div class="text-sm text-gray-600">${minutesToHoursString(dayTotal)} óra</div>
      </div>
      <div class="space-y-2">
        ${items.map(e => `
          <div class="rounded border px-3 py-2">
            <div class="flex items-center justify-between gap-4">
              <div class="font-semibold">${minutesToHoursString(e.minutes)} óra</div>
              <div class="text-xs text-gray-500">#${e.id}</div>
            </div>
            <div class="text-sm text-gray-700">
              ${e.description ? escapeHtml(e.description) : '<span class="text-gray-400">Nincs leírás</span>'}
            </div>
          </div>
        `).join("")}
      </div>
    `;

    container.appendChild(row);
  }
}


let currentMonth = null;

function getMonthNow() {
  const d = new Date();
  return d.getFullYear() + "-" + String(d.getMonth() + 1).padStart(2, "0");
}

function shiftMonth(month, delta) {
  const [y, m] = month.split("-").map(Number);
  const d = new Date(y, m - 1, 1);
  d.setMonth(d.getMonth() + delta);
  return d.getFullYear() + "-" + String(d.getMonth() + 1).padStart(2, "0");
}

async function loadMonth(month) {
  currentMonth = month;

  // input + label sync
  const monthInput = document.getElementById("monthInput");
  if (monthInput) monthInput.value = month;

  const response = await fetch(`/api/time-entries?month=${encodeURIComponent(month)}`, {
    headers: { "Accept": "application/json" },
    credentials: "same-origin",
  });

  const data = await response.json();
  render(data);
}

const initialMonth = getMonthNow();
loadMonth(initialMonth).catch((e) => console.error(e));


document.getElementById("prevMonth").onclick = () => loadMonth(shiftMonth(currentMonth, -1));
document.getElementById("nextMonth").onclick = () => loadMonth(shiftMonth(currentMonth, +1));

document.getElementById("monthInput").addEventListener("change", (e) => {
  if (e.target.value) loadMonth(e.target.value);
});


const modal = document.getElementById("entryModal");
const backdrop = document.getElementById("modalBackdrop");

document.getElementById("addEntryBtn").onclick = () => {

    modal.classList.remove("hidden");
    backdrop.classList.remove("hidden");

};

document.getElementById("cancelBtn").onclick = () => {

    modal.classList.add("hidden");
    backdrop.classList.add("hidden");

};

function getCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.getAttribute("content") : "";
}

function showToast(message, isError = false) {
  const el = document.getElementById("toast");
  el.textContent = message;

  el.classList.remove("hidden");
  el.style.backgroundColor = isError ? "#b91c1c" : "#111827";

  clearTimeout(window.__toastTimer);
  window.__toastTimer = setTimeout(() => {
    el.classList.add("hidden");
  }, 2500);
}

function hoursToMinutes(hours) {
  return Math.round(parseFloat(hours) * 60);
}

function closeModal() {
  modal.classList.add("hidden");
  backdrop.classList.add("hidden");
}

document.getElementById("saveBtn").onclick = async () => {
  const entry_date = document.getElementById("entryDate").value;
  const hours = document.getElementById("entryHours").value;
  const description = document.getElementById("entryDescription").value;

  try {
    const response = await fetch("/api/time-entries", {
      method: "POST",
      headers: {
        "Accept": "application/json",
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": getCsrfToken(),
      },
      credentials: "same-origin",
      body: JSON.stringify({
        entry_date,
        minutes: hoursToMinutes(hours),
        description,
      }),
    });

    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
      throw new Error(data?.message ?? "Mentés sikertelen.");
    }

    showToast(data?.message ?? "Sikeres mentés.");
    closeModal();

    // Frissítjük a listát
    await loadMonth(currentMonth);

  } catch (e) {
    showToast(e.message ?? "Hiba történt.", true);
  }
};

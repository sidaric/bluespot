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

async function loadCurrentMonth() {
  const today = new Date();
  const month =
    today.getFullYear() + "-" +
    String(today.getMonth() + 1).padStart(2, "0");

  const response = await fetch(`/api/time-entries?month=${encodeURIComponent(month)}`, {
    headers: { "Accept": "application/json" },
    credentials: "same-origin",
  });

  const data = await response.json();
  render(data);
}

loadCurrentMonth().catch((e) => console.error(e));

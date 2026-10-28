document.addEventListener("DOMContentLoaded", () => {
  const transactionsList = document.getElementById("transactionsList");
  const searchInput = document.getElementById("transactionSearchInput");
  const statusFilter = document.getElementById("statusFilterSelect");
  const countBadge = document.getElementById("transactionCountBadge");

  let allTransactions = [];

  async function loadTransactions() {
    try {
      const response = await fetch("../api/get_patient_transaction.php");
      if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);

      const result = await response.json();

      if (result.status === "success") {
        allTransactions = result.transactions || result.data || [];
        renderTransactions(allTransactions);
      } else {
        transactionsList.innerHTML = `<div class="empty-state">${result.message}</div>`;
      }
    } catch (error) {
      console.error("Error fetching transactions:", error);
      transactionsList.innerHTML = `<div class="empty-state">An error occurred while loading records.</div>`;
    }
  }

  function renderTransactions(items) {
    if (!transactionsList) return;

    if (items.length === 0) {
      transactionsList.innerHTML = `<div class="empty-state">No transaction records found.</div>`;
      if (countBadge) countBadge.textContent = "0 Records";
      return;
    }

    if (countBadge) countBadge.textContent = `${items.length} Record${items.length > 1 ? "s" : ""}`;

    transactionsList.innerHTML = items
      .map((item) => {
        const statusClass = cleanStatusClass(item.status);

        return `
          <div class="transaction-item">
            <div class="item-left">
              <div class="queue-code-badge">${escapeHtml(item.queue_code || "N/A")}</div>
              <div class="service-info">
                <span class="service-name">${escapeHtml(item.service || item.service_snapshot || "General Service")}</span>
                <span class="transaction-date">${escapeHtml(item.transaction_date)}</span>
                <span class="server-info">Served by: ${escapeHtml(item.server_name)}</span>
              </div>
            </div>
            <div class="item-right">
              ${
                item.service_duration && item.service_duration !== "N/A"
                  ? `<div class="duration-tag">${escapeHtml(item.service_duration)}</div>`
                  : ""
              }
            </div>
          </div>
        `;
      })
      .join("");
  }

  function cleanStatusClass(status) {
    const s = String(status || "").toLowerCase().trim();
    if (s.includes("completed")) return "status-completed";
    if (s.includes("progress")) return "status-progress";
    if (s.includes("waiting")) return "status-waiting";
    if (s.includes("cancel")) return "status-cancelled";
    return "status-default";
  }

  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }

  function filterAndRender() {
    const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : "";
    const selectedStatus = statusFilter ? statusFilter.value : "all";

    const filtered = allTransactions.filter((item) => {
      const matchesSearch =
        (item.queue_code && item.queue_code.toLowerCase().includes(searchTerm)) ||
        (item.service && item.service.toLowerCase().includes(searchTerm));

      const matchesStatus =
        selectedStatus === "all" ||
        (item.status && item.status.toLowerCase() === selectedStatus.toLowerCase());

      return matchesSearch && matchesStatus;
    });

    renderTransactions(filtered);
  }

  if (searchInput) searchInput.addEventListener("input", filterAndRender);
  if (statusFilter) statusFilter.addEventListener("change", filterAndRender);

  loadTransactions();
});
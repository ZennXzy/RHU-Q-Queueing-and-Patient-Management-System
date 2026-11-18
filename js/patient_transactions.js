document.addEventListener("DOMContentLoaded", () => {
  const transactionsList = document.getElementById("transactionsList");
  const searchInput = document.getElementById("transactionSearchInput");
  const statusFilter = document.getElementById("statusFilterSelect");
  const countBadge = document.getElementById("transactionCountBadge");

  let allTransactions = [];

  // --Escape HTML--
  function escapeHtml(value) {
    return String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  // --Format Service Duration--
  function formatDuration(secondsVal) {
    if (
      secondsVal === null ||
      secondsVal === undefined ||
      secondsVal === "" ||
      !Number.isFinite(Number(secondsVal)) ||
      Number(secondsVal) < 0
    ) {
      return "N/A";
    }

    const totalSeconds = Math.floor(Number(secondsVal));

    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;

    return [hours, minutes, seconds]
      .map((value) => String(value).padStart(2, "0"))
      .join(":");
  }

  // --Get Display Duration--
  function getDisplayDuration(item) {
    if (
      item.service_duration_formatted &&
      item.service_duration_formatted !== "N/A"
    ) {
      return item.service_duration_formatted;
    }

    return formatDuration(item.service_duration);
  }

  // --Load Transactions--
  async function loadTransactions() {
    if (!transactionsList) return;

    try {
      const response = await fetch("../api/get_patient_transaction.php", {
        cache: "no-store",
      });

      if (!response.ok) {
        throw new Error(`HTTP Error: ${response.status}`);
      }

      const result = await response.json();

      if (result.status === "success") {
        allTransactions = result.transactions || result.data || [];
        filterAndRender();
      } else {
        transactionsList.innerHTML = `
          <div class="empty-state">
            ${escapeHtml(result.message || "Unable to load transactions.")}
          </div>
        `;

        if (countBadge) countBadge.textContent = "0 Records";
      }
    } catch (error) {
      console.error("Error fetching transactions:", error);

      transactionsList.innerHTML = `
        <div class="empty-state">
          An error occurred while loading records.
        </div>
      `;

      if (countBadge) countBadge.textContent = "0 Records";
    }
  }

  // --Render Transactions--
  function renderTransactions(items) {
    if (!transactionsList) return;

    if (items.length === 0) {
      transactionsList.innerHTML = `
        <div class="empty-state">
          No transaction records found.
        </div>
      `;

      if (countBadge) countBadge.textContent = "0 Records";
      return;
    }

    if (countBadge) {
      countBadge.textContent = `${items.length} Record${items.length > 1 ? "s" : ""}`;
    }

    transactionsList.innerHTML = items
      .map((item) => {
        const formattedDuration = getDisplayDuration(item);

        return `
        <div class="transaction-item">
          <div class="item-left">
            <div class="queue-code-badge">
              ${escapeHtml(item.queue_code || "N/A")}
            </div>

            <div class="service-info">
              <span class="service-name">
                ${escapeHtml(
                  item.service || item.service_snapshot || "General Service",
                )}
              </span>

              <span class="transaction-date">
                ${escapeHtml(item.transaction_date || "N/A")}
              </span>

              <span class="server-info">
                Served by:
                ${escapeHtml(item.staff_id || item.staff_name || "N/A")}
              </span>
            </div>
          </div>

          <div class="item-right">
            <div class="duration-tag"
                 title="Service Duration (HH:MM:SS)">
              ${escapeHtml(formattedDuration)}
            </div>
          </div>
        </div>
      `;
      })
      .join("");
  }

  // --Status Class--
  function cleanStatusClass(status) {
    const s = String(status || "")
      .toLowerCase()
      .trim();

    if (s.includes("completed")) return "status-completed";
    if (s.includes("progress")) return "status-progress";
    if (s.includes("waiting")) return "status-waiting";
    if (s.includes("cancel")) return "status-cancelled";

    return "status-default";
  }

  // --Search and Filter--
  function filterAndRender() {
    const searchTerm = searchInput
      ? searchInput.value.toLowerCase().trim()
      : "";

    const selectedStatus = statusFilter ? statusFilter.value : "all";

    const filtered = allTransactions.filter((item) => {
      const matchesSearch =
        String(item.queue_code || "")
          .toLowerCase()
          .includes(searchTerm) ||
        String(item.service || "")
          .toLowerCase()
          .includes(searchTerm);

      const matchesStatus =
        selectedStatus === "all" ||
        String(item.status || "").toLowerCase() ===
          selectedStatus.toLowerCase();

      return matchesSearch && matchesStatus;
    });

    renderTransactions(filtered);
  }

  // --Event Listeners--
  if (searchInput) {
    searchInput.addEventListener("input", filterAndRender);
  }

  if (statusFilter) {
    statusFilter.addEventListener("change", filterAndRender);
  }

  loadTransactions();
});

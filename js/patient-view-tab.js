let currentModalPatientId = null;

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
function formatTransactionDuration(secondsVal) {
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
function getTransactionDuration(tx) {
  if (
    tx.service_duration_formatted &&
    tx.service_duration_formatted !== "N/A"
  ) {
    return tx.service_duration_formatted;
  }

  return formatTransactionDuration(tx.service_duration);
}

// --Initialize Tab Buttons--
document.addEventListener("DOMContentLoaded", () => {
  ["btnViewProfile", "btnViewTransactions"].forEach((id) => {
    const btn = document.getElementById(id);

    if (btn) {
      btn.addEventListener("click", (e) => e.stopPropagation());
    }
  });
});

// --Set Current Patient--
window.setModalPatientId = function (patientId) {
  currentModalPatientId = patientId;

  switchModalView("profile");
};

// --Switch Modal View--
function switchModalView(view) {
  const profileView = document.getElementById("patientProfileView");
  const transactionsView = document.getElementById("patientTransactionsView");
  const btnProfile = document.getElementById("btnViewProfile");
  const btnTransactions = document.getElementById("btnViewTransactions");

  if (!profileView || !transactionsView || !btnProfile || !btnTransactions) {
    return;
  }

  if (view === "transactions") {
    profileView.style.display = "none";
    transactionsView.style.display = "block";

    btnProfile.classList.remove("active");
    btnTransactions.classList.add("active");

    if (currentModalPatientId) {
      loadPatientTransactions(currentModalPatientId);
    } else {
      const container = document.getElementById("transactionsList");

      if (container) {
        container.innerHTML =
          '<div class="empty-transactions">No patient selected.</div>';
      }
    }
  } else {
    profileView.style.display = "";
    transactionsView.style.display = "none";

    btnTransactions.classList.remove("active");
    btnProfile.classList.add("active");
  }
}

// --Load Patient Transactions--
async function loadPatientTransactions(patientId) {
  const container = document.getElementById("transactionsList");

  if (!container) return;

  container.innerHTML = `
    <div class="empty-transactions">
      Loading transactions...
    </div>
  `;

  try {
    const response = await fetch(
      `../api/get_patient_transaction.php?patient_id=${encodeURIComponent(patientId)}`,
      { cache: "no-store" },
    );

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();

    if (data.status !== "success") {
      throw new Error(data.message || "Unable to load transactions.");
    }

    const transactions = data.transactions || data.data || [];

    if (transactions.length > 0) {
      container.innerHTML = transactions
        .map((tx) => {
          const duration = getTransactionDuration(tx);

          return `
          <div class="transaction-card">
            <div class="tx-header">
              <span class="tx-code-badge">
                Queue Code: ${escapeHtml(tx.queue_code || "N/A")}
              </span>

              <span class="tx-date">
                ${escapeHtml(tx.transaction_date || "N/A")}
              </span>
            </div>

            <div class="tx-grid">
              <div class="tx-field">
                <label>Service</label>
                <span>
                  ${escapeHtml(tx.service || "N/A")}
                </span>
              </div>

              <div class="tx-field">
                <label>Server / Attendant</label>
                <span>
                  ${escapeHtml(tx.staff_name || tx.staff_id || "N/A")}
                </span>
              </div>

              <div class="tx-field">
                <label>Service Duration</label>
                <span>
                  ${escapeHtml(duration)}
                </span>
              </div>

              <div class="tx-field full-width">
                <label>Remarks</label>
                <span class="tx-remarks">
                  "${escapeHtml(tx.remarks || "None")}"
                </span>
              </div>
            </div>
          </div>
        `;
        })
        .join("");
    } else {
      container.innerHTML = `
        <div class="empty-transactions">
          No transaction history found for this patient.
        </div>
      `;
    }
  } catch (error) {
    console.error("Error fetching transactions:", error);

    container.innerHTML = `
      <div class="empty-transactions">
        Failed to load transactions.
      </div>
    `;
  }
}

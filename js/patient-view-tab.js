let currentModalPatientId = null;

// Escape text before putting it into innerHTML
function escapeHtml(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

// Clicks on the tab buttons must not bubble up to the dialog. Otherwise a
// "click outside to close" handler can see the click after the layout has
// changed and wrongly close the modal.
document.addEventListener('DOMContentLoaded', () => {
  ['btnViewProfile', 'btnViewTransactions'].forEach(id => {
    const btn = document.getElementById(id);
    if (btn) btn.addEventListener('click', e => e.stopPropagation());
  });
});

// Expose function globally so view buttons can set patient ID
window.setModalPatientId = function(patientId) {
  currentModalPatientId = patientId;
  // Reset tab to profile view when opening a new modal
  switchModalView('profile');
};

// Switch between Profile and Transactions views
function switchModalView(view) {
  const profileView = document.getElementById('patientProfileView');
  const transactionsView = document.getElementById('patientTransactionsView');
  const btnProfile = document.getElementById('btnViewProfile');
  const btnTransactions = document.getElementById('btnViewTransactions');

  if (view === 'transactions') {
    profileView.style.display = 'none';
    transactionsView.style.display = 'block';

    btnProfile.classList.remove('active');
    btnTransactions.classList.add('active');

    if (currentModalPatientId) {
      loadPatientTransactions(currentModalPatientId);
    } else {
      document.getElementById('transactionsList').innerHTML = 
        '<div class="empty-transactions">No patient selected.</div>';
    }
  } else {
    profileView.style.display = '';
    transactionsView.style.display = 'none';

    btnTransactions.classList.remove('active');
    btnProfile.classList.add('active');
  }
}

// Fetch transaction history from backend
async function loadPatientTransactions(patientId) {
  const container = document.getElementById('transactionsList');
  container.innerHTML = '<div class="empty-transactions">Loading transactions...</div>';

  try {
    // FIX: Updated path from get_patient_transaction.php to get_patient_transactions.php (or ../php/ if located there)
    const response = await fetch(`../api/get_patient_transaction.php?patient_id=${patientId}`);
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();

    if (data.status === 'success' && data.transactions && data.transactions.length > 0) {
      container.innerHTML = data.transactions.map(tx => `
        <div class="transaction-card">
          <div class="tx-header">
            <span class="tx-code-badge">Queue Code: ${escapeHtml(tx.queue_code)}</span>
            <span class="tx-date">${escapeHtml(tx.transaction_date)}</span>
          </div>
          <div class="tx-grid">
            <div class="tx-field">
              <label>Service</label>
              <span>${escapeHtml(tx.service)}</span>
            </div>
            <div class="tx-field">
              <label>Server / Attendant</label>
              <span>${escapeHtml(tx.server_name)}</span>
            </div>
            <div class="tx-field">
              <label>Service Duration</label>
              <span>${escapeHtml(tx.service_duration)}</span>
            </div>
            <div class="tx-field full-width">
              <label>Remarks</label>
              <span class="tx-remarks">"${escapeHtml(tx.remarks)}"</span>
            </div>
          </div>
        </div>
      `).join('');
    } else {
      container.innerHTML = '<div class="empty-transactions">No transaction history found for this patient.</div>';
    }
  } catch (error) {
    console.error('Error fetching transactions:', error);
    container.innerHTML = '<div class="empty-transactions">Failed to load transactions.</div>';
  }
}
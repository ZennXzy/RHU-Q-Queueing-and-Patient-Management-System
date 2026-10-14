document.addEventListener('DOMContentLoaded', () => {
  const logsModal = document.getElementById('logsModal');
  const openLogsBtn = document.getElementById('open-logs-button');
  const closeLogsBtn = document.getElementById('logsCloseBtn');
  const logsSearch = document.getElementById('logsSearch');
  const logsRefreshBtn = document.getElementById('logsRefreshBtn');
  const logsBody = document.getElementById('logsBody');
  const logsHeadRow = document.getElementById('logsHeadRow');
  const logsInfo = document.getElementById('logsInfo');
  const logsPrevBtn = document.getElementById('logsPrevBtn');
  const logsNextBtn = document.getElementById('logsNextBtn');
  const logsPageLabel = document.getElementById('logsPageLabel');

  let currentPage = 1;
  let currentSearch = '';
  let totalPages = 1;

  // 1. OPEN MODAL
  if (openLogsBtn && logsModal) {
    openLogsBtn.addEventListener('click', () => {
      if (typeof logsModal.showModal === 'function') {
        logsModal.showModal();
        fetchLogs(1, currentSearch);
      }
    });
  }

  // 2. CLOSE MODAL VIA BUTTON
  if (closeLogsBtn && logsModal) {
    closeLogsBtn.addEventListener('click', () => {
      logsModal.close();
    });
  }

  // 3. CLOSE MODAL ON BACKDROP CLICK
  if (logsModal) {
    logsModal.addEventListener('click', (e) => {
      const rect = logsModal.getBoundingClientRect();
      const isInModal =
        rect.top <= e.clientY &&
        e.clientY <= rect.top + rect.height &&
        rect.left <= e.clientX &&
        e.clientX <= rect.left + rect.width;
      if (!isInModal) {
        logsModal.close();
      }
    });
  }

  // 4. LIVE SEARCH WITH DEBOUNCE
  let searchTimer;
  if (logsSearch) {
    logsSearch.addEventListener('input', (e) => {
      currentSearch = e.target.value.trim();
      clearTimeout(searchTimer);
      searchTimer = setTimeout(() => {
        currentPage = 1;
        fetchLogs(currentPage, currentSearch);
      }, 300);
    });
  }

  // 5. REFRESH BUTTON
  if (logsRefreshBtn) {
    logsRefreshBtn.addEventListener('click', () => {
      fetchLogs(currentPage, currentSearch);
    });
  }

  // 6. PAGINATION CONTROLS
  if (logsPrevBtn) {
    logsPrevBtn.addEventListener('click', () => {
      if (currentPage > 1) {
        currentPage--;
        fetchLogs(currentPage, currentSearch);
      }
    });
  }

  if (logsNextBtn) {
    logsNextBtn.addEventListener('click', () => {
      if (currentPage < totalPages) {
        currentPage++;
        fetchLogs(currentPage, currentSearch);
      }
    });
  }

  // 7. FETCH LOGS FROM BACKEND API
  async function fetchLogs(page = 1, search = '') {
    if (!logsBody) return;
    logsBody.innerHTML = '<tr class="logs-state"><td colspan="2" style="text-align: center; padding: 20px;">Loading logs...</td></tr>';

    try {
      const response = await fetch(`../api/get_activity_logs.php?page=${page}&search=${encodeURIComponent(search)}`);
      if (!response.ok) throw new Error('Failed to fetch activity logs.');

      const data = await response.json();

      if (data.status === 'success') {
        renderTable(data.columns || [], data.logs || []);
        currentPage = data.current_page || 1;
        totalPages = data.total_pages || 1;
        updatePaginationUI(data.total_records || 0);
      } else {
        logsBody.innerHTML = `<tr class="logs-state"><td colspan="2" style="text-align: center; padding: 20px;">${data.message || 'No logs found.'}</td></tr>`;
      }
    } catch (err) {
      console.error('Error loading logs:', err);
      logsBody.innerHTML = '<tr class="logs-state"><td colspan="2" style="text-align: center; color: #dc2626; padding: 20px;">Error loading logs from server.</td></tr>';
    }
  }

  // 8. RENDER TABLE HEADERS & ROWS
  function renderTable(columns = [], rows = []) {
    if (!logsHeadRow || !logsBody) return;

    if (columns.length > 0) {
      logsHeadRow.innerHTML = columns.map(col => `<th>${escapeHtml(col)}</th>`).join('');
    } else {
      logsHeadRow.innerHTML = '<th>Activity Message</th><th>Date & Time</th>';
    }

    if (rows.length > 0) {
      logsBody.innerHTML = rows.map(row => {
        return `<tr>${row.map(cell => `<td>${escapeHtml(cell)}</td>`).join('')}</tr>`;
      }).join('');
    } else {
      logsBody.innerHTML = '<tr class="logs-state"><td colspan="2" style="text-align: center; padding: 20px;">No activity logs found.</td></tr>';
    }
  }

  // 9. UPDATE PAGINATION BUTTONS & INFO
  function updatePaginationUI(totalRecords) {
    if (logsPageLabel) logsPageLabel.textContent = `Page ${currentPage} of ${totalPages || 1}`;
    if (logsInfo) logsInfo.textContent = `Total records: ${totalRecords}`;
    if (logsPrevBtn) logsPrevBtn.disabled = currentPage <= 1;
    if (logsNextBtn) logsNextBtn.disabled = currentPage >= totalPages;
  }

  // 10. SECURITY HELPER TO ESCAPE HTML
  function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }
});
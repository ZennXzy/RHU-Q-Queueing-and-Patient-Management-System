document.addEventListener("DOMContentLoaded", () => {
  const filterModal = document.getElementById('filter-records-modal');
  const filterForm = document.getElementById('filter-records-form');
  const openFilterBtn = document.getElementById('open-filter-button');
  const closeFilterBtn = document.getElementById('closeFilterModal');
  const resetFilterBtn = document.getElementById('resetFilterBtn');
  const searchInput = document.getElementById('searchReports');
  const exportBtn = document.getElementById('export-btn');

  // Initial load
  fetchReports();

  // Modal Triggers
  if (openFilterBtn && filterModal) {
    openFilterBtn.addEventListener('click', () => filterModal.showModal());
  }
  if (closeFilterBtn && filterModal) {
    closeFilterBtn.addEventListener('click', () => filterModal.close());
  }

  if (resetFilterBtn && filterForm) {
    resetFilterBtn.addEventListener('click', () => {
      filterForm.reset();
      fetchReports();
    });
  }

  // Handle Search Input (Debounced)
  let searchTimer;
  if (searchInput) {
    searchInput.addEventListener('input', () => {
      clearTimeout(searchTimer);
      searchTimer = setTimeout(() => {
        fetchReports(new URLSearchParams(new FormData(filterForm)), searchInput.value);
      }, 300);
    });
  }

  // Handle Filter Form Submission
  if (filterForm) {
    filterForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const formData = new FormData(filterForm);
      
      // FIX: Convert FormData to URLSearchParams so .toString() works correctly
      const params = new URLSearchParams(formData);
      
      fetchReports(params, searchInput ? searchInput.value : '');
      if (filterModal) filterModal.close();
    });
  }

  // Fetch Reports Function via AJAX
  async function fetchReports(filterParams = new URLSearchParams(), searchQuery = '') {
    try {
      if (searchQuery) filterParams.set('search', searchQuery);

      const response = await fetch(`../api/get_reports.php?${filterParams.toString()}`);
      if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);

      const result = await response.json();

      if (result.status === 'success') {
        const tbody = document.getElementById('reports-table-body');
        const statTotal = document.getElementById('stat-total-records');

        if (tbody) tbody.innerHTML = result.html;
        if (statTotal) statTotal.textContent = result.total;
      } else {
        if (typeof showToast === 'function') {
          showToast(result.message || 'No records found.', 'info');
        }
      }
    } catch (error) {
      console.error('Error fetching reports:', error);
      if (typeof showToast === 'function') {
        showToast('An error occurred while loading reports.', 'error');
      }
    }
  }

  // Export CSV Handler
  if (exportBtn) {
    exportBtn.addEventListener('click', () => {
      const formData = new FormData(filterForm);
      const params = new URLSearchParams(formData);
      if (searchInput && searchInput.value) params.set('search', searchInput.value);
      
      window.location.href = `../api/export_reports.php?${params.toString()}`;
    });
  }

  // Delegate View Details Click
  document.addEventListener('click', async (e) => {
    const viewBtn = e.target.closest('.view-record-btn');
    if (!viewBtn) return;

    const recordId = viewBtn.dataset.id;
    const viewModal = document.getElementById('view-record-modal');
    const contentContainer = document.getElementById('recordDetailsContent');

    try {
      const response = await fetch(`../api/get_reports.php?action=details&id=${recordId}`);
      const result = await response.json();

      if (result.status === 'success') {
        const rec = result.record;
        if (contentContainer) {
          contentContainer.innerHTML = `
            <p><strong>Patient Name:</strong> ${rec.fullname}</p>
            <p><strong>Visit Date:</strong> ${rec.transaction_date}</p>
            <p><strong>Age / Gender:</strong> ${rec.age} / ${rec.gender}</p>
            <p><strong>Service Taken:</strong> ${rec.service_type || rec.service_snapshot || 'N/A'}</p>
            <p><strong>Address:</strong> ${rec.address}</p>
            <p><strong>Vital Signs:</strong><br>
            <p>Blood Pressure: ${rec.blood_pressure || 'N/A'}</p>
            <p>Temperature: ${rec.temperature || 'N/A'}</p>
            <p>Respiratory Rate: ${rec.respiratory_rate || 'N/A'}</p>
            <p>Pulse Rate: ${rec.pulse_rate || 'N/A'}</p>
            <p>Weight: ${rec.weight || 'N/A'}</p>
            <p>Height: ${rec.height || 'N/A'}</p>
            <p><strong>Chief Complaint:</strong> ${rec.remarks || 'N/A'}</p>
          `;
        }
        if (viewModal) viewModal.showModal();
      }
    } catch (err) {
      console.error('Error fetching record details:', err);
    }
  });
});
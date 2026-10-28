document.addEventListener('DOMContentLoaded', () => {
  const servicesTableBody = document.getElementById('servicesTableBody');
  const serviceModal = document.getElementById('serviceModal');
  const openAddModalBtn = document.getElementById('openAddServiceModalBtn');
  const closeServiceModalBtn = document.getElementById('closeServiceModalBtn');
  const cancelServiceModalBtn = document.getElementById('cancelServiceModalBtn');
  const serviceForm = document.getElementById('serviceForm');
  const serviceModalTitle = document.getElementById('serviceModalTitle');
  const serviceIdInput = document.getElementById('serviceId');
  const serviceNameInput = document.getElementById('serviceNameInput');

  if (!servicesTableBody) return;

  function notifyUser(message, type = 'error') {
    if (typeof showToast === 'function') {
      showToast(message, type);
    } else {
      alert(message);
    }
  }

  // Fetch and Render Services
  function loadServices() {
    fetch('../api/get_services.php')
      .then(response => response.json())
      .then(data => {
        if (data.status === 'success') {
          renderServicesTable(data.services);
        } else {
          servicesTableBody.innerHTML = `<tr><td colspan="4" style="padding: 20px; text-align: center; color: #ef4444;">Failed to load services.</td></tr>`;
        }
      })
      .catch(error => {
        console.error('Error fetching services:', error);
        servicesTableBody.innerHTML = `<tr><td colspan="4" style="padding: 20px; text-align: center; color: #ef4444;">Error loading services.</td></tr>`;
      });
  }

  function renderServicesTable(services) {
    if (services.length === 0) {
      servicesTableBody.innerHTML = `<tr><td colspan="4" style="padding: 20px; text-align: center; color: #64748b;">No services found in the catalog.</td></tr>`;
      return;
    }

    let html = '';
    services.forEach(service => {
      const isAdmin = document.querySelector('#servicesTableBody').closest('table').querySelector('th:nth-child(4)') !== null;
      
      html += `
        <tr style="border-bottom: 1px solid #f1f5f9;">
          <td style="padding: 12px; color: #334155;">#</td>
          <td style="padding: 12px; font-weight: 500; color: #0f172a;">${escapeHtml(service.service_name)}</td>
          <td style="padding: 12px; color: #64748b;">${service.created_at || 'N/A'}</td>
          ${isAdmin ? `
          <td style="padding: 12px; text-align: right;">
            <button type="button" class="btn-edit-service" data-id="${service.service_id}" data-name="${escapeHtml(service.service_name)}" style="background: #3b82f6; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; margin-right: 5px; font-size: 0.85rem;">Edit</button>
            <button type="button" class="btn-delete-service" data-id="${service.service_id}" style="background: #ef4444; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 0.85rem;">Delete</button>
          </td>` : ''}
        </tr>
      `;
    });
    servicesTableBody.innerHTML = html;

    // Attach event listeners
    document.querySelectorAll('.btn-edit-service').forEach(btn => {
      btn.addEventListener('click', function() {
        openEditModal(this.getAttribute('data-id'), this.getAttribute('data-name'));
      });
    });

    document.querySelectorAll('.btn-delete-service').forEach(btn => {
      btn.addEventListener('click', function() {
        deleteService(this.getAttribute('data-id'));
      });
    });
  }

  function escapeHtml(text) {
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return text.replace(/[&<>"']/g, m => map[m]);
  }

  // Modal Controls
  if (openAddModalBtn) {
    openAddModalBtn.addEventListener('click', () => {
      serviceModalTitle.textContent = 'Add New Service';
      serviceIdInput.value = '';
      serviceNameInput.value = '';
      if (typeof serviceModal.showModal === 'function') serviceModal.showModal();
      else serviceModal.style.display = 'block';
    });
  }

  function openEditModal(id, name) {
    serviceModalTitle.textContent = 'Edit Service';
    serviceIdInput.value = id;
    serviceNameInput.value = name;
    if (typeof serviceModal.showModal === 'function') serviceModal.showModal();
    else serviceModal.style.display = 'block';
  }

  function closeModal() {
    if (typeof serviceModal.close === 'function') serviceModal.close();
    else serviceModal.style.display = 'none';
  }

  if (closeServiceModalBtn) closeServiceModalBtn.addEventListener('click', closeModal);
  if (cancelServiceModalBtn) cancelServiceModalBtn.addEventListener('click', closeModal);

  // Form Submit (Add or Edit)
  if (serviceForm) {
    serviceForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const id = serviceIdInput.value;
      const name = serviceNameInput.value.trim();

      if (!name) {
        notifyUser('Service name cannot be empty.', 'error');
        return;
      }

      const formData = new FormData();
      formData.append('service_name', name);
      formData.append('action', id ? 'edit' : 'add');
      if (id) formData.append('service_id', id);

      fetch('../php/manage_services.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.status === 'success') {
          notifyUser(data.message, 'success');
          closeModal();
          loadServices();
        } else {
          notifyUser(data.message || 'Operation failed.', 'error');
        }
      })
      .catch(err => {
        console.error('Error saving service:', err);
        notifyUser('An unexpected error occurred.', 'error');
      });
    });
  }

  // Delete Service
  function deleteService(id) {
    if (!confirm('Are you sure you want to delete this service?')) return;

    const formData = new FormData();
    formData.append('service_id', id);
    formData.append('action', 'delete');

    fetch('../php/manage_services.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.status === 'success') {
        notifyUser(data.message, 'success');
        loadServices();
      } else {
        notifyUser(data.message || 'Failed to delete service.', 'error');
      }
    })
    .catch(err => {
      console.error('Error deleting service:', err);
      notifyUser('An unexpected error occurred.', 'error');
    });
  }

  loadServices();
});
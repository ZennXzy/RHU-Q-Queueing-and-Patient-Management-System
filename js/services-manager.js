
document.addEventListener('DOMContentLoaded', () => {
    const servicesTableBody = document.getElementById('servicesTableBody');

    // Add / Edit Modal
    const serviceModal = document.getElementById('serviceModal');
    const openAddModalBtn = document.getElementById('openAddServiceModalBtn');
    const closeServiceModalBtn = document.getElementById('closeServiceModalBtn');
    const cancelServiceModalBtn = document.getElementById('cancelServiceModalBtn');

    const serviceForm = document.getElementById('serviceForm');
    const serviceModalTitle = document.getElementById('serviceModalTitle');
    const serviceIdInput = document.getElementById('serviceId');
    const serviceNameInput = document.getElementById('serviceNameInput');
    const saveServiceBtn = document.getElementById('saveServiceBtn');

    // Delete Confirmation Modal
    const deleteServiceModal = document.getElementById('deleteServiceModal');
    const deleteServiceForm = document.getElementById('deleteServiceForm');
    const deleteServiceIdInput = document.getElementById('deleteServiceId');
    const deleteServiceName = document.getElementById('deleteServiceName');

    const closeDeleteServiceModalBtn =
        document.getElementById('closeDeleteServiceModalBtn');

    const cancelDeleteServiceBtn =
        document.getElementById('cancelDeleteServiceBtn');

    const confirmDeleteServiceBtn =
        document.getElementById('confirmDeleteServiceBtn');

    // Mobile Services Toggle
    const toggleBtn = document.getElementById('toggleServicesBtn');
    const cardBody = document.getElementById('servicesCardBody');
    const divider = document.getElementById('servicesDivider');

    let isDeleting = false;
    let isSaving = false;

    function notifyUser(message, type = 'error') {
        if (typeof showToast === 'function') {
            showToast(message, type);
        } else {
            alert(message);
        }
    }

    if (toggleBtn && cardBody) {
        const isMobile = window.innerWidth <= 767.98;

        if (isMobile) {
            cardBody.style.display = 'none';

            if (divider) {
                divider.classList.add('collapsed-divider');
            }
            toggleBtn.textContent = 'Show Services';
        } else {
            cardBody.style.display = 'block';
        }

        toggleBtn.addEventListener('click', () => {
            const isHidden =
                cardBody.style.display === 'none' ||
                getComputedStyle(cardBody).display === 'none';

            if (isHidden) {
                cardBody.style.display = 'block';
                if (divider) {
                    divider.classList.remove('collapsed-divider');
                }
                toggleBtn.textContent = 'Hide Services';
            } else {
                cardBody.style.display = 'none';
                if (divider) {
                    divider.classList.add('collapsed-divider');
                }
                toggleBtn.textContent = 'Show Services';
            }
        });
    }

    if (!servicesTableBody) return;

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };

        return String(text ?? '').replace(
            /[&<>"']/g,
            character => map[character]
        );
    }

    async function loadServices() {

        try {
            const response = await fetch('../api/get_services.php');

            if (!response.ok) {
                throw new Error('Failed to fetch services.');
            }

            const data = await response.json();

            if (data.status === 'success') {
                renderServicesTable(data.services || []);
            } else {
                servicesTableBody.innerHTML = `
                    <tr>
                        <td colspan="5"
                            style="padding:20px;text-align:center;color:#DC3545;">
                            Failed to load services.
                        </td>
                    </tr>
                `;
            }

        } catch (error) {
            console.error('Error fetching services:', error);

            servicesTableBody.innerHTML = `
                <tr>
                    <td colspan="5"
                        style="padding:20px;text-align:center;color:#DC3545;">
                        Error loading services.
                    </td>
                </tr>
            `;
        }
    }

    function renderServicesTable(services) {

        const table = servicesTableBody.closest('table');

        const isAdmin = Boolean(
            table && table.querySelector('th:nth-child(5)')
        );

        const columnCount = isAdmin ? 5 : 4;
        if (services.length === 0) {
            servicesTableBody.innerHTML = `
                <tr>
                    <td colspan="${columnCount}"
                        style="padding:20px;text-align:center;color:#64748B;">
                        No services found in the catalog.
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';

        services.forEach(service => {

            const id = escapeHtml(service.service_id);
            const name = escapeHtml(service.service_name);
            const status = escapeHtml(service.status);
            const createdAt = escapeHtml(service.created_at || 'N/A');

            html += `
                <tr style="border-bottom:1px solid #F1F5F9;">

                    <td style="padding:12px;color:#334155;">
                        #
                    </td>

                    <td style="padding:12px;font-weight:500;color:#0F172A;">
                        ${name}
                    </td>

                    <td style="padding:12px;color:#334155;">
                        ${status}
                    </td>

                    <td style="padding:12px;color:#64748B;">
                        ${createdAt}
                    </td>

                    ${isAdmin ? `
                        <td style="padding:12px;text-align:right;">

                            <button
                                type="button"
                                class="btn-edit-service"
                                data-id="${id}"
                                data-name="${name}"
                                style="
                                    background:#3B82F6;
                                    color:#FFFFFF;
                                    border:none;
                                    padding:7px 13px;
                                    border-radius:7px;
                                    cursor:pointer;
                                    margin-right:5px;
                                    font-size:12px;
                                    font-weight:600;
                                "
                            >
                                Edit
                            </button>

                            <button
                                type="button"
                                class="btn-delete-service"
                                data-id="${id}"
                                data-name="${name}"
                                style="
                                    background:#DC3545;
                                    color:#FFFFFF;
                                    border:none;
                                    padding:7px 13px;
                                    border-radius:7px;
                                    cursor:pointer;
                                    font-size:12px;
                                    font-weight:600;
                                "
                            >
                                Delete
                            </button>

                        </td>
                    ` : ''}

                </tr>
            `;
        });

        servicesTableBody.innerHTML = html;
    }


    servicesTableBody.addEventListener('click', (event) => {

        const editButton = event.target.closest('.btn-edit-service');

        if (editButton) {
            openEditModal(
                editButton.dataset.id,
                editButton.dataset.name
            );
            return;
        }

        const deleteButton = event.target.closest('.btn-delete-service');

        if (deleteButton) {
            openDeleteModal(
                deleteButton.dataset.id,
                deleteButton.dataset.name
            );
        }
    });

    if (openAddModalBtn) {
        openAddModalBtn.addEventListener('click', () => {

            if (!serviceModal) return;

            serviceModalTitle.textContent = 'Add New Service';
            serviceIdInput.value = '';
            serviceNameInput.value = '';

            serviceModal.showModal();
        });
    }

    function openEditModal(id, name) {

        if (!serviceModal) return;

        serviceModalTitle.textContent = 'Edit Service';
        serviceIdInput.value = id;
        serviceNameInput.value = name;

        serviceModal.showModal();
    }

    function closeServiceModal() {
        if (serviceModal && serviceModal.open) {
            serviceModal.close();
        }
    }

    if (closeServiceModalBtn) {
        closeServiceModalBtn.addEventListener(
            'click',
            closeServiceModal
        );
    }

    if (cancelServiceModalBtn) {
        cancelServiceModalBtn.addEventListener(
            'click',
            closeServiceModal
        );
    }

    if (serviceForm) {
        serviceForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (isSaving) return;

            const id = serviceIdInput.value;
            const name = serviceNameInput.value.trim();

            if (!name) {
                notifyUser('Service name cannot be empty.', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('service_name', name);
            formData.append('action', id ? 'edit' : 'add');

            if (id) {
                formData.append('service_id', id);
            }

            isSaving = true;

            if (saveServiceBtn) {
                saveServiceBtn.disabled = true;
            }

            try {
                const response = await fetch(
                    '../php/manage_services.php',
                    {
                        method: 'POST',
                        body: formData
                    }
                );
                if (!response.ok) {
                    throw new Error('Failed to save service.');
                }
                const data = await response.json();

                if (data.status === 'success') {
                    notifyUser(
                        data.message || 'Service saved successfully.',
                        'success'
                    );
                    closeServiceModal();
                    loadServices();
                } else {
                    notifyUser(
                        data.message || 'Operation failed.',
                        'error'
                    );
                }
            } catch (error) {
                console.error('Error saving service:', error);
                notifyUser(
                    'An unexpected error occurred.',
                    'error'
                );
            } finally {
                isSaving = false;
                if (saveServiceBtn) {
                    saveServiceBtn.disabled = false;
                }
            }
        });
    }

    function openDeleteModal(id, name) {

        if (!deleteServiceModal) {
            notifyUser('Delete confirmation modal was not found.', 'error');
            return;
        }

        if (isDeleting) return;

        deleteServiceIdInput.value = id;
        deleteServiceName.textContent = name || 'Unnamed Service';

        deleteServiceModal.showModal();
    }

    function closeDeleteModal() {
        if (isDeleting) return;
        if (deleteServiceModal && deleteServiceModal.open) {
            deleteServiceModal.close();
        }
    }
    if (closeDeleteServiceModalBtn) {
        closeDeleteServiceModalBtn.addEventListener(
            'click',
            closeDeleteModal
        );
    }
    if (cancelDeleteServiceBtn) {
        cancelDeleteServiceBtn.addEventListener(
            'click',
            closeDeleteModal
        );
    }
    if (deleteServiceModal) {
        // Prevent closing while a deletion is processing.
        deleteServiceModal.addEventListener('cancel', (event) => {
            if (isDeleting) {
                event.preventDefault();
            }
        });

        // Clear selected service after closing.
        deleteServiceModal.addEventListener('close', () => {

            if (deleteServiceForm) {
                deleteServiceForm.reset();
            }

            if (deleteServiceName) {
                deleteServiceName.textContent = '—';
            }
        });
    }

    if (deleteServiceForm) {
        deleteServiceForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (isDeleting) return;
            const serviceId = deleteServiceIdInput.value;
            if (!serviceId) {
                notifyUser('No service selected for deletion.', 'error');
                return;
            }
            const formData = new FormData();
            formData.append('service_id', serviceId);
            formData.append('action', 'delete');

            isDeleting = true;

            if (confirmDeleteServiceBtn) {
                confirmDeleteServiceBtn.disabled = true;
                confirmDeleteServiceBtn.textContent = 'Deleting...';
            }
            try {
                const response = await fetch(
                    '../php/manage_services.php',
                    {
                        method: 'POST',
                        body: formData
                    }
                );
                if (!response.ok) {
                    throw new Error('Failed to delete service.');
                }
                const data = await response.json();
                if (data.status === 'success') {
                    notifyUser(
                        data.message || 'Service deleted successfully.',
                        'success'
                    );
                    if (deleteServiceModal && deleteServiceModal.open) {
                        deleteServiceModal.close();
                    }
                    loadServices();
                } else {
                    notifyUser(
                        data.message || 'Failed to delete service.',
                        'error'
                    );
                }
            } catch (error) {
                console.error('Error deleting service:', error);
                notifyUser(
                    'An unexpected error occurred while deleting the service.',
                    'error'
                );
            } finally {
                isDeleting = false;
                if (confirmDeleteServiceBtn) {
                    confirmDeleteServiceBtn.disabled = false;

                    confirmDeleteServiceBtn.innerHTML = `
                        <svg viewBox="0 0 24 24"
                             width="17" height="17"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="2"
                             stroke-linecap="round"
                             stroke-linejoin="round"
                             aria-hidden="true">
                            <path d="M3 6h18M8 6V4h8v2M5 6l1 14h12l1-14"/>
                        </svg>
                        <span>Delete Service</span>
                    `;
                }
            }
        });
    }
    loadServices();
});

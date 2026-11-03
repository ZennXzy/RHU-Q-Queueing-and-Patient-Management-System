document.addEventListener('DOMContentLoaded', () => {
    // 1. Fetch active services to populate the filter dropdown once with duplicate prevention
    fetch('../api/get_services.php?active_only=true')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.services) {
                const serviceFilter = document.getElementById('waiting-service-filter');
                if (serviceFilter) {
                    serviceFilter.innerHTML = '<option value="">All Services</option>';
                    
                    const seenServices = new Set();
                    data.services.forEach(service => {
                        if (!seenServices.has(service.service_name)) {
                            seenServices.add(service.service_name);
                            const option = document.createElement('option');
                            option.value = service.service_name;
                            option.textContent = service.service_name;
                            serviceFilter.appendChild(option);
                        }
                    });
                }
            }
        })
        .catch(error => console.error('Error fetching services for filter:', error));

    // 2. Main data polling loop
    async function fetchQueueManagerData() {
        try {
            const response = await fetch('../api/get_queue_manager_data.php');
            const result = await response.json();

            if (result.status === 'success') {
                updateWaitingQueue(result.waiting);
                updateInProgressQueue(result.in_progress);
                updateDashboardStats(result.counts);
                
                // Re-apply filter and empty placeholders after DOM updates
                applyServiceFilter();
            }
        } catch (error) {
            console.error('Error loading queue manager data:', error);
        }
    }

    function updateWaitingQueue(waitingList) {
        const container = document.getElementById('waiting-card-scroll');
        if (!container) return;

        if (waitingList.length > 0) {
            container.innerHTML = waitingList.map(row => `
                <div class="patient-row waiting-patient-row" data-service="${escapeHtml(row.service_name)}">
                    <span class="queue-id">${escapeHtml(row.queue_code)}</span>
                    <span class="patient-name">${escapeHtml(row.patient_name)}</span>
                    <span class="service-type">${escapeHtml(row.service_name)}</span>
                    <div class="action-container">
                        <button type="button" class="btn-view-queue" 
                            data-id="${row.queue_info_id}" 
                            data-code="${escapeHtml(row.queue_code)}" 
                            data-name="${escapeHtml(row.patient_name)}" 
                            title="View queue information">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                        <button type="button" class="btn-delete" 
                            data-id="${row.queue_info_id}" 
                            data-code="${escapeHtml(row.queue_code)}" 
                            data-name="${escapeHtml(row.patient_name)}">
                            <img src="../svg/delete-icon.svg" alt="delete-icon">
                        </button>
                        <button type="button" class="btn-accept" 
                            data-id="${row.queue_info_id}" 
                            data-code="${escapeHtml(row.queue_code)}" 
                            data-name="${escapeHtml(row.patient_name)}">
                            <img src="../svg/check-icon.svg" alt="check-icon">
                        </button>
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = `
                <div class="empty-state" style="text-align: center; padding: 51px; color: #6b7280; font-size: 14px;" data-is-empty-placeholder="true">
                    <p style="margin: 0;">No Waiting patient in records found.</p>
                </div>`;
        }
    }

    function updateInProgressQueue(inProgressList) {
        const container = document.getElementById('in-progress-scroll');
        if (!container) return;

        if (inProgressList.length > 0) {
            container.innerHTML = inProgressList.map(row => `
                <div class="patient-row in-progress-patient-row" data-service="${escapeHtml(row.service_name)}" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 15px; border-bottom: 1px solid #e2e8f0;">
                    <span class="queue-id" style="font-weight: bold; color: #2563eb;">${escapeHtml(row.queue_code)}</span>
                    <span class="patient-name">${escapeHtml(row.patient_name)}</span>
                    <span class="service-type">${escapeHtml(row.service_name)}</span>
                    <div class="action-container">
                        <button type="button" class="btn-view-queue" 
                            data-id="${row.queue_info_id}" 
                            data-code="${escapeHtml(row.queue_code)}" 
                            data-name="${escapeHtml(row.patient_name)}" 
                            title="View queue information">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                        <button type="button" class="btn-delete" 
                            data-id="${row.queue_info_id}" 
                            data-code="${escapeHtml(row.queue_code)}" 
                            data-name="${escapeHtml(row.patient_name)}">
                            <img src="../svg/delete-icon.svg" alt="delete-icon">
                        </button>
                        <button type="button" class="btn-complete" 
                            data-id="${row.queue_info_id}" 
                            data-code="${escapeHtml(row.queue_code)}" 
                            data-name="${escapeHtml(row.patient_name)}"
                            data-service="${escapeHtml(row.service_name)}"
                            title="Complete Queue">
                            Complete
                        </button>
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = `
                <div class="empty-state" style="text-align: center; padding: 51px; color: #6b7280; font-size: 14px;" data-is-empty-placeholder="true">
                    <p style="margin: 0;">No in-progress records found.</p>
                </div>`;
        }
    }

    function updateDashboardStats(counts) {
        const waitingStat = document.getElementById('stat-number-waiting');
        const inProgressStat = document.getElementById('stat-number-in-progress');
        const completedStat = document.getElementById('stat-number-completed');

        if (waitingStat) waitingStat.textContent = counts.waiting;
        if (inProgressStat) inProgressStat.textContent = counts.in_progress;
        if (completedStat) completedStat.textContent = counts.completed;
    }

    // 3. Unified filtering logic with dynamic empty-state placeholders for both lists
    function applyServiceFilter() {
        const serviceFilter = document.getElementById('waiting-service-filter');
        if (!serviceFilter) return;
        
        const selectedService = serviceFilter.value.trim().toLowerCase();
        
        // --- Filter Waiting Queue ---
        const waitingContainer = document.getElementById('waiting-card-scroll');
        if (waitingContainer) {
            const waitingRows = waitingContainer.querySelectorAll('.waiting-patient-row');
            let visibleWaitingCount = 0;

            waitingRows.forEach(row => {
                const serviceTypeEl = row.querySelector('.service-type');
                if (!serviceTypeEl) return;
                const serviceName = serviceTypeEl.textContent.trim().toLowerCase();
                
                if (selectedService === '' || serviceName === selectedService) {
                    row.style.display = 'flex';
                    visibleWaitingCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Handle dynamic empty placeholder for waiting list
            let waitingEmptyMsg = waitingContainer.querySelector('.filter-empty-waiting');
            if (visibleWaitingCount === 0 && waitingRows.length > 0) {
                if (!waitingEmptyMsg) {
                    waitingEmptyMsg = document.createElement('div');
                    waitingEmptyMsg.className = 'empty-state filter-empty-waiting';
                    waitingEmptyMsg.style.cssText = 'text-align: center; padding: 51px; color: #6b7280; font-size: 14px;';
                    waitingEmptyMsg.innerHTML = '<p style="margin: 0;">No current waiting queue for the selected service.</p>';
                    waitingContainer.appendChild(waitingEmptyMsg);
                } else {
                    waitingEmptyMsg.style.display = 'block';
                }
            } else if (waitingEmptyMsg) {
                waitingEmptyMsg.style.display = 'none';
            }
        }

        // --- Filter In-Progress Queue ---
        const inProgressContainer = document.getElementById('in-progress-scroll');
        if (inProgressContainer) {
            const inProgressRows = inProgressContainer.querySelectorAll('.in-progress-patient-row');
            let visibleInProgressCount = 0;

            inProgressRows.forEach(row => {
                const serviceTypeEl = row.querySelector('.service-type');
                if (!serviceTypeEl) return;
                const serviceName = serviceTypeEl.textContent.trim().toLowerCase();
                
                if (selectedService === '' || serviceName === selectedService) {
                    row.style.display = 'flex';
                    visibleInProgressCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Handle dynamic empty placeholder for in-progress list
            let inProgressEmptyMsg = inProgressContainer.querySelector('.filter-empty-inprogress');
            if (visibleInProgressCount === 0 && inProgressRows.length > 0) {
                if (!inProgressEmptyMsg) {
                    inProgressEmptyMsg = document.createElement('div');
                    inProgressEmptyMsg.className = 'empty-state filter-empty-inprogress';
                    inProgressEmptyMsg.style.cssText = 'text-align: center; padding: 51px; color: #6b7280; font-size: 14px;';
                    inProgressEmptyMsg.innerHTML = '<p style="margin: 0;">No in-progress queue for the selected service.</p>';
                    inProgressContainer.appendChild(inProgressEmptyMsg);
                } else {
                    inProgressEmptyMsg.style.display = 'block';
                }
            } else if (inProgressEmptyMsg) {
                inProgressEmptyMsg.style.display = 'none';
            }
        }
    }

    const serviceFilter = document.getElementById('waiting-service-filter');
    if (serviceFilter) {
        serviceFilter.addEventListener('change', applyServiceFilter);
    }

    function escapeHtml(str) {
        return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // Auto-refresh interval (every 3 seconds)
    setInterval(fetchQueueManagerData, 3000);
});
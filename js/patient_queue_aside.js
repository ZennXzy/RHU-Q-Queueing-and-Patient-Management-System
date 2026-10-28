document.addEventListener('DOMContentLoaded', () => {
    const asidePanel = document.getElementById('queueAsidePanel');
    const toggleBtn = document.getElementById('queueToggleBtn');
    const closeBtn = document.getElementById('queueCloseBtn');
    const queueBody = document.getElementById('queueAsideBody');

    if (!asidePanel) return;

    // 1. Handle Local Storage Preference for Collapse State
    const isCollapsed = localStorage.getItem('queueAsideCollapsed') === 'true';
    if (isCollapsed) {
        asidePanel.classList.add('collapsed');
    }

    const togglePanel = () => {
        asidePanel.classList.toggle('collapsed');
        localStorage.setItem('queueAsideCollapsed', asidePanel.classList.contains('collapsed'));
    };

    if (toggleBtn) toggleBtn.addEventListener('click', togglePanel);
    if (closeBtn) closeBtn.addEventListener('click', togglePanel);

    // 2. Fetch Active Queue Data Asynchronously
    const fetchPatientQueue = async () => {
        try {
            const response = await fetch('../php/patient_queue_info_aside.php', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.status === 'success') {
                if (data.hasQueue && data.queue) {
                    const q = data.queue;
                    var statusClass = q.status.toLowerCase();
                    if (q.status === 'In Progress'){
                        statusClass = 'serving'
                    }

                    // Render queue info with delete button aligned to the status row
                    queueBody.innerHTML = `
                        <div class="queue-info-card">
                            <div class="queue-header-row" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <div class="queue-status-badge ${statusClass}">
                                    ${escapeHtml(q.status)}
                                </div>
                                ${q.status.toLowerCase() === 'waiting' ? `
                                    <button class="btn-delete-queue" data-id="${escapeHtml(String(q.id))}" title="Cancel Queue Request" style="background-color: #dc3545; color: white; border: none; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; cursor: pointer; transition: background-color 0.2s;">
                                        Cancel Queue
                                    </button>
                                ` : ''}
                            </div>
                            <div class="queue-detail-item">
                                <span class="label">Queue ID:</span>
                                <span class="value">#${escapeHtml(String(q.queue_code))}</span>
                            </div>
                            <div class="queue-detail-item">
                                <span class="label">Service:</span>
                                <span class="value">${escapeHtml(q.service_snapshot)}</span>
                            </div>
                            <div class="queue-detail-item">
                                <span class="label">Requested At:</span>
                                <span class="value">${escapeHtml(q.created_at)}</span>
                            </div>
                            <div class="queue-detail-item description">
                                <span class="label">Vital Signs:</span>
                                <hr>
                                <div class="queue-detail-item">
                                    <span class="label">Blood Pressure:</span>
                                    <span class="value">${escapeHtml(q.blood_pressure)}(mmHg)</span>
                                </div>
                                <div class="queue-detail-item">
                                    <span class="label">Respiratory Rate:</span>
                                    <span class="value">${escapeHtml(q.respiratory_rate)}(cpm)</span>
                                </div>
                                <div class="queue-detail-item">
                                    <span class="label">Temperature:</span>
                                    <span class="value">${escapeHtml(q.temperature)}(°C)</span>
                                </div>
                                <div class="queue-detail-item">
                                    <span class="label">Pulse Rate:</span>
                                    <span class="value">${escapeHtml(q.pulse_rate)}(bpm)</span>
                                </div>
                                <div class="queue-detail-item">
                                    <span class="label">Weight:</span>
                                    <span class="value">${escapeHtml(q.weight)}(kg)</span>
                                </div>
                                <div class="queue-detail-item">
                                    <span class="label">Height:</span>
                                    <span class="value">${escapeHtml(q.height)}(cm)</span>
                                </div>
                            </div>

                            ${q.diagnosis ? `
                            <div class="queue-detail-item description">
                                <span class="label">Notes:</span>
                                <span class="value">${escapeHtml(q.diagnosis)}</span>
                            </div>` : ''}
                        </div>
                    `;

                    // Attach click handler for the newly rendered delete button
                    const deleteBtn = queueBody.querySelector('.btn-delete-queue');
                    if (deleteBtn) {
                        deleteBtn.addEventListener('click', async () => {
                            if (!confirm('Are you sure you want to cancel this queue request?')) {
                                return;
                            }

                            const queueId = deleteBtn.getAttribute('data-id');
                            try {
                                const formData = new FormData();
                                formData.append('queue_id', queueId);

                                const delResponse = await fetch('../php/patient_delete_queue.php', {
                                    method: 'POST',
                                    body: formData,
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest'
                                    }
                                });

                                const delResult = await delResponse.json();

                                if (delResult.status === 'success') {
                                    if (typeof showToast === 'function') {
                                        showToast(delResult.message, 'success');
                                    }
                                    // Refresh the aside content back to "no active queue"
                                    fetchPatientQueue();
                                } else {
                                    if (typeof showToast === 'function') {
                                        showToast(delResult.message || 'Failed to cancel queue.', 'error');
                                    } else {
                                        alert(delResult.message || 'Failed to cancel queue.');
                                    }
                                }
                            } catch (err) {
                                console.error('Error cancelling queue:', err);
                                alert('Server error while cancelling queue.');
                            }
                        });
                    }

                } else {
                    queueBody.innerHTML = `
                        <div class="no-active-queue">
                            <p>You have no active queue request at the moment.</p>
                            <a href="../pages/patient_add_queue.php" class="btn-request-queue">Request a Queue</a>
                        </div>
                    `;
                }
            } else {
                queueBody.innerHTML = `<div class="queue-error">Failed to load queue status.</div>`;
            }
        } catch (err) {
            console.error('Error fetching queue details:', err);
            queueBody.innerHTML = `<div class="queue-error">Network error loading queue status.</div>`;
        }
    };

    // Helper function to prevent XSS output rendering vulnerabilities
    function escapeHtml(str) {
        return str.replace(/&/g, "&amp;")
                  .replace(/</g, "&lt;")
                  .replace(/>/g, "&gt;")
                  .replace(/"/g, "&quot;")
                  .replace(/'/g, "&#039;");
    }

    // Trigger fetch on load
    fetchPatientQueue();
});
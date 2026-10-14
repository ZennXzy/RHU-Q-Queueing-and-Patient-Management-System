document.addEventListener('DOMContentLoaded', () => {
  const queueInfoModal = document.getElementById('queueInfoModal');
  const qiCloseBtn = document.getElementById('qiCloseBtn');
  const qiFooterCloseBtn = document.getElementById('qiFooterCloseBtn');

  const qiQueueCode = document.getElementById('qiQueueCode');
  const qiStatus = document.getElementById('qiStatus');
  const qiPatient = document.getElementById('qiPatient');
  const qiService = document.getElementById('qiService');
  const qiDate = document.getElementById('qiDate');
  const qiDiagnosis = document.getElementById('qiDiagnosis');

  // Helper to update vital sign tile values
  const updateVitalTile = (vitalKey, value) => {
    const tile = document.querySelector(`.qinfo-tile[data-vital="${vitalKey}"] .qinfo-tile__value`);
    if (tile) {
      tile.textContent = value || '--';
    }
  };

  // 1. OPEN MODAL & FETCH DETAILS VIA DELEGATED EVENT LISTENER
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.btn-view-queue');
    if (!btn) return;

    const queueId = btn.getAttribute('data-id');
    if (!queueId) return;

    // Reset fields & show loading state
    qiQueueCode.textContent = btn.getAttribute('data-code') || '--';
    qiPatient.textContent = btn.getAttribute('data-name') || '--';
    qiService.textContent = 'Loading...';
    qiDate.textContent = 'Loading...';
    qiDiagnosis.textContent = 'Loading...';
    
    ['blood_pressure', 'respiratory_rate', 'temperature', 'pulse_rate', 'weight', 'height'].forEach(v => updateVitalTile(v, '--'));

    if (queueInfoModal && typeof queueInfoModal.showModal === 'function') {
      queueInfoModal.showModal();
    }

    try {
      const response = await fetch(`../api/get_queue_info.php?queue_info_id=${queueId}`);
      if (!response.ok) throw new Error('Failed to fetch queue details.');

      const data = await response.json();

      if (data.status === 'success') {
        const info = data.queue;
        qiQueueCode.textContent = info.queue_code || '--';
        qiPatient.textContent = info.patient_name || '--';
        qiService.textContent = info.service_name || '--';
        qiDate.textContent = info.created_at || '--';
        qiDiagnosis.textContent = info.complaints || 'No complaints or diagnosis recorded.';

        // Populate Vitals
        updateVitalTile('blood_pressure', info.blood_pressure);
        updateVitalTile('respiratory_rate', info.respiratory_rate);
        updateVitalTile('temperature', info.temperature ? `${info.temperature}°C` : '');
        updateVitalTile('pulse_rate', info.pulse_rate);
        updateVitalTile('weight', info.weight ? `${info.weight} kg` : '');
        updateVitalTile('height', info.height ? `${info.height} cm` : '');
      } else {
        qiService.textContent = 'Error loading data';
        qiDiagnosis.textContent = data.message || 'Could not retrieve details.';
      }
    } catch (err) {
      console.error('Error fetching queue info:', err);
      qiService.textContent = 'Server Error';
      qiDiagnosis.textContent = 'Failed to communicate with server.';
    }
  });

  // 2. CLOSE MODAL VIA BUTTONS
  if (qiCloseBtn && queueInfoModal) {
    qiCloseBtn.addEventListener('click', () => queueInfoModal.close());
  }

  if (qiFooterCloseBtn && queueInfoModal) {
    qiFooterCloseBtn.addEventListener('click', () => queueInfoModal.close());
  }

  // 3. CLOSE MODAL ON BACKDROP CLICK
  if (queueInfoModal) {
    queueInfoModal.addEventListener('click', (e) => {
      const rect = queueInfoModal.getBoundingClientRect();
      const isInModal =
        rect.top <= e.clientY &&
        e.clientY <= rect.top + rect.height &&
        rect.left <= e.clientX &&
        e.clientX <= rect.left + rect.width;
      if (!isInModal) {
        queueInfoModal.close();
      }
    });
  }
});
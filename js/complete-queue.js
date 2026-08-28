document.addEventListener('click', async (e) => {
  const completeBtn = e.target.closest('.btn-complete');
  if (!completeBtn) return;

  e.preventDefault();

  const queueInfoId = completeBtn.getAttribute('data-queue-id');
  const patientName   = completeBtn.getAttribute('data-patient-name');
  const queueCode   = completeBtn.getAttribute('data-queue-code');
  const service     = completeBtn.getAttribute('data-service');


  if (!queueInfoId || !patientName) {
    showToast('Missing record details.', 'error');
    return;
  }

  completeBtn.disabled = true;

  try {
    const formData = new FormData();
    formData.append('queue_info_id', queueInfoId);
    formData.append('patient_name', patientName);
    formData.append('queue_code', queueCode);
    formData.append('service', service);


    const response = await fetch('../php/complete-queue.php', {
      method: 'POST',
      body: formData
    });

    const result = await response.json();

    if (result.status === 'success') {
      showToast('Patient service completed and recorded.', 'success');

      if (typeof reloadInProgressTable === 'function') reloadInProgressTable();
      if (typeof reloadTotalRecord === 'function') reloadTotalRecord();
    } else {
      showToast('Error: ' + (result.message || 'Failed to complete transaction.'), 'error');
      completeBtn.disabled = false;
    }
  } catch (error) {
    console.error('Error completing transaction:', error);
    showToast('Server connection error.', 'error');
    completeBtn.disabled = false;
  }
});
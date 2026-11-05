document.addEventListener('click', async (e) => {
  const acceptBtn = e.target.closest('.btn-accept');
  if (!acceptBtn) return;

  e.preventDefault();
  
  const queueInfoId = acceptBtn.getAttribute('data-id') || acceptBtn.dataset.id; 
  const queueCode   = acceptBtn.getAttribute('data-code') || acceptBtn.dataset.code; 

  if (!queueInfoId) {
    showToast('Invalid Queue ID.', 'error');
    return;
  }

  acceptBtn.disabled = true;

  try {
    const formData = new FormData();
    formData.append('queue_info_id', queueInfoId);

    const response = await fetch('../php/accept-queue.php', {
      method: 'POST',
      body: formData
    });

    const result = await response.json();

    if (result.status === 'success') {
      showToast(`${queueCode} Accepted.`, 'success');
    } else {
      showToast('Error updating status: ' + (result.message || 'Failed to process request.'), 'error');
      acceptBtn.disabled = false;
    }
  } catch (error) {
    console.error('Error accepting patient:', error);
    showToast('Server connection error.', 'error');
    acceptBtn.disabled = false;
  }
});
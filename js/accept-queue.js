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
      if (result.audio) {
        console.log('Audio received. Attempting to play...');
        const audio = new Audio('data:audio/mpeg;base64,' + result.audio);
        
        try {
          await audio.play();
          console.log('Audio played successfully!');
        } catch (playError) {
          console.error('Audio playback blocked or failed:', playError);
          showToast('Audio autoplay blocked by browser.', 'warning');
        }
      } else {
        console.warn('Status success, but no audio data was returned from backend.');
      }

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
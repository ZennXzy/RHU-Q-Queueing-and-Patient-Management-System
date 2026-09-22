function announcePatient(queueCode, patientName) {
  if (!('speechSynthesis' in window)) {
    console.warn('Text-to-speech is not supported in this browser.');
    return;
  }

  window.speechSynthesis.cancel();

  // Safe fallback to prevent crash if values are missing
  const safeCode = queueCode || '';
  const safeName = patientName || '';
  const formattedCode = safeCode.split('').join(' '); 
  const textToSpeak = `Queue number, ${formattedCode}, ${safeName}. Please proceed to the station.`;

  const utterance1 = new SpeechSynthesisUtterance(textToSpeak);
  utterance1.rate = 0.85; 
  utterance1.pitch = 1.0;
  utterance1.lang = 'en-US';

  const utterance2 = new SpeechSynthesisUtterance(textToSpeak);
  utterance2.rate = 0.85;
  utterance2.pitch = 1.0;
  utterance2.lang = 'en-US';

  utterance1.onend = () => {
    window.speechSynthesis.speak(utterance2);
  };

  window.speechSynthesis.speak(utterance1);
}

document.addEventListener('click', async (e) => {
  const acceptBtn = e.target.closest('.btn-accept');
  if (!acceptBtn) return;

  e.preventDefault();
  
  const queueInfoId = acceptBtn.getAttribute('data-id') || acceptBtn.dataset.id; 
  const queueCode   = acceptBtn.getAttribute('data-code') || acceptBtn.dataset.code;  
  const patientName = acceptBtn.getAttribute('data-name') || acceptBtn.dataset.name; 

  console.log('Clicked Button Data:', { queueInfoId, queueCode, patientName });

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
      if (queueCode && patientName) {
        announcePatient(queueCode, patientName);
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
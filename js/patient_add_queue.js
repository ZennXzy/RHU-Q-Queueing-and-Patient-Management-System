document.addEventListener('DOMContentLoaded', () => {
  const addQueueForm = document.getElementById('addQueueForm');
  const serviceSelect = document.getElementById('service');

  if (!addQueueForm) return;

  addQueueForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    // 1. Client-side Input Validation
    if (!serviceSelect || serviceSelect.value === "") {
      if (typeof showToast === 'function') {
        showToast('Please select a health service before submitting.', 'error');
      } else {
        alert('Please select a health service before submitting.');
      }
      serviceSelect.focus();
      return;
    }

    // Optional: Validate numeric input ranges if required
    const tempInput = document.getElementById('temperature');
    if (tempInput && tempInput.value) {
      const tempVal = parseFloat(tempInput.value);
      if (tempVal < 30 || tempVal > 45) {
        if (typeof showToast === 'function') {
          showToast('Please enter a valid body temperature (°C).', 'error');
        } else {
          alert('Please enter a valid body temperature (°C).');
        }
        tempInput.focus();
        return;
      }
    }

    // 2. Prepare Form Data
    const formData = new FormData(addQueueForm);

    try {
      // Explicitly targeted to the patient-specific PHP handler
      const response = await fetch('../php/patient_add_queue_process.php', {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
      });

      const result = await response.json();

      if (result.status === 'success') {
        if (typeof showToast === 'function') {
          showToast(result.message || 'Queue request submitted successfully!', 'success');
        }

        // Redirect to queue display without opening print dialogs
        setTimeout(() => {
          window.location.href = result.redirect || 'queue_display_page.php?msg=' + encodeURIComponent(result.message) + '&type=success';
        }, 1200);

      } else {
        if (typeof showToast === 'function') {
          showToast(result.message || 'Failed to submit queue. Please try again.', 'error');
        } else {
          alert(result.message || 'Failed to submit queue.');
        }
      }
    } catch (err) {
      console.error('Submission error:', err);
      if (typeof showToast === 'function') {
        showToast('Server error during queue submission. Please try again.', 'error');
      } else {
        alert('Server error during queue submission.');
      }
    }
  });
});
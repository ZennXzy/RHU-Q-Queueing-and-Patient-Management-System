document.addEventListener('DOMContentLoaded', () => {
  const queue_form_modal = document.getElementById('queue-form');
  const queue_form = document.getElementById('add-queue-form');
  const patient_search = document.getElementById('patient-search');
  const patient_id_input = document.getElementById('patient-id');
  const results_container = document.getElementById('patient-search-results');
  const serviceSelect = document.getElementById('service-type');

  const success_modal = document.getElementById('queue-success-modal');
  const modalQueueCode = document.getElementById('modalQueueCode');
  const modalPatientName = document.getElementById('modalPatientName');
  const modalServiceName = document.getElementById('modalServiceName');
  const modalDateTime = document.getElementById('modalDateTime');
  const successCountdown = document.getElementById('successCountdown');

  // Fetch and populate active services for the add-queue form dropdown
  if (serviceSelect) {
    fetch('../api/get_services.php?active_only=true')
      .then(response => response.json())
      .then(data => {
        if (data.status === 'success' && data.services) {
          serviceSelect.innerHTML = '<option value="" disabled selected>Select a service</option>';
          const seenServices = new Set();
          data.services.forEach(service => {
            if (!seenServices.has(service.service_name)) {
              seenServices.add(service.service_name);
              const option = document.createElement('option');
              option.value = service.service_name;
              option.textContent = service.service_name;
              serviceSelect.appendChild(option);
            }
          });
        }
      })
      .catch(error => console.error('Error fetching services for queue form:', error));
  }

  // 1. OPEN MODAL
  document.addEventListener('click', (e) => {
    const add_queue_button = e.target.closest('.add-queue-button');
    if (!add_queue_button) return;

    // Reset fields on open
    queue_form.reset();
    patient_id_input.value = '';
    patient_search.value = '';
    results_container.style.display = 'none';

    if (queue_form_modal && typeof queue_form_modal.showModal === 'function') {
      queue_form_modal.showModal();
    }
  });

  // 2. LIVE SEARCH PATIENT INPUT
  let debounceTimer;
  patient_search.addEventListener('input', (e) => {
    const query = e.target.value.trim();
    patient_id_input.value = '';

    if (query.length < 1) {
      results_container.style.display = 'none';
      results_container.innerHTML = '';
      return;
    }

    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(async () => {
      try {
        const response = await fetch(`../php/queue-form-search-patients.php?query=${encodeURIComponent(query)}`);
        const patients = await response.json();

        results_container.innerHTML = '';

        if (patients.length > 0) {
          patients.forEach(patient => {
            const item = document.createElement('div');
            item.className = 'search-result-item';
            item.textContent = `${patient.name} (ID: ${patient.id})`;

            item.addEventListener('click', () => {
              patient_search.value = patient.name;
              patient_id_input.value = patient.id;
              results_container.style.display = 'none';
            });

            results_container.appendChild(item);
          });
        } else {
          results_container.innerHTML = '<div class="search-no-result">No patient found</div>';
        }

        results_container.style.display = 'block';
      } catch (error) {
        console.error('Error fetching search results:', error);
      }
    }, 250);
  });

  // Close modal when clicking on backdrop
  if (queue_form_modal) {
    queue_form_modal.addEventListener("click", (e) => {
      const rect = queue_form_modal.getBoundingClientRect();
      const isInModal =
        rect.top <= e.clientY &&
        e.clientY <= rect.top + rect.height &&
        rect.left <= e.clientX &&
        e.clientX <= rect.left + rect.width;
      if (!isInModal) {
        queue_form_modal.close();
      }
    });
  }

  // 3. SUBMIT FORM & SHOW 5-SECOND SUCCESS MODAL
  if (queue_form) {
    queue_form.addEventListener('submit', async (e) => {
      e.preventDefault();

      if (!patient_id_input.value) {
        showToast('Please select a valid patient from the search list.', 'error');
        return;
      }

      const formData = new FormData(queue_form);

      try {
        const response = await fetch('../php/add-to-queue.php', {
          method: 'POST',
          body: formData
        });

        const result = await response.json();

        if (result.status === 'success') {
          queue_form_modal.close();
          
          // Populate success modal fields
          modalQueueCode.textContent = result.queue_code || '';
          modalPatientName.textContent = result.patient_name || '';
          modalServiceName.textContent = result.service_name || '';
          modalDateTime.textContent = result.datetime || '';

          // Open 5-second auto-close modal
          if (success_modal && typeof success_modal.showModal === 'function') {
            // 1. Populate the hidden receipt template
            document.getElementById('printQueueCode').textContent = result.queue_code;
            document.getElementById('printPatientName').textContent = result.patient_name;
            document.getElementById('printServiceName').textContent = result.service_name;
            document.getElementById('printDateTime').textContent = result.datetime;

            // 2. Automatically trigger the thermal print dialog
            window.print();
            success_modal.showModal();
            let secondsLeft = 5;
            successCountdown.textContent = secondsLeft;

            const countdownInterval = setInterval(() => {
              secondsLeft--;
              successCountdown.textContent = secondsLeft;

              if (secondsLeft <= 0) {
                clearInterval(countdownInterval);
                success_modal.close();
                window.location.reload(); // Refresh to reflect updated queue list
              }
            }, 1000);
          } else {
            showToast('Patient Added to Queue Successfully.', 'success');
            setTimeout(() => window.location.reload(), 1500);
          }

        } else {
          showToast(result.message || 'Failed to add to queue.', 'error');
        }
      } catch (err) {
        console.error('Form submit error:', err);
        showToast('Server error while submitting form.', 'error');
      }
    });
  }
});
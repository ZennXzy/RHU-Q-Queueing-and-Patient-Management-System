document.addEventListener('DOMContentLoaded', () => {
  const queue_form_modal = document.getElementById('queue-form');
  const queue_form = document.getElementById('add-queue-form');
  const patient_search = document.getElementById('patient-search');
  const patient_id_input = document.getElementById('patient-id');
  const results_container = document.getElementById('patient-search-results');

  // 1. OPEN MODAL
  document.addEventListener('click', (e) => {
    const add_queue_button = e.target.closest('.add-queue-button');
    if (!add_queue_button) return;

    // Reset fields on open
    queue_form.reset();
    patient_id_input.value = '';
    results_container.style.display = 'none';

    if (queue_form_modal && typeof queue_form_modal.showModal === 'function') {
      queue_form_modal.showModal();
    }
  });

  // 2. LIVE SEARCH PATIENT INPUT
  let debounceTimer;
  patient_search.addEventListener('input', (e) => {
    const query = e.target.value.trim();
    
    // Clear selected ID if user modifies search text
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

            // On selecting a patient
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

  if (queue_form) {
    queue_form.addEventListener('submit', async (e) => {
      e.preventDefault();

      if (!patient_id_input.value) {
        showToast('Please select a valid patient from the search list.','error');
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
          showToast('Patient Added to Queue Successfully.','success');
        } else {
          showToast('Error: ' + (result.message || 'Failed to add to queue.','error'));

        }
      } catch (err) {
        console.error('Form submit error:', err);
      }
    });
  }
});
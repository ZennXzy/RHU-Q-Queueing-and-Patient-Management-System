document.addEventListener('DOMContentLoaded', () => {
  const deleteModal = document.getElementById('deleteConfirmModal');
  const deleteForm = document.getElementById('deletePatientForm');
  const deleteIdInput = document.getElementById('deletePatientId');
  const passwordInput = document.getElementById('deleteAdminPassword');
  const errorMsg = document.getElementById('deleteErrorMsg');

  // 1. OPEN DELETE MODAL ON BUTTON CLICK
  document.addEventListener('click', (e) => {
    const deleteBtn = e.target.closest('.delete-btn');
    if (!deleteBtn) return;

    const patientId = deleteBtn.dataset.id;
    
    // Reset form fields
    deleteIdInput.value = patientId;
    passwordInput.value = '';
    if (errorMsg) {
      errorMsg.style.display = 'none';
      errorMsg.textContent = '';
    }

    if (deleteModal && typeof deleteModal.showModal === 'function') {
      deleteModal.showModal();
    }
  });

  // 2. SUBMIT DELETE REQUEST
  if (deleteForm) {
    deleteForm.addEventListener('submit', async (e) => {
      e.preventDefault();

      const formData = new FormData(deleteForm);

      try {
        const response = await fetch('../php/delete_patient.php', {
          method: 'POST',
          body: formData
        });

        const result = await response.json();

        if (result.status === 'success') {
          deleteModal.close();
          location.reload(); // Refresh table to display updated dataset
        } else {
          if (errorMsg) {
            errorMsg.textContent = result.message || 'Incorrect password or deletion failed.';
            errorMsg.style.display = 'block';
          }
        }
      } catch (error) {
        console.error('Error executing delete action:', error);
        if (errorMsg) {
          errorMsg.textContent = 'Server connection error.';
          errorMsg.style.display = 'block';
        }
      }
    });
  }
});
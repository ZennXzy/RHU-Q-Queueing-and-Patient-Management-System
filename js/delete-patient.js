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
          showToast('Record Deleted Successfully.','success');
          location.reload();
        } else {
          if (errorMsg) {
            deleteModal.close();
            showToast('Incorrect password or deletion failed.','error');
          }
        }
      } catch (error) {
        console.error('Error executing delete action:', error);
        if (errorMsg) {
          showToast('Server connection error.','error');
        }
      }
    });
  }
});
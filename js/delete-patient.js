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

  function reloadPatientTable() {
    fetch('../php/patient_table.php')
        .then(response => response.text())
        .then(html => {
            document.getElementById('patientTableBody').innerHTML = html;
        })
        .catch(error => {
            console.error('Error loading table:', error);
        });   
  }
  function reloadTotalRecord(){
    fetch('../php/total_records.php')
        .then(response => response.text())
        .then(html => {
            document.getElementById('total-records').innerHTML = html;
        })
        .catch(error => {
            console.error('Error loading total records:', error);
        }); 
  }

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
          reloadPatientTable();
          reloadTotalRecord();
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
document.addEventListener('DOMContentLoaded', () => {
  const deleteModal = document.getElementById('deleteAccountModal');
  const deleteForm = document.getElementById('deleteAccountForm');
  const deleteIdInput = document.getElementById('deleteAccountId');
  const deleteTypeInput = document.getElementById('deleteAccountType');
  const passwordInput = document.getElementById('deleteAdminPassword');

  // Open modal on delete button click[cite: 22]
  document.addEventListener('click', (e) => {
    const deleteBtn = e.target.closest('.delete-btn');
    if (!deleteBtn) return;

    const accountId = deleteBtn.dataset.id;
    const accountType = deleteBtn.dataset.type;
    
    deleteIdInput.value = accountId;
    deleteTypeInput.value = accountType;
    passwordInput.value = '';

    if (deleteModal && typeof deleteModal.showModal === 'function') {
      deleteModal.showModal();
    }
  });

  // Handle form submission via fetch[cite: 22]
  if (deleteForm) {
    deleteForm.addEventListener('submit', async (e) => {
      e.preventDefault();

      const formData = new FormData(deleteForm);

      try {
        const response = await fetch('../php/delete_account.php', {
          method: 'POST',
          body: formData
        });

        const result = await response.json();

        if (result.status === 'success') {
          deleteModal.close();
          showToast(result.message || 'Account Deleted Successfully.', 'success');
          
          // Reload account table list if available
          const activeTab = document.querySelector('.tab-btn.active');
          const currentType = activeTab ? activeTab.getAttribute('data-type') : 'staff';
          if (typeof window.loadAccounts === 'function') {
            window.loadAccounts(currentType);
          } else {
            location.reload();
          }
        } else {
          showToast(result.message || 'Incorrect password or deletion failed.', 'error');
        }
      } catch (error) {
        console.error('Error executing delete action:', error);
        showToast('Server connection error.', 'error');
      }
    });
  }
});
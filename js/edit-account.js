document.addEventListener("DOMContentLoaded", () => {
  const editModal = document.getElementById('edit-account');
  const editForm = document.getElementById('edit-account-form');
  const editAccountIdInput = document.getElementById('editAccountId');

  // Load account data directly if viewing Settings Page
  if (editForm && editAccountIdInput && editAccountIdInput.value && !editModal) {
    loadAccountDetails(editAccountIdInput.value);
  }

  // Handle Edit Button Clicks from User Accounts Table Modal
  document.addEventListener('click', async (e) => {
    const editBtn = e.target.closest('.edit-btn');
    if (!editBtn) return;

    const accountId = editBtn.dataset.id;
    const roleType = editBtn.dataset.role || 'Staff';

    await loadAccountDetails(accountId, roleType);

    if (editModal && typeof editModal.showModal === 'function') {
      editModal.showModal();
    }
  });

  async function loadAccountDetails(accountId, roleType = '') {
    if (!accountId) return;

    try {
      const response = await fetch(`../api/get_accounts.php?action=details&id=${accountId}&role=${encodeURIComponent(roleType)}`);
      if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);

      const result = await response.json();

      if (result.status === 'success') {
        const account = result.account;
        const hasAdmin = result.has_admin;

        const badgeElement = document.getElementById('modalAccountIdBadge');
        if (badgeElement) badgeElement.textContent = `ID: #${String(accountId).padStart(4, '0')}`;

        const setVal = (id, val) => {
          const el = document.getElementById(id);
          if (el) el.value = val !== undefined && val !== null ? val : '';
        };

        setVal('editAccountId', accountId);
        setVal('editUsername', account.username);
        setVal('editFullname', account.fullname);
        setVal('editEmail', account.email);
        setVal('editRole', account.role);
        setVal('current_password', '');
        setVal('editPassword', '');

        const displayTitle = document.getElementById('displayFullName');
        if (displayTitle) displayTitle.textContent = account.fullname || account.username;

        const adminOption = document.querySelector('#editRole option[value="Admin"]');
        if (adminOption) {
          if (hasAdmin && account.role !== 'Admin') {
            adminOption.disabled = true;
            adminOption.textContent = 'Admin (Already Exists)';
          } else {
            adminOption.disabled = false;
            adminOption.textContent = 'Admin';
          }
        }
      } else {
        if (typeof showToast === 'function') {
          showToast('Failed to fetch account details: ' + (result.message || ''), 'error');
        }
      }
    } catch (error) {
      console.error('Error opening edit account form:', error);
      if (typeof showToast === 'function') {
        showToast('An error occurred while loading account details.', 'error');
      }
    }
  }

  if (editModal) {
    editModal.addEventListener("click", (e) => {
      const rect = editModal.getBoundingClientRect();
      const isInModal =
        rect.top <= e.clientY &&
        e.clientY <= rect.top + rect.height &&
        rect.left <= e.clientX &&
        e.clientX <= rect.left + rect.width;
      if (!isInModal) {
        editModal.close();
      }
    });
  }

  if (editForm) {
    editForm.addEventListener('submit', async (e) => {
      e.preventDefault();

      const formData = new FormData(editForm);

      try {
        const response = await fetch('../php/update_account.php', {
          method: 'POST',
          body: formData
        });

        const result = await response.json();

        if (result.status === 'success') {
          if (editModal && typeof editModal.close === 'function') {
            editModal.close();
          }
          if (typeof showToast === 'function') {
            showToast('Account Updated Successfully.', 'success');
          }
        } else {
          if (typeof showToast === 'function') {
            showToast('Update Failed: ' + (result.message || 'Unable to update account.'), 'error');
          }
        }
      } catch (error) {
        console.error('Error submitting account update:', error);
        if (typeof showToast === 'function') {
          showToast('An error occurred while saving account modifications.', 'error');
        }
      }
    });
  }
});
document.addEventListener("DOMContentLoaded", () => {
  const editModal = document.getElementById('edit-account');
  const editForm = document.getElementById('edit-account-form');
  const editAccountIdInput = document.getElementById('editAccountId');

  // Load account data directly if viewing Settings Page
  if (editForm && editAccountIdInput && editAccountIdInput.value && !editModal) {
    loadAccountDetails(editAccountIdInput.value);
  }

  // Handle Edit Button Clicks from User Accounts Table
  document.addEventListener('click', async (e) => {
    const editBtn = e.target.closest('.edit-btn');
    if (!editBtn) return;

    const accountId = editBtn.dataset.id;
    // Fix 1: Read data-type attribute ("patient" or "staff")
    const roleType = editBtn.dataset.type || editBtn.dataset.role || 'staff';

    await loadAccountDetails(accountId, roleType);

    if (editModal && typeof editModal.showModal === 'function') {
      editModal.showModal();
    }
  });

  async function loadAccountDetails(accountId, roleType = 'staff') {
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
        setVal('current_password', '');
        setVal('editPassword', '');

        // Fix 2: Dynamically add/update hidden target_role input for update_account.php
        let targetRoleInput = document.getElementById('editTargetRole');
        if (!targetRoleInput && editForm) {
          targetRoleInput = document.createElement('input');
          targetRoleInput.type = 'hidden';
          targetRoleInput.id = 'editTargetRole';
          targetRoleInput.name = 'target_role';
          editForm.appendChild(targetRoleInput);
        }
        if (targetRoleInput) {
          targetRoleInput.value = roleType.toLowerCase() === 'patient' ? 'Patient' : account.role;
        }

        // Fix 3: Handle Role Selector disabling & selection for Patients
        const roleSelect = document.getElementById('editRole');
        const adminOption = document.querySelector('#editRole option[value="Admin"]');

        if (roleSelect) {
          let patientOpt = roleSelect.querySelector('option[value="Patient"]');

          if (roleType.toLowerCase() === 'patient') {
            if (!patientOpt) {
              patientOpt = document.createElement('option');
              patientOpt.value = 'Patient';
              patientOpt.textContent = 'Patient';
              roleSelect.appendChild(patientOpt);
            }
            roleSelect.value = 'Patient';
            roleSelect.disabled = true;
          } else {
            if (patientOpt) patientOpt.remove();
            roleSelect.disabled = false;
            setVal('editRole', account.role);

            if (adminOption) {
              if (hasAdmin && account.role !== 'Admin') {
                adminOption.disabled = true;
                adminOption.textContent = 'Admin (Already Exists)';
              } else {
                adminOption.disabled = false;
                adminOption.textContent = 'Admin';
              }
            }
          }
        }

        const displayTitle = document.getElementById('displayFullName');
        if (displayTitle) displayTitle.textContent = account.fullname || account.username;

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

      // Fix 4: Explicitly append role value if disabled (disabled controls are ignored by FormData)
      const roleSelect = document.getElementById('editRole');
      if (roleSelect && roleSelect.disabled) {
        formData.append('role', roleSelect.value);
      }

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
          if (typeof loadAccounts === 'function') {
            loadAccounts();
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
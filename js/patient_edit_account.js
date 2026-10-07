document.addEventListener("DOMContentLoaded", () => {
  const patientForm = document.getElementById('edit-account-form');
  const accountIdInput = document.getElementById('editAccountId');

  // Load patient account details on page load
  if (patientForm && accountIdInput && accountIdInput.value) {
    loadPatientDetails(accountIdInput.value);
  }

  // Fetch details from get_accounts API
  async function loadPatientDetails(accountId) {
    if (!accountId) return;

    try {
      const response = await fetch(`../api/get_accounts.php?action=details&id=${accountId}&role=Patient`);
      if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);

      const result = await response.json();

      if (result.status === 'success') {
        const account = result.account;

        const setVal = (id, val) => {
          const el = document.getElementById(id);
          if (el) el.value = val !== undefined && val !== null ? val : '';
        };

        setVal('editAccountId', accountId);
        setVal('editAccountToken', account.account_token);
        setVal('editUsername', account.username);
        setVal('editFullname', account.fullname);
        setVal('editEmail', account.email);
        setVal('current_password', '');
        setVal('editPassword', '');

        const displayTitle = document.getElementById('displayFullName');
        if (displayTitle) displayTitle.textContent = account.fullname || account.username;

      } else {
        if (typeof showToast === 'function') {
          showToast('Failed to load settings: ' + (result.message || ''), 'error');
        }
      }
    } catch (error) {
      console.error('Error fetching patient settings:', error);
      if (typeof showToast === 'function') {
        showToast('An error occurred while loading account details.', 'error');
      }
    }
  }

  // Handle patient settings form submission
  if (patientForm) {
    patientForm.addEventListener('submit', async (e) => {
      e.preventDefault();

      const formData = new FormData(patientForm);

      try {
        const response = await fetch('../php/patient_update_account.php', {
          method: 'POST',
          body: formData
        });

        const result = await response.json();

        if (result.status === 'success') {
          if (typeof showToast === 'function') {
            showToast(result.message || 'Account Updated Successfully.', 'success');
          }
          const curPass = document.getElementById('current_password');
          const newPass = document.getElementById('editPassword');
          if (curPass) curPass.value = '';
          if (newPass) newPass.value = '';
        } else if (result.status === 'info') {
          if (typeof showToast === 'function') {
            showToast(result.message, 'info');
          }
        } else {
          if (typeof showToast === 'function') {
            showToast('Update Failed: ' + (result.message || 'Unable to save settings.'), 'error');
          }
        }
      } catch (error) {
        console.error('Error submitting patient settings:', error);
        if (typeof showToast === 'function') {
          showToast('An error occurred while saving account modifications.', 'error');
        }
      }
    });
  }
});
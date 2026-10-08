document.addEventListener('DOMContentLoaded', () => {
  const add_account_modal = document.getElementById('add-account');
  const add_account_form = document.getElementById('add-account-form');

  document.addEventListener('click', (e) => {
    const add_account_button = e.target.closest('.add-account-button');
    if (!add_account_button) return;

    add_account_form.reset();

    if (add_account_modal && typeof add_account_modal.showModal === 'function') {
      add_account_modal.showModal();
    }
  });

  if (add_account_modal) {
    add_account_modal.addEventListener("click", (e) => {
      const rect = add_account_modal.getBoundingClientRect();
      const isInModal =
        rect.top <= e.clientY &&
        e.clientY <= rect.top + rect.height &&
        rect.left <= e.clientX &&
        e.clientX <= rect.left + rect.width;
      if (!isInModal) {
        add_account_modal.close();
      }
    });
  }
  
  if (add_account_form) {
    add_account_form.addEventListener('submit', async (e) => {
      e.preventDefault();

      const formData = new FormData(add_account_form);

      const username = formData.get('username')?.toString().trim();
      const fullname = formData.get('fullname')?.toString().trim();
      const email    = formData.get('email')?.toString().trim();
      const password = formData.get('password')?.toString();

      if (!username || !fullname || !email || !password) {
        showToast('Please fill in all require_onced fields.', 'error');
        return; 
      }

      const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailPattern.test(email)) {
        showToast('Please enter a valid email address.', 'error');
        return;
      }

      if (password.length < 8) {
        showToast('Password must be at least 8 characters long.', 'error');
        return;
      }

      try {
        const response = await fetch('../php/add-account.php', {
          method: 'POST',
          body: formData
        });

        const result = await response.json();

        if (result.status === 'success') {
          add_account_form.reset(); 
          add_account_modal.close();
          showToast('User Added to the System.', 'success');
        } else {
          showToast(result.message || 'Failed to add user.', 'error');
        }
      } catch (err) {
        console.error('Form submit error:', err);
        showToast('An unexpected server error occurred.', 'error');
      }
    });
  }
});
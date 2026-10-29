document.addEventListener("DOMContentLoaded", () => {
    const openBtn = document.getElementById('openResetModalBtn');
    const closeBtn = document.getElementById('closeResetModalBtn');
    const modal = document.getElementById('resetModal');
    const form = document.getElementById('resetPasswordForm');

    if (openBtn && modal) {
        openBtn.addEventListener('click', (e) => {
            e.preventDefault();
            modal.showModal();
        });
    }

    if (closeBtn && modal) {
        closeBtn.addEventListener('click', () => {
            modal.close();
        });
    }

    // Close modal when clicking outside the dialog box backdrop
    if (modal) {
        modal.addEventListener('click', (e) => {
            const rect = modal.getBoundingClientRect();
            if (e.clientX < rect.left || e.clientX > rect.right || e.clientY < rect.top || e.clientY > rect.bottom) {
                modal.close();
            }
        });
    }

    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);

            try {
                const response = await fetch('../php/process_password_reset.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.status === 'success') {
                    if (typeof showToast === 'function') {
                        showToast(result.message, 'success');
                    } else {
                        alert(result.message);
                    }
                    modal.close();
                    form.reset();
                } else {
                    if (typeof showToast === 'function') {
                        showToast(result.message, 'error');
                    } else {
                        alert('Error: ' + result.message);
                    }
                }
            } catch (err) {
                console.error('Password reset error:', err);
                if (typeof showToast === 'function') {
                    showToast('An error occurred while resetting the password.', 'error');
                }
            }
        });
    }
});
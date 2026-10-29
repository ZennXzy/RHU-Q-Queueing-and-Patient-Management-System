document.addEventListener("DOMContentLoaded", () => {
    const openBtn = document.getElementById('openActivateModalBtn');
    const closeBtn = document.getElementById('closeActivateModalBtn');
    const modal = document.getElementById('activateModal');
    const form = document.getElementById('activateAccountForm');

    if (openBtn && modal) {
        openBtn.addEventListener('click', () => {
            modal.showModal();
        });
    }

    if (closeBtn && modal) {
        closeBtn.addEventListener('click', () => {
            modal.close();
        });
    }

    // Close modal when clicking outside the dialog box
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
                const response = await fetch('../php/activate_account.php', {
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
                    setTimeout(() => {
                        window.location.href = '../pages/login_page.php';
                    }, 1500);
                } else {
                    if (typeof showToast === 'function') {
                        showToast(result.message, 'error');
                    } else {
                        alert('Error: ' + result.message);
                    }
                }
            } catch (err) {
                console.error('Activation error:', err);
                if (typeof showToast === 'function') {
                    showToast('An error occurred during activation.', 'error');
                }
            }
        });
    }
});
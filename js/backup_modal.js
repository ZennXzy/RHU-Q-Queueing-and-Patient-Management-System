document.addEventListener('DOMContentLoaded', function () {
    // DOM Element References
    const modal = document.getElementById('backupModal');
    const openBtn = document.getElementById('openBackupModalBtn');
    const closeBtn = document.getElementById('closeBackupModalBtn');
    const cancelBtn = document.getElementById('cancelBackupModalBtn');
    const backupForm = document.getElementById('backupForm');
    
    const selectAllCheckbox = document.getElementById('selectAllTables');
    const tableCheckboxes = document.querySelectorAll('.table-checkbox');

    // Function to open the modal
    function openModal() {
        if (modal) {
            if (typeof modal.showModal === 'function') {
                modal.showModal(); // Standard <dialog> API
            }
        }
    }

    // Function to close the modal
    function closeModal() {
        if (modal) {
            if (typeof modal.close === 'function' && modal.open) {
                modal.close(); // Standard <dialog> API
            }
        }
    }

    // Event Listeners for Opening and Closing
    if (openBtn) {
        openBtn.addEventListener('click', openModal);
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }

    if (cancelBtn) {
        cancelBtn.addEventListener('click', closeModal);
    }

    // Close modal when clicking directly on the backdrop container
    if (modal) {
        modal.addEventListener('click', function (e) {
            const rect = modal.getBoundingClientRect();
            const isInDialog = (rect.top <= e.clientY && e.clientY <= rect.top + rect.height &&
                rect.left <= e.clientX && e.clientX <= rect.left + rect.width);
            if (!isInDialog) {
                closeModal();
            }
        });
    }

    // Close modal on 'Escape' key press
    window.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal && modal.open) {
            closeModal();
        }
    });

    // Select All / Unselect All checkbox logic
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            tableCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
        });
    }

    // Update 'Select All' checkbox state when individual checkboxes are toggled
    tableCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const allChecked = Array.from(tableCheckboxes).every(cb => cb.checked);
            const noneChecked = Array.from(tableCheckboxes).every(cb => !cb.checked);

            if (selectAllCheckbox) {
                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = !allChecked && !noneChecked;
            }
        });
    });

    // Form submission validation
    const unifiedForm = document.getElementById('unifiedBackupForm');
    if (unifiedForm) {
        unifiedForm.addEventListener('submit', function (e) {
            // Check if backup tab is active and validate tables
            const backupSection = document.getElementById('backupSection');
            if (backupSection && backupSection.style.display !== 'none') {
                const anySelected = Array.from(tableCheckboxes).some(cb => cb.checked);
                if (!anySelected) {
                    e.preventDefault();
                    alert('Please select at least one database table to back up.');
                    return;
                }
            }

            const submitBtn = document.getElementById('modalSubmitBtn');
            if (submitBtn) {
                submitBtn.disabled = true;
                const originalText = submitBtn.innerText;
                submitBtn.innerText = 'Processing...';

                // Re-enable button after download/action initiates
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerText = originalText;
                    closeModal();
                }, 10000);
            }
        });
    }
});
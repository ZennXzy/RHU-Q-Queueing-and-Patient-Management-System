// js/delete-queue.js
document.addEventListener("DOMContentLoaded", () => {
  const deleteModal = document.getElementById("delete-queue-modal");
  const cancelBtn = document.getElementById("cancelDeleteBtn");
  const confirmBtn = document.getElementById("confirmDeleteBtn");

  const patientNameEl = document.getElementById("deletePatientName");
  const queueCodeEl = document.getElementById("deleteQueueCode");

  let activeQueueId = null;
  let activeRow = null;

  document.addEventListener("click", (e) => {
    const deleteBtn = e.target.closest(".btn-delete");
    if (!deleteBtn) return;

    activeQueueId = deleteBtn.dataset.id;
    activeRow = deleteBtn.closest("tr");

    if (patientNameEl) patientNameEl.textContent = deleteBtn.dataset.name;
    if (queueCodeEl) queueCodeEl.textContent = deleteBtn.dataset.code;

    if (deleteModal) deleteModal.showModal();
  });

  if (cancelBtn) {
    cancelBtn.addEventListener("click", () => {
      if (deleteModal) deleteModal.close();
    });
  }

  if (deleteModal) {
    deleteModal.addEventListener("click", (e) => {
      const rect = deleteModal.getBoundingClientRect();
      const isInModal =
        rect.top <= e.clientY &&
        e.clientY <= rect.top + rect.height &&
        rect.left <= e.clientX &&
        e.clientX <= rect.left + rect.width;
      if (!isInModal) {
        deleteModal.close();
      }
    });
  }

  if (confirmBtn) {
    confirmBtn.addEventListener("click", async () => {
      if (!activeQueueId) return;

      try {
        const formData = new FormData();
        formData.append("queue_info_id", activeQueueId);

        const response = await fetch("../php/delete_queue.php", {
          method: "POST",
          body: formData
        });

        if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);

        const result = await response.json();

        if (result.status === "success") {
          if (deleteModal) deleteModal.close();
          if (activeRow) activeRow.remove();
          if (typeof showToast === "function") {
            showToast("Queue entry removed successfully.", "success");
          }
        } else {
          if (typeof showToast === "function") {
            showToast("Delete Failed: " + (result.message || "Unable to remove queue entry."), "error");
          }
        }
      } catch (error) {
        console.error("Error deleting queue entry:", error);
        if (typeof showToast === "function") {
          showToast("An error occurred while deleting the queue item.", "error");
        }
      }
    });
  }
});
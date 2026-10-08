document.addEventListener("DOMContentLoaded", () => {
  const completeModal = document.getElementById("complete-queue-modal");
  const completeForm = document.getElementById("completeQueueForm");
  const cancelBtn = document.getElementById("cancelCompleteBtn");
  const toggleAppointment = document.getElementById("toggleAppointment");
  const appointmentContainer = document.getElementById("appointmentContainer");
  const appointmentDate = document.getElementById("appointmentDate");

  document.addEventListener("click", (e) => {
    const completeBtn = e.target.closest(".btn-complete");
    if (!completeBtn) return;

    e.preventDefault();

    const queueInfoId = completeBtn.getAttribute("data-queue-id");
    const patientName = completeBtn.getAttribute("data-patient-name");
    const queueCode   = completeBtn.getAttribute("data-queue-code");
    const service     = completeBtn.getAttribute("data-service");

    if (!queueInfoId || !patientName) {
      if (typeof showToast === "function") showToast("Missing record details.", "error");
      return;
    }

    // Populate UI elements
    document.getElementById("completePatientName").textContent = patientName;
    document.getElementById("serviceName").textContent = service;
    document.getElementById("completeQueueCodeBadge").textContent = `QUEUE: #${queueCode || "0000"}`;

    // Populate hidden form inputs
    document.getElementById("completeQueueId").value = queueInfoId;
    document.getElementById("completePatientNameInput").value = patientName;
    document.getElementById("completeQueueCodeInput").value = queueCode;
    document.getElementById("completeServiceInput").value = service;

    // Reset form controls
    document.getElementById("completeRemarks").value = "";
    if (toggleAppointment) toggleAppointment.checked = false;
    if (appointmentContainer) appointmentContainer.classList.add("hidden");
    if (appointmentDate) {
      appointmentDate.require_onced = false;
      appointmentDate.value = "";
    }

    if (completeModal) completeModal.showModal();
  });

  if (toggleAppointment) {
    toggleAppointment.addEventListener("change", (e) => {
      if (e.target.checked) {
        appointmentContainer.classList.remove("hidden");
        appointmentDate.require_onced = true;
      } else {
        appointmentContainer.classList.add("hidden");
        appointmentDate.require_onced = false;
        appointmentDate.value = "";
      }
    });
  }

  if (cancelBtn) {
    cancelBtn.addEventListener("click", () => {
      if (completeModal) completeModal.close();
    });
  }

  if (completeModal) {
    completeModal.addEventListener("click", (e) => {
      const rect = completeModal.getBoundingClientRect();
      const isInModal =
        rect.top <= e.clientY &&
        e.clientY <= rect.top + rect.height &&
        rect.left <= e.clientX &&
        e.clientX <= rect.left + rect.width;
      if (!isInModal) {
        completeModal.close();
      }
    });
  }

  if (completeForm) {
    completeForm.addEventListener("submit", async (e) => {
      e.preventDefault();

      const submitBtn = completeForm.querySelector('button[type="submit"]');
      if (submitBtn) submitBtn.disabled = true;

      // FormData now includes all inputs automatically via input 'name' attributes
      const formData = new FormData(completeForm);

      for (const [key, value] of formData.entries()) {
        console.log(`${key}:`, value);
      }
      
      try {
        const response = await fetch("../php/complete-queue.php", {
          method: "POST",
          body: formData
        });

        const result = await response.json();

        if (result.status === "success") {
          completeModal.close();

          if (typeof showToast === "function") {
            showToast("Patient service completed and recorded.", "success");
          }

          if (typeof reloadInProgressTable === "function") reloadInProgressTable();
          if (typeof reloadTotalRecord === "function") reloadTotalRecord();
        } else {
          if (typeof showToast === "function") {
            showToast("Error: " + (result.message || "Failed to complete transaction."), "error");
          }
        }
      } catch (error) {
        console.error("Error completing transaction:", error);
        if (typeof showToast === "function") {
          showToast("Server connection error.", "error");
        }
      } finally {
        if (submitBtn) submitBtn.disabled = false;
      }
    });
  }
});
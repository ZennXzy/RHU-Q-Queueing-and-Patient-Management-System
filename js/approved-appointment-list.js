document.addEventListener("DOMContentLoaded", () => {
  const approvedContainer = document.getElementById("approvedAppointmentsList");
  const approvedCountBadge = document.getElementById("approvedCount");

  const loadApprovedAppointments = async () => {
    try {
      const response = await fetch("../php/approved_appointment_list.php");
      if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);

      const data = await response.json();

      if (data.status === "success") {
        if (approvedContainer) approvedContainer.innerHTML = data.html;
        if (approvedCountBadge) approvedCountBadge.textContent = data.total;
      } else {
        console.error("Error loading approved appointments:", data.message);
      }
    } catch (error) {
      console.error("Fetch error for approved appointments:", error);
    }
  };

  // Event handler delegation for Add to Queue button and Options menu
  if (approvedContainer) {
    approvedContainer.addEventListener("click", async (e) => {
      const completeBtn = e.target.closest(".btn-complete");

      if (completeBtn) {
        const appointmentId = completeBtn.dataset.id;
        const patientId = completeBtn.dataset.patientId;
        const service = completeBtn.dataset.service;

        await addToQueueAction({patientId, service });
        await deleteAppointment({appointmentId});
      }
    });
  }

  // Function to process adding patient to queue
  const addToQueueAction = async ({patientId, service }) => {
    try {
      const payload = new URLSearchParams({
        patient_id: patientId,
        service_name: service
      });

      const response = await fetch("../php/add-to-queue.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: payload.toString()
      });

      const result = await response.json();

      if (result.status === "success") {
        if (typeof showToast === "function") {
          showToast(result.message || "Added to queue successfully.", "success");
        }
        loadApprovedAppointments(); // Refresh approved list
      } else {
        if (typeof showToast === "function") {
          showToast("Error: " + (result.message || "Failed to add to queue."), "error");
        }
      }
    } catch (err) {
      console.error("Queue processing error:", err);
      if (typeof showToast === "function") {
        showToast("An error occurred while contacting the server.", "error");
      }
    }
  };

  const deleteAppointment = async ({appointmentId}) => {
    try {
      const payload = new URLSearchParams({
        appointment_id: appointmentId,
      });

      const response = await fetch("../php/remove_appointment.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: payload.toString()
      });

      const result = await response.json();
      loadApprovedAppointments();

      if (result.status === "error") {
        if (typeof showToast === "function") {
          showToast(result.message || "Failed to remove appointment from database", "error");
        }
      }
    } catch (err) {
      console.error("Queue processing error:", err);
      if (typeof showToast === "function") {
        showToast("An error occurred while contacting the server.", "error");
      }
    }
  };

  // Initial fetch
  loadApprovedAppointments();

  // Expose reload function globally
  window.reloadApprovedAppointments = loadApprovedAppointments;
});
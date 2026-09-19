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

  // Event handler delegation for Complete button and Options menu
  if (approvedContainer) {
    approvedContainer.addEventListener("click", async (e) => {
      const completeBtn = e.target.closest(".btn-complete");
      const optionsBtn = e.target.closest(".options-menu");

      if (completeBtn) {
        const appointmentId = completeBtn.dataset.id;
        const patientName = completeBtn.dataset.patientName;
        const service = completeBtn.dataset.service;

        // Optionally trigger the Complete Queue Modal or complete appointment workflow directly
        if (typeof openCompleteModal === "function") {
          openCompleteModal({ appointmentId, patientName, service });
        } else {
          await completeAppointmentAction(appointmentId);
        }
      } else if (optionsBtn) {
        const appointmentId = optionsBtn.dataset.id;
        // Handle options menu dropdown logic here
      }
    });
  }

  const completeAppointmentAction = async (id) => {
    try {
      const response = await fetch("../php/update_appointment_status.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `appointment_id=${encodeURIComponent(id)}&action=complete`
      });

      const result = await response.json();
      if (result.status === "success") {
        if (typeof showToast === "function") {
          showToast("Appointment marked as completed.", "success");
        }
        loadApprovedAppointments();
      } else {
        if (typeof showToast === "function") {
          showToast("Error: " + result.message, "error");
        }
      }
    } catch (err) {
      console.error("Action error:", err);
    }
  };

  // Initial fetch
  loadApprovedAppointments();

  // Expose reload function globally
  window.reloadApprovedAppointments = loadApprovedAppointments;
});
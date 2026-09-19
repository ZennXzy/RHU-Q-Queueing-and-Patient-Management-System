document.addEventListener("DOMContentLoaded", () => {
  const pendingContainer = document.getElementById("pendingAppointmentsList");
  const pendingCountBadge = document.getElementById("pendingCount");

  const loadPendingAppointments = async () => {
    try {
      const response = await fetch("../php/pending_appointment_list.php");
      if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);

      const data = await response.json();

      if (data.status === "success") {
        if (pendingContainer) pendingContainer.innerHTML = data.html;
        if (pendingCountBadge) pendingCountBadge.textContent = data.total;
      } else {
        console.error("Error loading pending appointments:", data.message);
      }
    } catch (error) {
      console.error("Fetch error for pending appointments:", error);
    }
  };

  // Delegate click events for Approve and Cancel/Decline buttons
  if (pendingContainer) {
    pendingContainer.addEventListener("click", async (e) => {
      const approveBtn = e.target.closest(".btn-approve");
      const cancelBtn = e.target.closest(".btn-cancel");

      if (approveBtn) {
        const appointmentId = approveBtn.dataset.id;
        await handleAppointmentAction(appointmentId, "approve");
      } else if (cancelBtn) {
        const appointmentId = cancelBtn.dataset.id;
        await handleAppointmentAction(appointmentId, "decline");
      }
    });
  }

  // Placeholder handler function for status updates
  const handleAppointmentAction = async (id, action) => {
    try {
      const response = await fetch("../php/update_appointment_status.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `appointment_id=${encodeURIComponent(id)}&action=${encodeURIComponent(action)}`
      });

      const result = await response.json();
      if (result.status === "success") {
        if (typeof showToast === "function") {
          showToast(`Appointment ${action}d successfully.`, "success");
        }
        loadPendingAppointments(); // Refresh list
      } else {
        if (typeof showToast === "function") {
          showToast("Error: " + result.message, "error");
        }
      }
    } catch (err) {
      console.error("Action processing error:", err);
    }
  };

  // Initial load
  loadPendingAppointments();
  
  // Expose function globally if you need to trigger reload after completing queue
  window.reloadPendingAppointments = loadPendingAppointments;
});
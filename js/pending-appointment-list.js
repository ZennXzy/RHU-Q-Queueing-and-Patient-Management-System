document.addEventListener("DOMContentLoaded", () => {
  const pendingContainer = document.getElementById("pendingAppointmentsList");
  const pendingCountBadge = document.getElementById("pendingCount");

  // Modal elements
  const declineModal = document.getElementById("declineModal");
  const declineForm = document.getElementById("declineForm");
  const declineAppointmentInput = document.getElementById("declineAppointmentId");
  const declineReasonInput = document.getElementById("declineReason");
  const closeDeclineModalBtn = document.getElementById("closeDeclineModal");
  const cancelDeclineModalBtn = document.getElementById("cancelDeclineBtn");

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

  // Helper function to close modal
  const closeModal = () => {
    if (declineModal) declineModal.style.display = "none";
  };

  // Event Delegation for Approve and Decline Buttons
  if (pendingContainer) {
    pendingContainer.addEventListener("click", async (e) => {
      const approveBtn = e.target.closest(".btn-approve");
      const cancelBtn = e.target.closest(".btn-cancel");

      if (approveBtn) {
        const appointmentId = approveBtn.dataset.id;
        await handleAppointmentAction(appointmentId, "approve");
      } else if (cancelBtn) {
        const appointmentId = cancelBtn.dataset.id;
        
        // Open Modal instead of executing immediate action
        if (declineModal && declineAppointmentInput && declineReasonInput) {
          declineAppointmentInput.value = appointmentId;
          declineReasonInput.value = "";
          declineModal.style.display = "flex";
        }
      }
    });
  }

  // Handle Decline Modal Form Submission via AJAX
  if (declineForm) {
    declineForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      
      const appointmentId = declineAppointmentInput.value;
      const reason = declineReasonInput.value.trim();

      if (!reason) {
        if (typeof showToast === "function") showToast("Please enter a reason for declining.", "error");
        return;
      }

      await handleAppointmentAction(appointmentId, "Decline", reason);
      closeModal();
    });
  }

  // Modal Close Listeners
  if (closeDeclineModalBtn) closeDeclineModalBtn.addEventListener("click", closeModal);
  if (cancelDeclineModalBtn) cancelDeclineModalBtn.addEventListener("click", closeModal);
  
  if (declineModal) {
    window.addEventListener("click", (e) => {
      if (e.target === declineModal) closeModal();
    });
  }

  // Action Handler (Handles both Approve and Decline)
  const handleAppointmentAction = async (id, action, reason = "") => {
    try {
      const payload = new URLSearchParams({
        appointment_id: id,
        action: action,
        reason: reason
      });

      const response = await fetch("../php/update_appointment_status.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: payload.toString()
      });

      const result = await response.json();

      if (result.status === "success") {
        if (typeof showToast === "function") {
          const actionText = action === "Approve" ? "Approved" : "Declined";
          showToast(`Appointment ${actionText} successfully.`, "success");
        }
        loadPendingAppointments(); // Refresh appointment list dynamically
      } else {
        if (typeof showToast === "function") {
          showToast("Error: " + (result.message || "Failed to process request"), "error");
        }
      }
    } catch (err) {
      console.error("Action processing error:", err);
      if (typeof showToast === "function") {
        showToast("An error occurred while contacting the server.", "error");
      }
    }
  };

  // Initial load
  loadPendingAppointments();
  
  // Expose reload function globally
  window.reloadPendingAppointments = loadPendingAppointments;
});
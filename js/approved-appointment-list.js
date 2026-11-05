
document.addEventListener("DOMContentLoaded", () => {
    const approvedContainer = document.getElementById("approvedAppointmentsList");
    const approvedCountBadge = document.getElementById("approvedCount");
    let isLoading = false;
    let isProcessing = false;

    const notify = (message, type) => {
        if (typeof showToast === "function") showToast(message, type);
        else if (type === "error") console.error(message);
    };

    const loadApprovedAppointments = async () => {
        if (!approvedContainer || isLoading || isProcessing) return;
        isLoading = true;

        try {
            const response = await fetch("../php/approved_appointment_list.php", {
                credentials: "same-origin",
                cache: "no-store"
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            const data = await response.json();
            if (data.status !== "success") throw new Error(data.message || "Unable to load appointments.");

            approvedContainer.innerHTML = data.html;
            if (approvedCountBadge) approvedCountBadge.textContent = data.total;
        } catch (error) {
            console.error("Approved appointments error:", error);
        } finally {
            isLoading = false;
        }
    };

    const addToQueue = async (patientId, service) => {
        const payload = new URLSearchParams({
            patient_id: patientId,
            service_name: service
        });

        const response = await fetch("../php/add-to-queue.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            credentials: "same-origin",
            body: payload.toString()
        });

        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        const result = await response.json();
        if (result.status !== "success") throw new Error(result.message || "Failed to add patient to queue.");
        return result;
    };

    const updateAppointmentStatus = async (appointmentId) => {
        const payload = new URLSearchParams({ appointment_id: appointmentId });

        const response = await fetch("../php/remove_appointment.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            credentials: "same-origin",
            body: payload.toString()
        });

        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        const result = await response.json();
        if (result.status !== "success") throw new Error(result.message || "Failed to update appointment status.");
        return result;
    };

    if (approvedContainer) {
        approvedContainer.addEventListener("click", async (event) => {
            const button = event.target.closest(".btn-complete");
            if (!button || button.disabled || isProcessing) return;

            const appointmentId = button.dataset.id;
            const patientId = button.dataset.patientId;
            const service = button.dataset.service;

            if (!appointmentId || !patientId || !service) {
                notify("Missing appointment information.", "error");
                return;
            }

            isProcessing = true;
            button.disabled = true;

            try {
                await addToQueue(patientId, service);

                try {
                    await updateAppointmentStatus(appointmentId);
                    notify("Patient added to queue successfully.", "success");
                } catch (error) {
                    console.error("Appointment status update error:", error);
                    notify("Patient added to queue, but appointment status was not updated. Verify the queue before retrying.", "error");
                }
            } catch (error) {
                console.error("Add to queue error:", error);
                notify(error.message || "Failed to add patient to queue.", "error");
            } finally {
                isProcessing = false;
                if (button.isConnected) button.disabled = false;
                await loadApprovedAppointments();

                if (typeof window.refreshAppointmentNotification === "function") {
                    window.refreshAppointmentNotification();
                }
            }
        });
    }

    loadApprovedAppointments();

    setInterval(() => {
        if (!document.hidden) loadApprovedAppointments();
    }, 3000);

    document.addEventListener("visibilitychange", () => {
        if (!document.hidden) loadApprovedAppointments();
    });

    window.reloadApprovedAppointments = loadApprovedAppointments;
});

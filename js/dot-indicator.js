document.addEventListener("DOMContentLoaded", () => {
  const indicator = document.querySelector('.indicator');

  const updateAppointmentIndicator = async () => {
    if (!indicator) return;

    try {
      const response = await fetch('../api/check_pending_appointments.php');
      if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);

      const data = await response.json();

      if (data.status === 'success' && data.hasPending) {
        indicator.classList.add('active'); 
      } else {
        indicator.classList.remove('active'); 
      }
    } catch (error) {
      console.error('Failed to update appointment indicator:', error);
    }
  };

  // Run on page load
  updateAppointmentIndicator();

  // Poll every 30 seconds to update in real-time
  setInterval(updateAppointmentIndicator, 30000);

  // Expose globally for instant UI triggers
  window.updateAppointmentIndicator = updateAppointmentIndicator;
});
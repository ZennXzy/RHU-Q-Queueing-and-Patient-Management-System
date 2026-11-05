document.addEventListener('DOMContentLoaded', () => {
  const sidebar = document.getElementById('patientSidebar');
  const overlay = document.getElementById('sidebarOverlay');
  const toggleBtn = document.getElementById('sidebarToggleBtn');
  const closeBtn = document.getElementById('closeSidebarBtn');

  // Appointment notification pulse
  const appointmentNav = document.getElementById('patientAppointmentNav');

  async function checkDueAppointments() {
      if (!appointmentNav) return;

      try {
          const response = await fetch('../php/patient_appointment_due_check.php', {
              cache: 'no-store'
          });

          if (!response.ok) throw new Error('Appointment check failed');

          const data = await response.json();

          appointmentNav.classList.toggle(
              'appointment-nav-pulse',
              data.status === 'success' && data.has_due_appointment === true
          );

      } catch (error) {
          console.error('Error checking due appointments:', error);
      }
  }

  checkDueAppointments();

  setInterval(checkDueAppointments, 3000);

  if (!sidebar) return;

  function openSidebar() {
    sidebar.classList.add('active');
    if (overlay) overlay.classList.add('active');
    localStorage.setItem('patientSidebarState', 'open');
  }

  function closeSidebar() {
    sidebar.classList.remove('active');
    if (overlay) overlay.classList.remove('active');
    localStorage.setItem('patientSidebarState', 'closed');
  }

  // Toggle button event
  if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
      if (sidebar.classList.contains('active')) {
        closeSidebar();
      } else {
        openSidebar();
      }
    });
  }

  // Close button inside sidebar
  if (closeBtn) {
    closeBtn.addEventListener('click', closeSidebar);
  }

  // Close when clicking outside overlay
  if (overlay) {
    overlay.addEventListener('click', closeSidebar);
  }

  // Restore state from previous page load (Desktop preference)
  const savedState = localStorage.getItem('patientSidebarState');
  if (savedState === 'open' && window.innerWidth >= 1024) {
    openSidebar();
  }
});
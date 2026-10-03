document.addEventListener('DOMContentLoaded', () => {
  const sidebar = document.getElementById('patientSidebar');
  const overlay = document.getElementById('sidebarOverlay');
  const toggleBtn = document.getElementById('sidebarToggleBtn');
  const closeBtn = document.getElementById('closeSidebarBtn');

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
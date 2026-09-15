document.addEventListener("DOMContentLoaded", () => {
  const checkSessionStatus = async () => {
    try {
      const response = await fetch('../php/check-private.php?ajax_check=1');
      if (!response.ok) return;

      const data = await response.json();

      if (data.status === 'logged_out' || data.status === 'unauthenticated') {
        window.location.href = '../pages/login_page.php?msg=Session Expired&type=info';
      }
    } catch (error) {
      console.error('Session check failed:', error);
    }
  };

  setInterval(checkSessionStatus, 5000);
});
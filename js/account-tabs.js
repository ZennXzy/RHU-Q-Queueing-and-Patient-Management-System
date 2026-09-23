document.addEventListener("DOMContentLoaded", () => {
  const tabButtons = document.querySelectorAll('.table-selector .tab-btn');
  const tableBody = document.getElementById('account-list');
  const statTotal = document.getElementById('stat-card-total');
  const statActive = document.getElementById('stat-card-active');
  
  let currentTab = 'staff';

  const loadAccountTable = async (type) => {
    try {
      const response = await fetch(`../php/account_table.php?type=${type}`);
      if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);

      const data = await response.json();

      if (data.status === 'success') {
        tableBody.innerHTML = data.html;
        if (statTotal) statTotal.textContent = data.total;
        if (statActive) statActive.textContent = data.active;
      }
    } catch (error) {
      console.error('Error switching account tab:', error);
    }
  };

  tabButtons.forEach(button => {
    button.addEventListener('click', (e) => {
      const targetType = e.target.dataset.type;
      if (targetType === currentTab) return;

      tabButtons.forEach(btn => btn.classList.remove('active'));
      e.target.classList.add('active');

      currentTab = targetType;
      loadAccountTable(currentTab);
    });
  });

  loadAccountTable(currentTab);
});
<aside class="sidebar">
  <div class="nav-group">
    <a href="#" class="menu-item">
          <div class="menu-content">
              <div class="menu-icon">
                  <img src="../svg/dashboard-monitor.svg" alt="dashboard_icon">
              </div>
              <span class="menu-text">Dashboard</span>
          </div>
      </a>
      <a href="../pages/register_page.php" class="menu-item">
          <div class="menu-content">
              <div class="menu-icon">
                  <img src="../svg/registration-paper.svg" alt="registration_icon">
              </div>
              <span class="menu-text">Registration</span>
          </div>
      </a>
      <div class="dropdown-group">
          <a href="javascript:void(0)" class="menu-item dropdown-toggle" onclick="toggleDropdown(this)">
              <div class="menu-content">
                  <div class="menu-icon">
                      <img src="../svg/queue-line.svg" alt="queue_icon">
                  </div>
                  <span class="menu-text">Queue</span>
              </div>
              <div class="dropdown-arrow">
                  <svg viewBox="0 0 24 24" width="14" height="14">
                      <polyline points="6 9 12 15 18 9"></polyline>
                  </svg>
              </div>
          </a> 
          <div class="submenu-container">
              <a href="../pages/queue_display_page.php" class="sub-nav-link">
                  <div class="menu-icon">         
                      <img src="../svg/queue-signal.svg" alt="queue_display_icon">
                  </div>
                  <span class="menu-text">Queue Display</span>
              </a> 
              
              <a href="../pages/queue_manager_page.php" class="sub-nav-link">
                  <div class="menu-icon">
                      <img src="../svg/queue-manager.svg" alt="queue-manager-icon">
                  </div>
                  <span class="menu-text">Queue Manager</span> 
              </a>
              <a href="../pages/display_settings.php" class="sub-nav-link">
                  <div class="menu-icon">
                      <img src="../svg/display-settings-icon.svg" alt="queue-manager-icon">
                  </div>
                  <span class="menu-text">Edit Display Cards</span> 
              </a>
          </div>
      </div>

      <a href="../pages/appointment_page.php" class="menu-item">
          <div class="menu-content">
              <div class="menu-icon">
                  <img src="../svg/reminder-appointment.svg" alt="appointment-icon">
              </div>
              <span class="menu-text">Appointment</span>
              <div class="indicator active">
                  <span class="dot-notification"></span>
              </div>
          </div>
      </a>
      <a href="#" class="menu-item">
          <div class="menu-content">
              <div class="menu-icon">
                  <img src="../svg/reports.svg" alt="reports-icon">
              </div>
              <span class="menu-text">Reports</span>
          </div>
      </a>

      <a href="../pages/patient_records.php" class="menu-item">
          <div class="menu-content">
              <div class="menu-icon">
                  <img src="../svg/records.svg" alt="records-icon">
              </div>
              <span class="menu-text">Patient Records</span>
          </div>
      </a>
      <a href="../pages/user_accounts_page.php" class="menu-item">
          <div class="menu-content">
              <div class="menu-icon">
                  <img src="../svg/user-accounts-icon.svg" alt="user-accounts-icon">
              </div>
              <span class="menu-text">User Accounts</span>
          </div>
      </a>
  </div>
  <div class="lowercontrol">
      <a href="../pages/accounts_page.php" class="menu-item account-item" style="width:200px; padding-left: 0px;">
          <div class="menu-content" style="justify-content: center;">
              <div class="menu-icon">
                  <img src="../svg/account-settings-icon.svg" alt="account-settings-icon">
              </div>
              <span class="menu-text">Settings</span>
          </div>
      </a>
      <form action="../php/logout.php" method="post" class="menu-item logout-item" id="logoutForm" style="transform: rotate(180deg);">
          <div class="menu-content">
              <div class="menu-icon">
                  <button type="submit" name="logout" style="border:none; background:none;">
                      <img src="../svg/logout-icon.svg" alt="logout-icon">
                  </button>
              </div>
          </div>
      </form>
  </div>
</aside>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const currentPath = window.location.pathname;
        const currentPage = currentPath.substring(currentPath.lastIndexOf("/") + 1);

        // 1. Highlight standard menu items
        const menuLinks = document.querySelectorAll(".sidebar .menu-item:not(.dropdown-toggle)");
        
        menuLinks.forEach((link) => {
            const href = link.getAttribute("href");
            if (href && href !== "#" && href.includes(currentPage)) {
            link.classList.add("active");
            }
        });

        // 2. Highlight sub-nav links and expand parent dropdown if inside a submenu
        const subNavLinks = document.querySelectorAll(".sidebar .sub-nav-link");

        subNavLinks.forEach((subLink) => {
            const href = subLink.getAttribute("href");
            if (href && href !== "#" && href.includes(currentPage)) {
            subLink.classList.add("active");

            // Expand parent dropdown group
            const parentDropdown = subLink.closest(".dropdown-group");
            if (parentDropdown) {
                const toggleBtn = parentDropdown.querySelector(".dropdown-toggle");
                const submenuContainer = parentDropdown.querySelector(".submenu-container");

                if (toggleBtn) toggleBtn.classList.add("active");
                if (submenuContainer) submenuContainer.style.display = "block";
            }
            }
        });
    });
</script>
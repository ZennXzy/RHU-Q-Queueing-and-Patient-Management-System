<?php
require_once '../php/connection.php';
$role = $_SESSION['logged_role'];
?>

<!-- Mobile/Tablet toggle (hidden on desktop via CSS) -->
<button type="button" class="sidebar-toggle" id="sidebarToggle" aria-label="Open menu" aria-expanded="false" aria-controls="mainSidebar">
  <span class="bar"></span>
  <span class="bar"></span>
  <span class="bar"></span>
</button>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="sidebar" id="mainSidebar">
  <div class="nav-group">
    <a href="../pages/dashboard_page.php" class="menu-item">
          <div class="menu-content">
              <div class="menu-icon">
                  <img src="../svg/dashboard-monitor.svg" alt="dashboard_icon">
              </div>
              <span class="menu-text">Dashboard</span>
          </div>
      </a>
      <?php if($role === 'Admin'){?>
      <a href="../pages/register_page.php" class="menu-item">
          <div class="menu-content">
              <div class="menu-icon">
                  <img src="../svg/registration-paper.svg" alt="registration_icon">
              </div>
              <span class="menu-text">Registration</span>
          </div>
      </a><?php }?>
        <?php if($role === "Admin" || $role === "Doctor/Staff"){ ?>   
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
      </div> <?php } else if ($role === "Patient"){ ?>
      <a href="../pages/queue_display_page.php" class="sub-nav-link">
            <div class="menu-icon">         
                <img src="../svg/queue-signal.svg" alt="queue_display_icon">
            </div>
            <span class="menu-text">Queue Display</span>
        </a>  <?php }?>

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
      <?php if($role === 'Admin'){?>
      <a href="../pages/reports_page.php" class="menu-item">
          <div class="menu-content">
              <div class="menu-icon">
                  <img src="../svg/reports.svg" alt="reports-icon">
              </div>
              <span class="menu-text">Reports</span>
          </div>
      </a><?php }?>

      <a href="../pages/patient_records.php" class="menu-item">
          <div class="menu-content">
              <div class="menu-icon">
                  <img src="../svg/records.svg" alt="records-icon">
              </div>
              <span class="menu-text">Patient Records</span>
          </div>
      </a>
      <?php if($role === 'Admin'){?>
      <a href="../pages/user_accounts_page.php" class="menu-item">
          <div class="menu-content">
              <div class="menu-icon">
                  <img src="../svg/user-accounts-icon.svg" alt="user-accounts-icon">
              </div>
              <span class="menu-text">User Accounts</span>
          </div>
      </a><?php }?>
  </div>
  <div class="lowercontrol">
      <a href="../pages/account_settings_page.php" class="menu-item account-item" style="width:200px; padding-left: 0px;">
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

        // 3. Sidebar toggle for tablet / mobile
        const sidebar = document.getElementById("mainSidebar");
        const toggleBtn = document.getElementById("sidebarToggle");
        const overlay = document.getElementById("sidebarOverlay");
        const desktopQuery = window.matchMedia("(min-width: 992px)");

        function setSidebarOpen(open) {
            sidebar.classList.toggle("open", open);
            overlay.classList.toggle("show", open);
            toggleBtn.classList.toggle("is-open", open);
            toggleBtn.setAttribute("aria-expanded", open ? "true" : "false");
            toggleBtn.setAttribute("aria-label", open ? "Close menu" : "Open menu");
            document.body.classList.toggle("sidebar-open", open);
        }

        toggleBtn.addEventListener("click", () => {
            setSidebarOpen(!sidebar.classList.contains("open"));
        });

        overlay.addEventListener("click", () => setSidebarOpen(false));

        document.addEventListener("keydown", (e) => {
            if (e.key === "Escape") setSidebarOpen(false);
        });

        // Close after choosing a page (but not when opening the Queue dropdown)
        sidebar.querySelectorAll("a[href]:not(.dropdown-toggle)").forEach((a) => {
            a.addEventListener("click", () => setSidebarOpen(false));
        });

        // Reset when resizing up to desktop
        desktopQuery.addEventListener("change", (e) => {
            if (e.matches) setSidebarOpen(false);
        });
    });
</script>
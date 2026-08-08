// Configuration dictionary for available services
const DEPARTMENT_CONFIG = {
  medical: {
    title: 'MEDICAL',
    themeClass: 'dept-medical',
    icon: '../svg/medical-icon.svg',
    filter: 'brightness(0) saturate(100%) invert(28%) sepia(65%) saturate(1329%) hue-rotate(185deg) brightness(91%) contrast(93%)'
  },
  dental: {
    title: 'DENTAL',
    themeClass: 'dept-dental',
    icon: '../svg/dental-icon.svg',
    filter: 'brightness(0) saturate(100%) invert(39%) sepia(55%) saturate(663%) hue-rotate(78deg) brightness(91%) contrast(92%)'
  },
  vaccine: {
    title: 'VACCINE',
    themeClass: 'dept-vaccine',
    icon: '../svg/vaccine-icon.svg',
    filter: 'brightness(0) saturate(100%) invert(53%) sepia(94%) saturate(1767%) hue-rotate(6deg) brightness(97%) contrast(99%)'
  },
  laboratory: {
    title: 'LABORATORY',
    themeClass: 'dept-medical', // Re-uses base theme styling or customized CSS class
    icon: '../svg/medical-icon.svg',
    filter: 'none'
  }
};

// Sample dynamic queue data (This can eventually come from your backend or database)
const SAMPLE_QUEUE_DATA = {
  medical: {
    activeTickets: ['A0011', 'A0013'],
    waitingTickets: ['A0014', 'A0016', 'A0015'],
    avgTime: '00:10:00'
  },
  dental: {
    activeTickets: [],
    waitingTickets: [],
    avgTime: '00:00:00'
  },
  vaccine: {
    activeTickets: ['C0012'],
    waitingTickets: ['C0017', 'C0018'],
    avgTime: '00:07:10'
  },
  laboratory: {
    activeTickets: ['L0001'],
    waitingTickets: ['L0002'],
    avgTime: '00:05:00'
  }
};

document.addEventListener('DOMContentLoaded', () => {
  const displayCardContainer = document.querySelector('.display-card');

  function renderQueueDisplay() {
    // Fetch active departments selected from Display Settings
    const activeDepts = JSON.parse(localStorage.getItem('activeDepartments')) || [];

    // Save reference to the minimize/fullscreen button at bottom right
    const minimizeBtn = displayCardContainer.querySelector('.minimize-btn');

    // Adjust grid columns dynamically based on active service count
    displayCardContainer.style.gridTemplateColumns = `repeat(${Math.max(activeDepts.length, 1)}, 1fr)`;
    displayCardContainer.innerHTML = '';


    // 1. Handle Empty State
    if (activeDepts.length === 0) {
      displayCardContainer.style.gridTemplateColumns = '1fr';
      displayCardContainer.innerHTML = `
        <div class="empty-display-state">
          <h2>No Available Services</h2>
          <p>Please configure display cards in the Display Settings.</p>
        </div>
      `;

      // Re-attach bottom toolbar button
      if (minimizeBtn) {
        displayCardContainer.appendChild(minimizeBtn);
      }
      return;
    }

    // Loop through active departments and build their unified column structure
    activeDepts.forEach(dept => {
      const config = DEPARTMENT_CONFIG[dept.id] || {
        title: dept.name.toUpperCase(),
        themeClass: 'dept-medical',
        icon: '../svg/medical-icon.svg',
        filter: 'none'
      };

      const queueData = SAMPLE_QUEUE_DATA[dept.id] || {
        activeTickets: [],
        waitingTickets: [],
        avgTime: '00:00:00'
      };

      // 1. Build Active Tickets HTML
      let activeTicketsHTML = '';
      if (queueData.activeTickets.length > 0) {
        activeTicketsHTML = queueData.activeTickets
          .map(ticket => `<div class="ticket-box">${ticket}</div>`)
          .join('');
      } else {
        activeTicketsHTML = `<div class="no-queue">No Queue</div>`;
      }

      // 2. Build Waiting List Badges HTML
      let waitingBadgesHTML = '';
      if (queueData.waitingTickets.length > 0) {
        waitingBadgesHTML = queueData.waitingTickets
          .map(ticket => `<span class="badge">${ticket}</span>`)
          .join('');
      } else {
        waitingBadgesHTML = `<span>- No waiting items -</span>`;
      }

      // 3. Assemble Unified Department Column (Card + Waiting List)
      const columnEl = document.createElement('div');
      columnEl.className = 'dept-column';
      columnEl.innerHTML = `
        <!-- Main Active Queue Card -->
        <div class="queue-card ${config.themeClass}">
            <div class="dept-title">
                <img src="${config.icon}" alt="${config.title}" style="filter: ${config.filter};"> ${config.title}
            </div>

            ${activeTicketsHTML}

            <div class="time-stat">
                <div class="label">Average Waiting Time</div>
                <div class="value">${queueData.avgTime}</div>
            </div>
        </div>

        <!-- Dynamic Waiting List Section -->
        <div class="waiting-card">
            <div class="waiting-column">
                <div class="waiting-section">
                    <div class="waiting-title">Waiting</div>
                    <div class="waiting-badges">
                        ${waitingBadgesHTML}
                    </div>
                </div>
            </div>
        </div>
      `;

      displayCardContainer.appendChild(columnEl);
    });

    // Re-attach bottom toolbar minimize button if present
    if (minimizeBtn) {
      displayCardContainer.appendChild(minimizeBtn);
    }
  }

  // Initial render when page loads
  renderQueueDisplay();

  // Listen to live updates triggered from display_settings.html tab
  window.addEventListener('storage', (e) => {
    if (e.key === 'activeDepartments') {
      renderQueueDisplay();
    }
  });
});
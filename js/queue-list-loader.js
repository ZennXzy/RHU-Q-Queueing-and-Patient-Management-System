function reloadWaitingTable() {
  fetch('../php/queue-list-waiting.php')
      .then(response => response.text())
      .then(html => {
          document.getElementById('waiting-card-scroll').innerHTML = html;
      })
      .catch(error => {
          console.error('Error loading list:', error);
      });
}

function reloadWaitingDashboard() {
  fetch('../php/queue-waiting-dashboard.php')
      .then(response => response.text())
      .then(html => {
          document.getElementById('stat-number-waiting').innerHTML = html;
      })
      .catch(error => {
          console.error('Error loading list:', error);
      });
}


function reloadInProgressTable() {
  fetch('../php/queue-list-in-progress.php')
      .then(response => response.text())
      .then(html => {
          document.getElementById('in-progress-scroll').innerHTML = html;
      })
      .catch(error => {
          console.error('Error loading list:', error);
      });
}


function reloadInProgressDashboard() {
  fetch('../php/queue-in-progress-dashboard.php')
      .then(response => response.text())
      .then(html => {
          document.getElementById('stat-number-in-progress').innerHTML = html;
      })
      .catch(error => {
          console.error('Error loading list:', error);
      });
}


function reloadCompleteDashboard() {
  fetch('../php/queue-completed-dashboard.php')
      .then(response => response.text())
      .then(html => {
          document.getElementById('stat-number-completed').innerHTML = html;
      })
      .catch(error => {
          console.error('Error loading list:', error);
      });
}



setInterval(() => {
  reloadWaitingTable();
  reloadWaitingDashboard();
  reloadInProgressTable();
  reloadInProgressDashboard();
  reloadCompleteDashboard();
}, 1000);
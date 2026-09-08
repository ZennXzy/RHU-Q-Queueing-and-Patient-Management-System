function reloadtable() {
  fetch('../php/account-list.php')
      .then(response => response.text())
      .then(html => {
          document.getElementById('account-list').innerHTML = html;
      })
      .catch(error => {
          console.error('Error loading list:', error);
      });
}

function reloadActive() {
  fetch('../php/account-active.php')
      .then(response => response.text())
      .then(html => {
          document.getElementById('stat-card-active').innerHTML = html;
      })
      .catch(error => {
          console.error('Error loading list:', error);
      });
}


function reloadTotal() {
  fetch('../php/account-total.php')
      .then(response => response.text())
      .then(html => {
          document.getElementById('stat-card-total').innerHTML = html;
      })
      .catch(error => {
          console.error('Error loading list:', error);
      });
}



setInterval(() => {
  reloadtable();
  reloadActive();
  reloadTotal();
}, 1000);
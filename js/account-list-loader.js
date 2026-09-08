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


setInterval(() => {
  reloadtable();
}, 1000);
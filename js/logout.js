var logoutForm = document.getElementById('logoutForm');
if (logoutForm){
  logoutForm.addEventListener('submit', function(e){
    if (!confirm('Are you sure you want to logout?')){
      e.preventDefault();
    }
  });
}
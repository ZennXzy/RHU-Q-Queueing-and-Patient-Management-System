(function(){
  var form = document.querySelector('form');
  if (!form) return;
  form.addEventListener('submit', function(e){
    var user = document.getElementById('username').value || '';
    var pass = document.getElementById('password').value || '';
    if (user.trim() === '' || pass.trim() === ''){
      e.preventDefault();
      showToast('Please provide both username and password', 'error');
      return false;
    }
    console.log(user);
    return true;
  });
})();
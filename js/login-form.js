function showToast(message, type) {
  var container = document.getElementById('toastContainer');
  if (!container) return;
  var toast = document.createElement('div');
  toast.className = 'toast ' + (type || 'info');
  toast.innerHTML = '<span class="icon">•</span><span>' + message + '</span>';
  container.appendChild(toast);
  setTimeout(function(){
    toast.style.animation = 'toast-out 300ms ease forwards';
    setTimeout(function(){ container.removeChild(toast); }, 300);
  }, 4000);
}

(function(){
  var params = new URLSearchParams(window.location.search);
  if (params.has('msg')){
    var msg = params.get('msg');
    var type = params.get('type') || 'info';
    showToast(decodeURIComponent(msg), type);
    if (history.replaceState) {
      var url = new URL(window.location);
      url.search = '';
      history.replaceState(null, '', url);
    }
  }
})();

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
    if (pass.length <= 10) {
      e.preventDefault();
      showToast('Password must be more than 10 characters', 'error');
      return false;
    }
    return true;
  });
})();
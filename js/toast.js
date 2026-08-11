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


(function () {
  'use strict';

  var logoutForm = document.getElementById('logoutForm');
  if (!logoutForm) return;

  /* ---------- Load the stylesheet (once) ---------- */
  if (!document.getElementById('logoutModalStyles')) {
    var script = document.currentScript;
    var cssUrl = script && script.src
      ? new URL('../styles/logout_modal.css', script.src).href
      : '../styles/logout_modal.css';

    var link = document.createElement('link');
    link.id = 'logoutModalStyles';
    link.rel = 'stylesheet';
    link.href = cssUrl;
    document.head.appendChild(link);
  }

  var dialog = null;
  var confirmBtn = null;
  var cancelBtn = null;
  var submitter = null;

  var dialogSupported = typeof HTMLDialogElement === 'function';

  /* ---------- Build the popup the first time it is needed ---------- */
  function buildDialog() {
    dialog = document.createElement('dialog');
    dialog.id = 'logoutModal';
    dialog.className = 'logout-modal';
    dialog.setAttribute('aria-labelledby', 'logoutModalTitle');
    dialog.setAttribute('aria-describedby', 'logoutModalText');

    dialog.innerHTML =
      '<div class="logout-modal__card">' +
        '<div class="logout-modal__icon" aria-hidden="true">' +
          '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" ' +
            'stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
            '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>' +
            '<polyline points="16 17 21 12 16 7"></polyline>' +
            '<line x1="21" y1="12" x2="9" y2="12"></line>' +
          '</svg>' +
        '</div>' +
        '<h2 class="logout-modal__title" id="logoutModalTitle">Log out of RHU-Q?</h2>' +
        '<p class="logout-modal__text" id="logoutModalText">' +
          'You will be signed out of your account and returned to the sign-in page.' +
        '</p>' +
        '<div class="logout-modal__actions">' +
          '<button type="button" class="logout-modal__btn logout-modal__btn--cancel" id="logoutCancelBtn" autofocus>Cancel</button>' +
          '<button type="button" class="logout-modal__btn logout-modal__btn--confirm" id="logoutConfirmBtn">Log out</button>' +
        '</div>' +
      '</div>';

    document.body.appendChild(dialog);

    confirmBtn = dialog.querySelector('#logoutConfirmBtn');
    cancelBtn = dialog.querySelector('#logoutCancelBtn');

    cancelBtn.addEventListener('click', function () {
      dialog.close();
    });

    confirmBtn.addEventListener('click', submitLogout);

    // Click on the dark area outside the popup
    dialog.addEventListener('click', function (event) {
      if (event.target === dialog) dialog.close();
    });

    // Reset the button if the popup is closed (Esc, Cancel, or outside click)
    dialog.addEventListener('close', function () {
      confirmBtn.disabled = false;
      confirmBtn.textContent = 'Log out';
    });
  }

  /* ---------- Send the logout request ---------- */
  function submitLogout() {
    if (confirmBtn) {
      confirmBtn.disabled = true;
      confirmBtn.textContent = 'Logging out...';
    }

    // form.submit() does not send the clicked button's name, and logout.php may
    // look for it, so add it back as a hidden field.
    var field = document.createElement('input');
    field.type = 'hidden';
    field.name = (submitter && submitter.name) ? submitter.name : 'logout';
    field.value = (submitter && submitter.value) ? submitter.value : '1';
    logoutForm.appendChild(field);

    // Call the real submit (does not trigger the "submit" event again)
    HTMLFormElement.prototype.submit.call(logoutForm);
  }

  /* ---------- Intercept the logout form ---------- */
  logoutForm.addEventListener('submit', function (event) {
    event.preventDefault();
    submitter = event.submitter || null;

    // Very old browsers without <dialog>: keep the original confirm box
    if (!dialogSupported) {
      if (window.confirm('Are you sure you want to logout?')) submitLogout();
      return;
    }

    if (!dialog) buildDialog();
    dialog.showModal();
    cancelBtn.focus(); // so pressing Enter does not log out by accident
  });
})();
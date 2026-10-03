document.addEventListener('DOMContentLoaded', () => {
  const regForm = document.getElementById('accountRegistrationForm');
  if (!regForm) return;

  // 1. Copy Token Button Event (Handles unfocused clipboard errors gracefully)
  const copyTokenBtn = document.getElementById('copyTokenBtn');
  if (copyTokenBtn) {
    copyTokenBtn.addEventListener('click', () => {
      const tokenInput = document.getElementById('account_token');
      if (!tokenInput || !tokenInput.value) return;

      // Ensure field is focused before copying
      tokenInput.focus();
      tokenInput.select();

      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(tokenInput.value)
          .then(() => {
            notifyUser('Account Token copied to clipboard!', 'success');
          })
          .catch(() => {
            // Fallback if writeText fails or document loses focus
            fallbackCopyText(tokenInput.value);
          });
      } else {
        fallbackCopyText(tokenInput.value);
      }
    });
  }

  // Fallback function using execCommand
  function fallbackCopyText(text) {
    try {
      const tempInput = document.createElement('input');
      tempInput.value = text;
      document.body.appendChild(tempInput);
      tempInput.select();
      const successful = document.execCommand('copy');
      document.body.removeChild(tempInput);

      if (successful) {
        notifyUser('Account Token copied to clipboard!', 'success');
      } else {
        notifyUser('Failed to copy token automatically. Please copy manually.', 'error');
      }
    } catch (err) {
      notifyUser('Failed to copy token automatically. Please copy manually.', 'error');
    }
  }

  // 2. Restore Form Draft from sessionStorage on Load
  const savedDraft = sessionStorage.getItem('accountFormDraft');
  if (savedDraft) {
    const draftData = JSON.parse(savedDraft);
    Object.keys(draftData).forEach((key) => {
      const field = regForm.elements[key];
      if (field) {
        if (field.type === 'radio' || field.type === 'checkbox') {
          const matchingOption = regForm.querySelector(`[name="${key}"][value="${draftData[key]}"]`);
          if (matchingOption) matchingOption.checked = true;
        } else {
          field.value = draftData[key];
        }
      }
    });
  }

  // Auto-save Input Changes
  regForm.addEventListener('input', () => {
    const formData = new FormData(regForm);
    const draftObj = {};
    formData.forEach((value, key) => {
      draftObj[key] = value;
    });
    sessionStorage.setItem('accountFormDraft', JSON.stringify(draftObj));
  });

  function notifyUser(message, type = 'error') {
    if (typeof showToast === 'function') {
      showToast(message, type);
    } else {
      alert(message);
    }
  }

  // 3. Step 1: Client-Side Validation & Server Draft Call
  const nextStepBtn = document.getElementById('nextStepBtn');
  if (nextStepBtn) {
    nextStepBtn.addEventListener('click', function () {
      const formData = new FormData(regForm);

      const fname = formData.get('fname') || '';
      const lname = formData.get('lname') || '';
      const address = formData.get('address') || '';
      const religion = formData.get('religion') || '';
      const birthDate = formData.get('birthDate') || '';
      const contactnum = formData.get('contactnum') || '';
      const sex = formData.get('gender') || '';
      const civilStatus = formData.get('civilStatus') || '';
      const employmentStatus = formData.get('employment_status') || '';

      if (lname.trim() === '' || fname.trim() === '') {
        notifyUser('Please provide your first and last name', 'error');
        return;
      }
      if (address.trim() === '') {
        notifyUser('Please provide your full address', 'error');
        return;
      }
      if (!religion || religion.trim() === '') {
        notifyUser('Please select/provide your religion', 'error');
        return;
      }
      if (sex.trim() === '') {
        notifyUser('Please select your gender', 'error');
        return;
      }
      if (birthDate.trim() === '') {
        notifyUser('Please provide your date of birth', 'error');
        return;
      }

      // Contact Number is now OPTIONAL (Only validate length if filled out)
      const cleanContact = contactnum.replace(/\D/g, '');
      if (cleanContact.length > 0 && cleanContact.length < 11) {
        notifyUser('Please provide a valid 11-digit contact number', 'error');
        return;
      }

      if (civilStatus.trim() === '') {
        notifyUser('Please select your civil status', 'error');
        return;
      }
      if (employmentStatus.trim() === '') {
        notifyUser('Please select an employment status', 'error');
        return;
      }

      const postData = new FormData(regForm);
      postData.append('action', 'check_and_draft');

      fetch('../php/patient_register.php', {
        method: 'POST',
        body: postData
      })
      .then(async response => {
        const text = await response.text();
        try { return JSON.parse(text); } 
        catch (err) { throw new Error('Server response error'); }
      })
      .then(data => {
        if (data.status === 'success') {
          // Set generated 6-digit token into read-only field
          if (data.account_token) {
            document.getElementById('account_token').value = data.account_token;
          }

          document.getElementById('patientInfoStep').style.display = 'none';
          document.getElementById('credentialsStep').style.display = 'block';
          const pageTitle = document.getElementById('pageTitle');
          if (pageTitle) pageTitle.innerText = 'Account Credentials Setup';
          window.scrollTo({ top: 0, behavior: 'smooth' });
        } else {
          notifyUser(data.message, 'error');
        }
      })
      .catch((err) => {
        console.error(err);
        notifyUser('An unexpected error occurred. Please try again.', 'error');
      });
    });
  }

  // 4. Back Button Step 2
  const backStepBtn = document.getElementById('backStepBtn');
  if (backStepBtn) {
    backStepBtn.addEventListener('click', function () {
      document.getElementById('credentialsStep').style.display = 'none';
      document.getElementById('patientInfoStep').style.display = 'block';
      const pageTitle = document.getElementById('pageTitle');
      if (pageTitle) pageTitle.innerText = 'Account Registration';
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  // 5. Step 2 Submit (Email Optional)
  regForm.addEventListener('submit', function (e) {
    e.preventDefault();

    const username = document.getElementById('username')?.value.trim() || '';
    const email = document.getElementById('email')?.value.trim() || '';
    const pwd = document.getElementById('password')?.value || '';
    const confirmPwd = document.getElementById('confirm_password')?.value || '';

    if (username === '') {
      notifyUser('Please choose a username.', 'error');
      return;
    }
    
    // Email optional check
    if (email.length > 0 && (!email.includes("@") || !email.includes("."))) {
      notifyUser('Please provide a valid email address.', 'error');
      return;
    }

    if (pwd.length < 6) {
      notifyUser('Password must be at least 6 characters long.', 'error');
      return;
    }

    if (pwd !== confirmPwd) {
      notifyUser('Passwords do not match.', 'error');
      return;
    }

    const postData = new FormData(regForm);
    postData.append('action', 'final_register');

    fetch('../php/patient_register.php', {
      method: 'POST',
      body: postData
    })
    .then(async response => {
      const text = await response.text();
      try { return JSON.parse(text); } 
      catch (err) { throw new Error('Server response error'); }
    })
    .then(data => {
      if (data.status === 'success') {
        sessionStorage.removeItem('accountFormDraft');
        alert(data.message);
        window.location.href = '../pages/login_page.php';
      } else {
        notifyUser(data.message, 'error');
      }
    })
    .catch((err) => {
      console.error(err);
      notifyUser('An unexpected error occurred during registration.', 'error');
    });
  });
});
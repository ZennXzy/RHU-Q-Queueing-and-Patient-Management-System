document.addEventListener('DOMContentLoaded', () => {
  const regForm = document.getElementById('accountRegistrationForm');
  if (!regForm) return;

  // 1. Restore Form Draft from sessionStorage on Load
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

  // 2. Auto-save Input Changes to sessionStorage
  regForm.addEventListener('input', () => {
    const formData = new FormData(regForm);
    const draftObj = {};
    formData.forEach((value, key) => {
      draftObj[key] = value;
    });
    sessionStorage.setItem('accountFormDraft', JSON.stringify(draftObj));
  });

  // Helper function for Toast / Alert notifications
  function notifyUser(message, type = 'error') {
    if (typeof showToast === 'function') {
      showToast(message, type);
    } else {
      alert(message);
    }
  }

  // 3. Step 1: Client-Side Validation + AJAX Duplication Check & Draft
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
      const occupation = formData.get('occupation') || '';
      const business = formData.get('business') || '';
      const philStatus = formData.get('philStatusInput') || '';
      const philNum = formData.get('philNumber') || '';
      const prinMem = formData.get('prinMember') || '';
      const yakapmem = formData.get('yakapmem') || '';

      // Clean age input string
      const ageInput = document.getElementById('age_display');
      if (ageInput) {
        ageInput.value = ageInput.value.replace(/\D/g, ''); 
      }

      // Validations
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

      if (contactnum.trim() === '' || contactnum.replace(/\D/g, '').length < 11) {
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
      } else if (employmentStatus === 'Employed' && occupation.trim() === '') {
        notifyUser('Please provide your occupation', 'error');
        return;
      } else if (employmentStatus === 'Self-Employed' && business.trim() === '') {
        notifyUser('Please provide your business name', 'error');
        return;
      }

      // Family Info Checks
      const mfname = formData.get('mfname') || document.getElementById('mfname')?.value || '';
      const mlname = formData.get('mlname') || document.getElementById('mlname')?.value || '';

      if (mfname.trim() === '' || mlname.trim() === '') {
        notifyUser("Please provide your mother's first and last name", 'error');
        return;
      }

      const ffname = formData.get('ffname') || document.getElementById('ffname')?.value || '';
      const flname = formData.get('flname') || document.getElementById('flname')?.value || '';

      if (ffname.trim() === '' || flname.trim() === '') {
        notifyUser("Please provide your father's first and last name", 'error');
        return;
      }
      
      const sfname = formData.get('sfname') || document.getElementById('sfname')?.value || '';
      const slname = formData.get('slname') || document.getElementById('slname')?.value || '';

      if (civilStatus === 'Married' || civilStatus === 'Widow') {
        if (sfname.trim() === '' || slname.trim() === '') {
          notifyUser("Please provide your spouse's first and last name", 'error');
          return;
        }
      }

      // PhilHealth Info Checks
      if (philStatus.trim() === '') {
        notifyUser('Please select PhilHealth status', 'error');
        return;
      } else if (philStatus === 'Member' && philNum.trim() === '') {
        notifyUser('Please provide your PhilHealth Number', 'error');
        return;
      } else if (philStatus === 'Dependent') {
        if (philNum.trim() === '' || prinMem.trim() === '') {
          notifyUser('Please provide PhilHealth Number and Principal Member Name.', 'error');
          return;
        }
      }

      if (yakapmem.trim() === '') {
        notifyUser('Please select Yakap Membership status', 'error');
        return;
      }

      // Send to Backend for Duplication Check & Server Draft Storage
      const postData = new FormData(regForm);
      postData.append('action', 'check_and_draft');

      fetch('../php/patient_register.php', {
        method: 'POST',
        body: postData
      })
      .then(async response => {
        const text = await response.text();
        try {
          return JSON.parse(text);
        } catch (err) {
          console.error('Server returned invalid JSON:', text);
          throw new Error('Server response error');
        }
      })
      .then(data => {
        if (data.status === 'success') {
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

  // 4. Back Button in Step 2
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

  // 5. Step 2: Final Registration AJAX
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
    
    if (email.length > 0 && !email.includes("@gmail.com")){
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
      try {
        return JSON.parse(text);
      } catch (err) {
        console.error('Server returned invalid JSON:', text);
        throw new Error('Server response error');
      }
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
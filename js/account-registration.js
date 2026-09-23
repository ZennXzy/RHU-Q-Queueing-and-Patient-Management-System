document.addEventListener('DOMContentLoaded', () => {
  const regForm = document.getElementById('accountRegistrationForm');
  if (!regForm) return;

  // 1. Restore Form Draft from sessionStorage on load
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

  // 2. Auto-save input changes to sessionStorage
  regForm.addEventListener('input', () => {
    const formData = new FormData(regForm);
    const draftObj = {};
    formData.forEach((value, key) => {
      draftObj[key] = value;
    });
    sessionStorage.setItem('accountFormDraft', JSON.stringify(draftObj));
  });

  // 3. Client-side Form Submit Listener
  regForm.addEventListener('submit', function(e) {
    const formData = new FormData(regForm);

    const fname = formData.get('fname') || '';
    const lname = formData.get('lname') || '';
    const address = formData.get('address') || '';

    const ageInput = document.getElementById('age_display');
    if (ageInput) {
      ageInput.value = ageInput.value.replace(/\D/g, ''); 
    }

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

    if (lname.trim() === '' || fname.trim() === '') {
      e.preventDefault();
      showToast('Please provide your first and last name', 'error');
      return;
    }

    if (address.trim() === '') {
      e.preventDefault();
      showToast('Please provide your full address', 'error');
      return;
    }

    if (religion.trim() === '') {
      e.preventDefault();
      showToast('Please select/provide your religion', 'error');
      return;
    }

    if (sex.trim() === '') {
      e.preventDefault();
      showToast('Please select your gender', 'error');
      return;
    }

    if (birthDate.trim() === '') {
      e.preventDefault();
      showToast('Please provide your date of birth', 'error');
      return;
    }

    if (contactnum.trim() === '' || contactnum.length < 11) {
      e.preventDefault();
      showToast('Please provide a valid 11-digit contact number', 'error');
      return;
    }
    
    if (emailContact.length > 0 && !emailContact.includes("@gmail.com")){
      e.preventDefault();
      showToast('Please provide a valid email address.', 'error');
      return;
    }

    if (civilStatus.trim() === '') {
      e.preventDefault();
      showToast('Please select your civil status', 'error');
      return;
    }
    
    if (employmentStatus.trim() === '') {
      e.preventDefault();
      showToast('Please select an employment status', 'error');
      return;
    } else if (employmentStatus === 'Employed' && occupation.trim() === ''){
      e.preventDefault();
      showToast('Please provide your occupation', 'error');
      return;
    } else if (employmentStatus === 'Self-Employed' && business.trim() === '') {
      e.preventDefault();
      showToast('Please provide your business name', 'error');
      return;
    }

    // Family Info Checks
    const mfname = formData.get('mfname') || document.getElementById('mfname')?.value || '';
    const mlname = formData.get('mlname') || document.getElementById('mlname')?.value || '';

    if (mfname.trim() === '' || mlname.trim() === '') {
      e.preventDefault();
      showToast("Please provide your mother's first and last name", 'error');
      return;
    }

    const ffname = formData.get('ffname') || document.getElementById('ffname')?.value || '';
    const flname = formData.get('flname') || document.getElementById('flname')?.value || '';

    if (ffname.trim() === '' || flname.trim() === '') {
      e.preventDefault();
      showToast("Please provide your father's first and last name", 'error');
      return;
    }
    
    const sfname = formData.get('sfname') || document.getElementById('sfname')?.value || '';
    const slname = formData.get('slname') || document.getElementById('slname')?.value || '';

    if (civilStatus === 'Married' || civilStatus === 'Widow') {
      if (sfname.trim() === '' || slname.trim() === '') {
        e.preventDefault();
        showToast("Please provide your spouse's first and last name", 'error');
        return;
      }
    }

    // PhilHealth Info
    if (philStatus.trim() === '') {
      e.preventDefault();
      showToast('Please select PhilHealth status', 'error');
      return;
    } else if (philStatus === 'Member' && philNum.trim() === '') {
      e.preventDefault();
      showToast('Please provide your PhilHealth Number', 'error');
      return;
    } else if (philStatus === 'Dependent') {
      if (philNum.trim() === '' || prinMem.trim() === ''){
        e.preventDefault();
        showToast('Please provide PhilHealth Number and Principal Member Name.', 'error');
        return;
      }
    }

    if (yakapmem.trim() === '') {
      e.preventDefault();
      showToast('Please select Yakap Membership status', 'error');
      return;
    }

    // Clear saved draft on successful form submission submission
    sessionStorage.removeItem('accountFormDraft');
  });
});
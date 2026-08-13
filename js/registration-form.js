const regForm = document.getElementById('patientRegistrationForm');
regForm.addEventListener('submit', function(e) {

  // Extract FormData values safely using regForm reference
  const formData = new FormData(regForm);
  
  // 1. Personal Details
  const fname = formData.get('fname') || document.getElementById('fname')?.value || '';
  const lname = formData.get('lname') || document.getElementById('lname')?.value || '';
  const mname = formData.get('mname') || document.getElementById('mname')?.value || '';
  const address = formData.get('address') || '';

  const ageInput = document.getElementById('age_display');
  if (ageInput) {
    // Overwrite the input's actual value with digits only (e.g. "24")
    ageInput.value = ageInput.value.replace(/\D/g, ''); 
  }

  const religion = formData.get('religion') || document.getElementById('religion')?.value || '';
  const birthDate = formData.get('birthDate') || document.getElementById('date_of_birth')?.value || '';
  const contactnum = formData.get('contactnum') || document.getElementById('contactnum')?.value || '';
  const emailContact = formData.get('emailContact') || document.getElementById('emailContact')?.value || '';
  const gender = formData.get('gender') || '';
  const civilStatus = formData.get('civilStatus') || '';
  const employmentStatus = formData.get('employment_status') || formData.get('employmentStatus') || '';
  const occupation = formData.get('occupation') || '';
  const business = formData.get('business') || '';
  const philStatus = formData.get('philStatusInput') || formData.get('philStatus') || '';
  const philNum = formData.get('philNumber') || formData.get('philNum') || '';
  const prinMem = formData.get('prinMember') || formData.get('prinMem') || '';
  const nhtsStatus = formData.get('nhtsInput') || formData.get('nhtsStatus') || '';
  const nhtsNum = formData.get('nhtsNumber') || formData.get('nhtsNum') || '';

  // 1. Personal Verification
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
  if (gender.trim() === '') {
    e.preventDefault();
    showToast('Please select your gender', 'error');
    return;
  }
  if (birthDate.trim() === '') {
    e.preventDefault();
    showToast('Please provide your date of birth', 'error');
    return;
  }
  if (contactnum.trim() === '') {
    e.preventDefault();
    showToast('Please provide your contact number', 'error');
    return;
  }
  if (religion.trim() === '') {
    e.preventDefault();
    showToast('Please select/provide your religion', 'error');
    return;
  }

  // 2. Family Info Verification (Extracted via FormData to ensure POST alignment)
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

  // 3. Civil Status & Spouse Verification
  if (civilStatus.trim() === '') {
    e.preventDefault();
    showToast('Please select your civil status', 'error');
    return;
  }

  const sfname = formData.get('sfname') || document.getElementById('sfname')?.value || '';
  const slname = formData.get('slname') || document.getElementById('slname')?.value || '';

  if (civilStatus === 'married' || civilStatus === 'widow') {
    if (sfname.trim() === '' || slname.trim() === '') {
      e.preventDefault();
      showToast("Please provide your spouse's first and last name", 'error');
      return;
    }
  }

  // 4. Employment Checks
  if (employmentStatus.trim() === '') {
    e.preventDefault();
    showToast('Please select an employment status', 'error');
    return;
  } else if (employmentStatus === 'employed' && occupation.trim() === '') {
    e.preventDefault();
    showToast('Please provide your occupation', 'error');
    return;
  } else if (employmentStatus === 'self-employed' && business.trim() === '') {
    e.preventDefault();
    showToast('Please provide your business name', 'error');
    return;
  }

  // 5. PhilHealth Checks
  if (philStatus.trim() === '') {
    e.preventDefault();
    showToast('Please select PhilHealth status', 'error');
    return;
  } else if (philStatus === 'member' && philNum.trim() === '') {
    e.preventDefault();
    showToast('Please provide your PhilHealth Number', 'error');
    return;
  } else if (philStatus === 'dependent' && (philNum.trim() === '' || prinMem.trim() === '')) {
    e.preventDefault();
    showToast('Please complete the PhilHealth dependent fields', 'error');
    return;
  }

  // 6. NHTS Checks
  if (nhtsStatus.trim() === '') {
    e.preventDefault();
    showToast('Please select NHTS status', 'error');
    return;
  } else if (nhtsStatus === 'yes' && nhtsNum.trim() === '') {
    e.preventDefault();
    showToast('Please complete the NHTS Information', 'error');
    return;
  }

});
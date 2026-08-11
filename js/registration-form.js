(function(){
  var form = document.querySelector('form');
  if (!form) return;
  form.addEventListener('submit', function(e){
    e.preventDefault();
    // personal information
    var lname = document.getElementById('lname').value || '';
    var fname = document.getElementById('fname').value || '';
    var mname = document.getElementById('mname').value || '';
    var sname = document.getElementById('sname').value || '';
    var address = document.getElementById('address').value || '';
    var age = document.getElementById('age').value || '';
    var religion = document.getElementById('religion').value || '';
    var birthDate = document.getElementById('birthDate').value || '';
    var contactnum = document.getElementById('contactnum').value || '';
    var emailContact = document.getElementById('emailContact').value || '';

    // family info
    var mlname = document.getElementById('mlname').value || '';
    var mfname = document.getElementById('mfname').value || '';
    var mmname = document.getElementById('mmname').value || '';
    if(mlname.trim() === '' || mfname.trim() === '' || mmname.trim() === ''){
      e.preventDefault();
      showToast('Please provide your mothers full name','error');
      return false;
    } else {
      const mFullName = mfname + " " + mmname + " " + mlname;
    }

    var flname = document.getElementById('flname').value || '';
    var ffname = document.getElementById('ffname').value || '';
    var fmname = document.getElementById('fmname').value || '';
    if(flname.trim() === '' || ffname.trim() === '' || fmname.trim() === ''){
      e.preventDefault();
      showToast('Please provide your fathers full name','error');
      return false;
    } else {
      const fFullName = ffname + " " + fmname + " " + flname;
    }

    var slname = document.getElementById('slname').value || '';
    var sfname = document.getElementById('sfname').value || '';
    var smname = document.getElementById('smname').value || '';
    if(civilStatus.value === 'married' || civilStatus.value === 'widow'){
      if(slname.trim() === '' || sfname.trim() === '' || smname.trim() === ''){
        e.preventDefault();
        showToast('Please provide your spouses full name','error');
        return false;
      } else {
        const sFullName = sfname + " " + smname + " " + slname;
      }
    } else if(civilStatus.trim() === ''){
      e.preventDefault();
      showToast('Please select your civil status','error');
      return false;
    }

    // selectors
    const formData = new FormData(form);

    // gender
    const gender = 
        formData.get('gender') || '';

    // civil Status
    const civilStatus = 
        formData.get('civilStatus') || '';

    // employment Status
    const employmentStatus =
        formData.get('employment_status') || '';

    const occupation =
        formData.get('occupation') || '';

    const business =
        formData.get('business') || '';
    
    // phil Status
    const philStatus = 
        formData.get('philStatusInput') || '';
    const philNum = 
        formData.get('philNumber') || '';
    const prinMem = 
        formData.get('prinMember') || '';

    // nhts status
    const nhtsStatus = 
        formData.get('nhtsInput') || '';
    const nhtsNum = 
        formData.get('nhtsNumber') || '';

    // verification
    if(lname.trim() === '' || fname.trim() === '' || mname.trim() === ''){
      e.preventDefault();
      showToast('Please provide your full name','error');
      return false;
    }
    if(address.trim() === ''){
      e.preventDefault();
      showToast('Please provide your full address','error');
      return false;
    }
    if(age.trim() === ''){
      e.preventDefault();
      showToast('Please provide your age','error');
      return false;
    }
    if(religion.trim() === ''){
      e.preventDefault();
      showToast('Please provide your religion','error');
      return false;
    }
    if(birthDate.trim() === ''){
      e.preventDefault();
      showToast('Please provide your date of birth','error');
      return false;
    }
    if(contactnum.trim() === ''){
      e.preventDefault();
      showToast('Please provide your contact number','error');
      return false;
    }
    if(emailContact.trim() === ''){
      e.preventDefault();
      showToast('Please provide your email','error');
      return false;
    }
    if(gender.trim() === ''){
      e.preventDefault();
      showToast('Please provide your gender','error');
      return false;
    }
    if(employmentStatus.value === 'employed' && occupation.value.trim() === ''){
      e.preventDefault();
      showToast('Please provide your occupation','error');
      return false;
    } else if(employmentStatus.value === 'self-employed' && business.value.trim() === ''){
      e.preventDefault();
      showToast('Please provide your business','error');
      return false;
    }
    if(philStatus.value === 'member' && philNum.value.trim() === ''){
      e.preventDefault();
      showToast('Please provide your philHealth Number','error');
      return false;
    } else if (philStatus.value === 'dependent' && philNum.value.trim() === '' && prinMember.value.trim() === ''){
      e.preventDefault();
      showToast('Please complete the philHealth fields','error');
      return false;
    }
    if(nhtsStatus.value === 'yes' && nhtsNum.value.trim() === ''){
      e.preventDefault();
      showToast('Please complete the NHTS Information','error');
      return false;
    }

    return true;
  });
})();

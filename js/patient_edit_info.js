document.addEventListener("DOMContentLoaded", () => {
  const btnEdit = document.getElementById('btnEditProfile');
  const btnCancel = document.getElementById('btnCancelEdit');
  const btnSave = document.getElementById('btnSaveProfile');
  const infoForm = document.getElementById('patientInfoForm');

  let isEditMode = false;
  let cachedBarangays = [];

  // Pre-fetch barangays for the inline search feature
  async function fetchBarangaysForEdit() {
    if (cachedBarangays.length > 0) return cachedBarangays;
    try {
      const response = await fetch('../api/get_barangay.php');
      const data = await response.json();
      if (data.status === 'success') {
        cachedBarangays = data.barangays;
      }
    } catch (error) {
      console.error('Error fetching barangays for edit mode:', error);
    }
    return cachedBarangays;
  }

  fetchBarangaysForEdit();

  if (!btnEdit || !infoForm) return;

  btnEdit.addEventListener('click', enableEditMode);
  btnCancel.addEventListener('click', cancelEditMode);
  infoForm.addEventListener('submit', handleFormSubmit);

  async function enableEditMode() {
    if (isEditMode) return;

    try {
      const patientIdInput = document.getElementById('patientId');
      const patientId = patientIdInput ? patientIdInput.value : '';

      // Fetch fresh profile details from API with ID[cite: 7]
      const response = await fetch(`../api/get_patients.php?action=details&id=${patientId}`);
      if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);

      const result = await response.json();
      if (result.status !== 'success') {
        if (typeof showToast === 'function') showToast('Failed to load profile details for editing.', 'error');
        return;
      }

      const p = result.patient || result.account;
      isEditMode = true;

      // Toggle action buttons
      btnEdit.style.display = 'none';
      btnCancel.style.display = 'inline-block';
      btnSave.style.display = 'inline-block';

      // Convert spans into input/select fields pre-populated with fetched data[cite: 7]
      replaceSpanWithInput('infoFullName', 'nameFields', p);
      replaceSpanWithInput('infoAgeGender', 'select_gender', 'editGender', p.gender);
      replaceSpanWithInput('infoBirthDate', 'date', 'editBirthDate', p.date_of_birth);
      replaceSpanWithInput('infoCivilStatus', 'select_civil', 'editCivilStatus', p.civil_status);
      replaceSpanWithInput('infoReligion', 'text', 'editReligion', p.religion);
      replaceSpanWithInput('infoContact', 'text', 'editContact', p.contact_number);
      replaceSpanWithInput('infoEmail', 'email', 'editEmail', p.email);
      replaceSpanWithInput('infoAddress', 'editAddress', 'editAddress', p.address);
      replaceSpanWithInput('infoMotherName', 'text', 'editMotherName', p.mothers_name);
      replaceSpanWithInput('infoFatherName', 'text', 'editFatherName', p.fathers_name);
      replaceSpanWithInput('infoSpouseName', 'text', 'editSpouseName', p.spouses_name);
      replaceSpanWithInput('infoEmploymentStatus', 'select_emp', 'editEmploymentStatus', p.employment_status);
      replaceSpanWithInput('infoOccupation', 'text', 'editOccupation', p.occupation);
      replaceSpanWithInput('infoBusiness', 'text', 'editBusiness', p.business_name);
      replaceSpanWithInput('infoPhilStatus', 'select_phil', 'editPhilStatus', p.phil_status);
      replaceSpanWithInput('infoPhilNumber', 'text', 'editPhilNumber', p.phil_number);
      replaceSpanWithInput('infoPrinMember', 'text', 'editPrinMember', p.principal_member);
      replaceSpanWithInput('infoYakapMember', 'select_yakap', 'editYakapMember', p.yakap_member);

      // Attach dynamic listeners and apply initial conditional visibility[cite: 7]
      setupConditionalListeners();

    } catch (error) {
      console.error('Error enabling edit mode:', error);
      if (typeof showToast === 'function') showToast('An error occurred while preparing edit mode.', 'error');
    }
  }

  function cancelEditMode() {
    if (!isEditMode) return;
    isEditMode = false;

    btnEdit.style.display = 'inline-block';
    btnCancel.style.display = 'none';
    btnSave.style.display = 'none';

    if (typeof loadPatientInformation === 'function') {
      loadPatientInformation();
    } else {
      location.reload();
    }
  }

  function replaceSpanWithInput(spanId, type, nameOrConfig, val = '') {
    const span = document.getElementById(spanId);
    if (!span) return;

    val = val !== undefined && val !== null ? val : '';

    if (type === 'nameFields') {
      const p = nameOrConfig;
      span.innerHTML = `
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(110px, 1fr)); gap: 6px; margin-top: 4px;">
          <input type="text" name="editFirstName" value="${p.firstname || ''}" class="form-control" placeholder="First Name" required style="padding: 6px; border: 1px solid #ccc; border-radius: 4px;">
          <input type="text" name="editMiddleName" value="${p.middlename || ''}" class="form-control" placeholder="Middle Name" style="padding: 6px; border: 1px solid #ccc; border-radius: 4px;">
          <input type="text" name="editLastName" value="${p.lastname || ''}" class="form-control" placeholder="Last Name" required style="padding: 6px; border: 1px solid #ccc; border-radius: 4px;">
          <select name="editSuffix" class="form-control" style="padding: 6px; border: 1px solid #ccc; border-radius: 4px;">
            <option value="None" ${!p.suffix ? 'selected' : ''}>Suffix: None</option>
            <option value="Jr." ${p.suffix === 'Jr.' ? 'selected' : ''}>Jr.</option>
            <option value="Sr." ${p.suffix === 'Sr.' ? 'selected' : ''}>Sr.</option>
            <option value="I" ${p.suffix === 'I' ? 'selected' : ''}>I</option>
            <option value="II" ${p.suffix === 'II' ? 'selected' : ''}>II</option>
            <option value="III" ${p.suffix === 'III' ? 'selected' : ''}>III</option>
            <option value="IV" ${p.suffix === 'IV' ? 'selected' : ''}>IV</option>
          </select>
        </div>
      `;
      return;
    }

    let inputHtml = '';
    const ctrlStyle = "width: 100%; padding: 6px; border: 1px solid #ccc; border-radius: 4px;";

    if (type === 'select_gender') {
      inputHtml = `
        <select name="${nameOrConfig}" class="form-control" style="${ctrlStyle}">
          <option value="Male" ${val === 'Male' ? 'selected' : ''}>Male</option>
          <option value="Female" ${val === 'Female' ? 'selected' : ''}>Female</option>
          <option value="Other" ${val === 'Other' ? 'selected' : ''}>Other</option>
        </select>`;
    } else if (type === 'editAddress') {
      // Render searchable address input structure with its own isolated dropdown container
      span.innerHTML = `
        <div style="position: relative;" id="editAddressWrapper">
          <input type="text" name="editAddress" id="editAddressInput" value="${val}" class="form-control" placeholder="Search Barangay / Address..." autocomplete="off" required style="${ctrlStyle}">
          <div id="editBarangayDropdown" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: #fff; border: 1px solid #cbd5e1; border-top: none; border-radius: 0 0 6px 6px; max-height: 180px; overflow-y: auto; z-index: 1050; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);"></div>
        </div>
      `;
      
      // Initialize self-contained search autocomplete behavior for this input
      setTimeout(() => {
        const inputEl = document.getElementById('editAddressInput');
        const dropdownEl = document.getElementById('editBarangayDropdown');
        const wrapperEl = document.getElementById('editAddressWrapper');

        if (!inputEl || !dropdownEl) return;

        inputEl.addEventListener('input', async function() {
          const query = this.value.toLowerCase().trim();
          dropdownEl.innerHTML = '';

          if (!query) {
            dropdownEl.style.display = 'none';
            return;
          }

          const list = await fetchBarangaysForEdit();
          const filtered = list.filter(b => b.toLowerCase().includes(query));

          if (filtered.length > 0) {
            dropdownEl.style.display = 'block';
            filtered.forEach(item => {
              const div = document.createElement('div');
              div.textContent = item;
              div.style.cssText = `
                padding: 8px 12px;
                cursor: pointer;
                font-size: 0.9rem;
                border-bottom: 1px solid #f1f5f9;
              `;
              div.addEventListener('mouseenter', () => div.style.backgroundColor = '#f8fafc');
              div.addEventListener('mouseleave', () => div.style.backgroundColor = '#fff');
              div.addEventListener('click', () => {
                inputEl.value = item;
                dropdownEl.style.display = 'none';
              });
              dropdownEl.appendChild(div);
            });
          } else {
            dropdownEl.style.display = 'none';
          }
        });

        // Hide suggestions when clicking outside
        document.addEventListener('click', (e) => {
          if (wrapperEl && !wrapperEl.contains(e.target)) {
            dropdownEl.style.display = 'none';
          }
        });
      }, 0);

      return;
    } else if (type === 'select_civil') {
      inputHtml = `
        <select name="${nameOrConfig}" id="editCivilStatusSelect" class="form-control" style="${ctrlStyle}">
          <option value="Single" ${val === 'Single' ? 'selected' : ''}>Single</option>
          <option value="Married" ${val === 'Married' ? 'selected' : ''}>Married</option>
          <option value="Widowed" ${val === 'Widowed' ? 'selected' : ''}>Widowed</option>
        </select>`;
    } else if (type === 'select_emp') {
      inputHtml = `
        <select name="${nameOrConfig}" id="editEmpStatusSelect" class="form-control" style="${ctrlStyle}">
          <option value="Employed" ${val === 'Employed' ? 'selected' : ''}>Employed</option>
          <option value="Unemployed" ${val === 'Unemployed' ? 'selected' : ''}>Unemployed</option>
          <option value="Self-Employed" ${val === 'Self-Employed' ? 'selected' : ''}>Self-Employed</option>
        </select>`;
    } else if (type === 'select_phil') {
      inputHtml = `
        <select name="${nameOrConfig}" id="editPhilStatusSelect" class="form-control" style="${ctrlStyle}">
          <option value="Member" ${val === 'Member' ? 'selected' : ''}>Member</option>
          <option value="Dependent" ${val === 'Dependent' ? 'selected' : ''}>Dependent</option>
          <option value="Not Applicable" ${val === 'Not Applicable' ? 'selected' : ''}>Not Applicable</option>
        </select>`;
    } else if (type === 'select_yakap') {
      inputHtml = `
        <select name="${nameOrConfig}" class="form-control" style="${ctrlStyle}">
          <option value="Yes" ${val === 'Yes' ? 'selected' : ''}>Yes</option>
          <option value="No" ${val === 'No' ? 'selected' : ''}>No</option>
        </select>`;
    } else {
      inputHtml = `<input type="${type}" name="${nameOrConfig}" value="${val}" class="form-control" style="${ctrlStyle}">`;
    }

    span.innerHTML = inputHtml;
  }

  function setupConditionalListeners() {
    // 1. Civil Status -> Spouse Group[cite: 7]
    const civilSelect = document.getElementById('editCivilStatusSelect');
    const spouseGroup = document.getElementById('infoSpouseGroup');
    
    function updateSpouseVisibility() {
      if (!civilSelect || !spouseGroup) return;
      const val = civilSelect.value;
      spouseGroup.style.display = (val === 'Married' || val === 'Widowed') ? 'block' : 'none';
    }
    
    if (civilSelect) {
      civilSelect.addEventListener('change', updateSpouseVisibility);
      updateSpouseVisibility(); 
    }

    // 2. Employment Status -> Occupation & Business Groups[cite: 7]
    const empSelect = document.getElementById('editEmpStatusSelect');
    const occGroup = document.getElementById('infoOccupationGroup');
    const bizGroup = document.getElementById('infoBusinessGroup');

    function updateEmploymentVisibility() {
      if (!empSelect) return;
      const val = empSelect.value;

      if (occGroup) occGroup.style.display = (val === 'Employed') ? 'block' : 'none';
      if (bizGroup) bizGroup.style.display = (val === 'Self-Employed') ? 'block' : 'none';
    }

    if (empSelect) {
      empSelect.addEventListener('change', updateEmploymentVisibility);
      updateEmploymentVisibility(); 
    }

    // 3. PhilHealth Status -> PhilHealth Number & Principal Member Groups[cite: 7]
    const philSelect = document.getElementById('editPhilStatusSelect');
    const philNumGroup = document.getElementById('infoPhilNumberGroup');
    const prinMemGroup = document.getElementById('infoPrinMemberGroup');

    function updatePhilHealthVisibility() {
      if (!philSelect) return;
      const val = philSelect.value;

      if (val === 'Member') {
        if (philNumGroup) philNumGroup.style.display = 'block';
        if (prinMemGroup) prinMemGroup.style.display = 'none';
      } else if (val === 'Dependent') {
        if (philNumGroup) philNumGroup.style.display = 'block';
        if (prinMemGroup) prinMemGroup.style.display = 'block';
      } else {
        if (philNumGroup) philNumGroup.style.display = 'none';
        if (prinMemGroup) prinMemGroup.style.display = 'none';
      }
    }

    if (philSelect) {
      philSelect.addEventListener('change', updatePhilHealthVisibility);
      updatePhilHealthVisibility(); 
    }
  }

  async function handleFormSubmit(e) {
    e.preventDefault();
    const formData = new FormData(infoForm);

    try {
      await validateAndUpdate(formData);
    } catch (error) {
      console.error('Error submitting patient profile update:', error);
    }
  }

  async function validateAndUpdate(formData) {
    try {
      const response = await fetch('../php/patient_update_info.php', {
        method: 'POST',
        body: formData
      });

      const result = await response.json();

      if (result.status === 'success') {
        if (typeof showToast === 'function') {
          showToast(result.message || 'Profile updated successfully.', 'success');
        }
        // Wait 1.2 seconds so the toast is visible before canceling edit mode / reloading
        setTimeout(() => {
          cancelEditMode();
        }, 1200);

      } else {
        if (typeof showToast === 'function') {
          showToast('Update Failed: ' + (result.message || 'Unable to update profile.'), 'error');
        }
      }
    } catch (error) {
      console.error('Error submitting patient profile update:', error);
      if (typeof showToast === 'function') {
        showToast('An unexpected error occurred.', 'error');
      }
    }
  }
});
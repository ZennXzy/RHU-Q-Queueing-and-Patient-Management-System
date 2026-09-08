document.addEventListener("DOMContentLoaded", () => {
  const editModal = document.getElementById('edit-patient');
  const editForm = document.getElementById('editPatientForm');

  /* ==========================================
     1. OPEN EDIT MODAL & POPULATE FIELDS
  ========================================== */
  document.addEventListener('click', async (e) => {
    const editBtn = e.target.closest('.edit-btn');
    if (!editBtn) return;

    const patientId = editBtn.dataset.id;
    if (!editModal || !editForm) return;

    try {
      const response = await fetch(`../php/get_patients.php?action=details&id=${patientId}`);
      if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);

      const result = await response.json();

      if (result.status === 'success') {
        const p = result.patient;

        // Populate ID Badge & Hidden Patient ID Field
        const badgeElement = document.getElementById('modalPatientIdBadge');
        if (badgeElement) badgeElement.textContent = `ID: #${String(patientId).padStart(4, '0')}`;
        
        const hiddenIdInput = document.getElementById('editPatientId');
        if (hiddenIdInput) hiddenIdInput.value = patientId;

        // Safe value assignment helper
        const setVal = (id, val) => {
          const el = document.getElementById(id);
          if (el) el.value = val !== undefined && val !== null ? val : '';
        };

        // Populate Form Inputs & Selects
        setVal('editFirstName', p.firstname);
        setVal('editMiddleName', p.middlename);
        setVal('editLastName', p.lastname);
        setVal('editSuffix', p.suffix);
        setVal('editGender', p.gender);
        setVal('editBirthDate', p.date_of_birth);
        setVal('editCivilStatus', p.civil_status);
        setVal('editReligion', p.religion);
        setVal('editContact', p.contact_number);
        setVal('editAddress', p.address);
        setVal('editDateRegistered', p.date_registered);
        setVal('editMotherName', p.mothers_name);
        setVal('editFatherName', p.fathers_name);
        setVal('editSpouseName', p.spouses_name);

        setVal('editEmploymentStatus', p.employment_status);
        setVal('editOccupation', p.occupation);
        setVal('editBusiness', p.business_name);

        setVal('editPhilStatus', p.phil_status);
        setVal('editPhilNumber', p.phil_number);
        setVal('editPrinMember', p.principal_member);
        setVal('editYakapMember', p.yakap_member);

        // suffix reset null
        if(p.suffix === '' || p.suffix === null){
          setVal('editSuffix', 'None');
        }

        // civil status condition
        const civilStatus = String(p.civil_status || p.civilStatus || '');
        const spouseGroup = document.getElementById('modalSpouseGroup');
        if (spouseGroup) {
          spouseGroup.style.display = (civilStatus.includes('Married') || civilStatus.includes('Widow')) ? 'block' : 'none';
        }

        // employment status condition
        const empStatus = String(p.employment_status || '');
        const occGroup = document.getElementById('modalOccupationGroup');
        const bizGroup = document.getElementById('modalBusinessGroup');

        if (occGroup) occGroup.style.display = (empStatus.includes('Employed') && !empStatus.includes('Unemployed') && !empStatus.includes('self')) ? 'block' : 'none';
        if (bizGroup) bizGroup.style.display = empStatus.includes('Self') ? 'block' : 'none';

        // philHealth Status condition
        const philStatus = p.phil_status;
        const philNumGroup = document.getElementById('modalPhilNumberGroup');
        const prinMemGroup = document.getElementById('modalPrinMemberGroup');

        if(philStatus.includes('Member')){
          philNumGroup.style.display = 'block';
          prinMemGroup.style.display = 'none';
          console.log("Member");
        } else if (philStatus.includes('Dependent')){
          philNumGroup.style.display = 'block';
          prinMemGroup.style.display = 'block';
          console.log("Dependent");
        } else {
          philNumGroup.style.display = 'none';
          prinMemGroup.style.display = 'none';
          console.log("Not Applicable");
        }

        editModal.showModal();
      }
    } catch (error) {
      console.error('Error opening edit modal:', error);
    }
  });

  function reloadPatientTable() {
    fetch('../php/patient_table.php')
        .then(response => response.text())
        .then(html => {
            document.getElementById('patientTableBody').innerHTML = html;
        })
        .catch(error => {
            console.error('Error loading table:', error);
        });
  }

  if (editModal) {
    editModal.addEventListener("click", (e) => {
      const rect = editModal.getBoundingClientRect();
      const isInModal =
        rect.top <= e.clientY &&
        e.clientY <= rect.top + rect.height &&
        rect.left <= e.clientX &&
        e.clientX <= rect.left + rect.width;
      if (!isInModal) {
        editModal.close();
      }
    });
  }

  if (editForm) {
    editForm.addEventListener('submit', async (e) => {
      e.preventDefault();

      const formData = new FormData(editForm);

      try {
        const response = await fetch('../php/update_patient.php', {
          method: 'POST',
          body: formData
        });

        const result = await response.json();

        if (result.status === 'success') {
          editModal.close();
          reloadPatientTable();
          showToast('Update Successfully.','success');
        } else {
          showToast('Update Failed: ' + (result.message || 'Unable to update patient record.','error'));
        }
      } catch (error) {
        console.error('Error submitting patient update:', error);
        showToast('An error occurred while saving profile modifications.','error');
      }
    });
  }
 
})
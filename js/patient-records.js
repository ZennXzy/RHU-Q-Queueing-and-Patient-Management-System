document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('patientModal');
  const searchInput = document.getElementById('patientSearchInput');
  const tableBody = document.getElementById('patientTableBody');
  const sortableHeaders = document.querySelectorAll('th.sortable');

  // Track sort directions: 0 = Full Name, 1 = Age, 4 = Date Registered
  const sortDirections = { 0: 'asc', 1: 'asc', 4: 'asc' };

  /* ==========================================
     HELPER UTILITIES
  ========================================== */
  // Safely retrieve the first non-empty value from an array of possible keys
  const getVal = (obj, keys, defaultVal = '') => {
    for (const key of keys) {
      if (obj && obj[key] !== undefined && obj[key] !== null && String(obj[key]).trim() !== '') {
        return obj[key];
      }
    }
    return defaultVal;
  };

  // Safe string conversion for case matching
  const cleanStr = (val) => String(val || '').toLowerCase().trim();

  // Combine multiple name parts into a single string
  const buildFullName = (obj, fKeys, mKeys, lKeys, sKeys = []) => {
    const f = getVal(obj, fKeys);
    const m = getVal(obj, mKeys);
    const l = getVal(obj, lKeys);
    const s = getVal(obj, sKeys);
    return [f, m, l, s].filter(Boolean).join(' ').trim();
  };

  /* ==========================================
     1. REAL-TIME SEARCH FILTER
  ========================================== */
  function debounce(func, delay = 300) {
    let timeout;
    return (...args) => {
      clearTimeout(timeout);
      timeout = setTimeout(() => func(...args), delay);
    };
  }

  function filterTable(searchTerm) {
    if (!tableBody) return;
    const term = searchTerm.toLowerCase();
    const rows = tableBody.querySelectorAll('tr');
    let visibleCount = 0;

    rows.forEach(row => {
      if (row.querySelector('.empty-table-placeholder')) return;

      const textContent = row.textContent.toLowerCase();
      if (textContent.includes(term)) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    let emptyRow = tableBody.querySelector('.search-empty-row');
    if (visibleCount === 0) {
      if (!emptyRow) {
        emptyRow = document.createElement('tr');
        emptyRow.className = 'search-empty-row';
        emptyRow.innerHTML = `
          <td colspan="6" style="padding: 20px; text-align: center; color: #666;">
            No matching patient records found for "<strong>${searchTerm}</strong>".
          </td>
        `;
        tableBody.appendChild(emptyRow);
      }
    } else if (emptyRow) {
      emptyRow.remove();
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', debounce((e) => {
      filterTable(e.target.value.trim());
    }, 300));
  }

  /* ==========================================
     2. COLUMN SORTING LOGIC
  ========================================== */
  sortableHeaders.forEach(header => {
    header.style.cursor = 'pointer';

    header.addEventListener('click', () => {
      const colIndex = parseInt(header.dataset.col, 10);
      const dataType = header.dataset.type;
      const currentDir = sortDirections[colIndex];
      const isAscending = currentDir === 'asc';

      sortableHeaders.forEach(h => {
        const icon = h.querySelector('.sort-icon');
        if (icon) icon.textContent = '⇅';
      });

      const icon = header.querySelector('.sort-icon');
      if (icon) icon.textContent = isAscending ? '▲' : '▼';

      const rows = Array.from(tableBody.querySelectorAll('tr')).filter(
        row => !row.querySelector('.empty-table-placeholder') && !row.classList.contains('search-empty-row')
      );

      rows.sort((rowA, rowB) => {
        const cellA = rowA.children[colIndex] ? rowA.children[colIndex].textContent.trim() : '';
        const cellB = rowB.children[colIndex] ? rowB.children[colIndex].textContent.trim() : '';

        let valA, valB;

        if (dataType === 'number') {
          valA = parseFloat(cellA) || 0;
          valB = parseFloat(cellB) || 0;
        } else if (dataType === 'date') {
          valA = new Date(cellA).getTime() || 0;
          valB = new Date(cellB).getTime() || 0;
        } else {
          valA = cellA.toLowerCase();
          valB = cellB.toLowerCase();
        }

        if (valA < valB) return isAscending ? -1 : 1;
        if (valA > valB) return isAscending ? 1 : -1;
        return 0;
      });

      sortDirections[colIndex] = isAscending ? 'desc' : 'asc';
      rows.forEach(row => tableBody.appendChild(row));
    });
  });

  /* ==========================================
     3. VIEW DETAILS CLICK & DATA MAPPING
  ========================================== */
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.view-btn');
    if (!btn) return;

    const patientId = btn.dataset.id;
    if (!modal) return;

    try {
      const response = await fetch(`../php/get_patients.php?action=details&id=${patientId}`);
      if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);

      const result = await response.json();

      if (result.status === 'success') {
        const p = result.patient;
        console.log("Fetched Patient Data Object:", p);

        // ID Badge
        const badgeEl = document.getElementById('modalPatientIdBadge');
        if (badgeEl) badgeEl.textContent = `ID: #${String(patientId).padStart(4, '0')}`;

        // 1. Personal Details
        const fullName = buildFullName(
          p, 
          ['fname', 'firstname', 'first_name'], 
          ['mname', 'middlename', 'middle_name'], 
          ['lname', 'lastname', 'last_name'], 
          ['sname', 'suffix']
        );

        // Birth Date & Dynamic Age Calculation
        const rawBirthDate = getVal(p, ['date_of_birth'], '');
        let calculatedAge = getVal(p, ['age'], null);

        if ((!calculatedAge || calculatedAge === 0 || calculatedAge === '0') && rawBirthDate) {
          const birth = new Date(rawBirthDate);
          if (!isNaN(birth.getTime())) {
            const today = new Date();
            calculatedAge = today.getFullYear() - birth.getFullYear();
            const m = today.getMonth() - birth.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) {
              calculatedAge--;
            }
          }
        }

        const gender = getVal(p, ['gender'], '-');
        const civilStatus = getVal(p, ['civil_status'], '-');
        const religion = getVal(p, ['religion'], '-');

        const elFullName = document.getElementById('modalFullName');
        const elAgeGender = document.getElementById('modalAgeGender');
        const elBirthDate = document.getElementById('modalBirthDate');
        const elCivilStatus = document.getElementById('modalCivilStatus');
        const elReligion = document.getElementById('modalReligion');

        if (elFullName) elFullName.textContent = fullName || '-';
        if (elAgeGender) elAgeGender.textContent = `${(calculatedAge && calculatedAge !== 0) ? calculatedAge : '-'} / ${gender}`;
        if (elBirthDate) elBirthDate.textContent = rawBirthDate || '-';
        if (elCivilStatus) elCivilStatus.textContent = civilStatus;
        if (elReligion) elReligion.textContent = religion;

        // Conditional Spouse Field
        const spouseGroup = document.getElementById('modalSpouseGroup');
        const statusLower = cleanStr(civilStatus);
        if (spouseGroup) {
          if (statusLower.includes('married') || statusLower.includes('widow')) {
            spouseGroup.style.display = 'block';
            const spouseName = getVal(p, ['spouses_name'], 'N/A');
            const elSpouse = document.getElementById('modalSpouseName');
            if (elSpouse) elSpouse.textContent = spouseName;
          } else {
            spouseGroup.style.display = 'none';
          }
        }

        // 2. Contact & Address
        const contactNum = getVal(p, ['contact_number'], '-');
        const address = getVal(p, ['address'], '-');
        const dateRegistered = getVal(p, ['register', 'date_registered', 'created_at', 'date_created'], '-');

        const elContact = document.getElementById('modalContact');
        const elAddress = document.getElementById('modalAddress');
        const elDateReg = document.getElementById('modalDateRegistered');

        if (elContact) elContact.textContent = contactNum;
        if (elAddress) elAddress.textContent = address;
        if (elDateReg) elDateReg.textContent = dateRegistered;

        // 3. Parents Information
        const finalMother = getVal(p, ['mothers_name'], '');
        const finalFather = getVal(p, ['fathers_name'], '');
        const finalSpouse = getVal(p, ['spouses_name'], '');


        const elMother = document.getElementById('modalMotherName');
        const elFather = document.getElementById('modalFatherName');
        const elSpouse = document.getElementById('modalSpouseName');

        if (elMother) elMother.textContent = finalMother || 'N/A';
        if (elFather) elFather.textContent = finalFather || 'N/A';
        if (elSpouse) elSpouse.textContent = finalSpouse || 'N/A';

        // 4. Employment Information
        const rawEmpStatus = getVal(p, ['employment_status'], '');
        const empStatus = cleanStr(rawEmpStatus);

        const elEmpStatus = document.getElementById('modalEmploymentStatus');
        if (elEmpStatus) elEmpStatus.textContent = rawEmpStatus || '-';

        const occGroup = document.getElementById('modalOccupationGroup');
        const bizGroup = document.getElementById('modalBusinessGroup');

        const isEmployed = empStatus.includes('employed') && !empStatus.includes('unemployed') && !empStatus.includes('self');
        const isSelfEmployed = empStatus.includes('self');

        if (occGroup) {
          if (isEmployed) {
            occGroup.style.display = 'block';
            const elOcc = document.getElementById('modalOccupation');
            if (elOcc) elOcc.textContent = getVal(p, ['occupation'], 'N/A');
          } else {
            occGroup.style.display = 'none';
          }
        }

        if (bizGroup) {
          if (isSelfEmployed) {
            bizGroup.style.display = 'block';
            const elBiz = document.getElementById('modalBusiness');
            if (elBiz) elBiz.textContent = getVal(p, ['business_name'], 'N/A');
          } else {
            bizGroup.style.display = 'none';
          }
        }

        // 5. PhilHealth Information
        const rawPhilStatus = getVal(p, ['philStatusInput', 'phil_status', 'philhealth_status'], '');
        const philStatus = cleanStr(rawPhilStatus);

        const elPhilStatus = document.getElementById('modalPhilStatus');
        if (elPhilStatus) elPhilStatus.textContent = rawPhilStatus || '-';

        const philNumGroup = document.getElementById('modalPhilNumberGroup');
        const prinGroup = document.getElementById('modalPrinMemberGroup');

        if (philNumGroup && prinGroup) {
          if (philStatus.includes('member')) {
            philNumGroup.style.display = 'block';
            prinGroup.style.display = 'none';
          } else if (philStatus.includes('dependent')) {
            philNumGroup.style.display = 'block';
            prinGroup.style.display = 'block';
          } else {
            philNumGroup.style.display = 'none';
            prinGroup.style.display = 'none';
          }
        }

        const elPhilNum = document.getElementById('modalPhilNumber');
        const elPrinMember = document.getElementById('modalPrinMember');

        if (elPhilNum) elPhilNum.textContent = getVal(p, ['phil_number'], 'N/A');
        if (elPrinMember) elPrinMember.textContent = getVal(p, ['principal_member'], 'N/A');

        // 6. NHTS Information
        const rawNhts = getVal(p, ['nhts_number'], '-');
        const nhtsStatus = cleanStr(rawNhts);

        const elNhtsStatus = document.getElementById('modalNhtsStatus');
        if (elNhtsStatus) elNhtsStatus.textContent = rawNhts;

        const nhtsNumGroup = document.getElementById('modalNhtsNumberGroup');
        if (nhtsNumGroup) {
          const isNhtsMember = nhtsStatus === 'yes';
          nhtsNumGroup.style.display = isNhtsMember ? 'block' : 'none';
          const elNhtsNum = document.getElementById('modalNhtsNumber');
          if (elNhtsNum) elNhtsNum.textContent = getVal(p, ['nhts_number'], 'N/A');
        }

        // 7. Queue/Service History Table
        const historyBody = document.getElementById('modalHistoryBody');
        const visits = p.history || [];

        if (historyBody) {
          if (visits.length === 0) {
            historyBody.innerHTML = `
              <tr>
                <td colspan="4" style="text-align: center; padding: 16px; color: #94a3b8; font-style: italic;">
                  No service/queue history records available.
                </td>
              </tr>
            `;
          } else {
            historyBody.innerHTML = visits.map(item => {
              const status = item.status || 'Pending';
              const statusClass = status.toLowerCase().replace(/\s+/g, '-');
              return `
                <tr>
                  <td>${item.date || '-'}</td>
                  <td>${item.service || '-'}</td>
                  <td><strong>${item.ticket || '-'}</strong></td>
                  <td><span class="status-badge status-${statusClass}">${status}</span></td>
                </tr>
              `;
            }).join('');
          }
        }

        modal.showModal();
      }
    } catch (error) {
      console.error('Error fetching patient profile:', error);
    }
  });

  /* ==========================================
     4. BACKDROP CLICK TO CLOSE DIALOG
  ========================================== */
  if (modal) {
    modal.addEventListener('click', (e) => {
      const rect = modal.getBoundingClientRect();
      const isInModal = (
        rect.top <= e.clientY && e.clientY <= rect.top + rect.height &&
        rect.left <= e.clientX && e.clientX <= rect.left + rect.width
      );
      if (!isInModal) {
        modal.close();
      }
    });
  }
});
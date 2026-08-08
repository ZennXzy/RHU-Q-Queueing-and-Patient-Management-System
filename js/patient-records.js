// Sample Patient Records Data
const PATIENTS_DATA = [
  {
    id: 'P-101',
    fullName: 'Juan Dela Cruz',
    age: 32,
    sex: 'Male',
    contact: '09171234567',
    address: 'Zone 1, Polangui, Bicol',
    history: [
      { date: '2026-03-10', service: 'Medical Consultation', ticket: 'A0011', status: 'Completed' },
      { date: '2026-01-15', service: 'Laboratory Test', ticket: 'L0004', status: 'Completed' }
    ]
  },
  {
    id: 'P-102',
    fullName: 'Maria Clara Santos',
    age: 27,
    sex: 'Female',
    contact: '09189876543',
    address: 'Zone 3, Polangui, Bicol',
    history: [
      { date: '2026-04-01', service: 'Dental Checkup', ticket: 'B0005', status: 'Completed' },
      { date: '2026-04-12', service: 'Vaccination', ticket: 'C0012', status: 'Pending' }
    ]
  },
  {
    id: 'P-101',
    fullName: 'Juan Dela Cruz',
    age: 32,
    sex: 'Male',
    contact: '09171234567',
    address: 'Zone 1, Polangui, Bicol',
    history: [
      { date: '2026-03-10', service: 'Medical Consultation', ticket: 'A0011', status: 'Completed' },
      { date: '2026-01-15', service: 'Laboratory Test', ticket: 'L0004', status: 'Completed' }
    ]
  },
  {
    id: 'P-102',
    fullName: 'Maria Clara Santos',
    age: 27,
    sex: 'Female',
    contact: '09189876543',
    address: 'Zone 3, Polangui, Bicol',
    history: [
      { date: '2026-04-01', service: 'Dental Checkup', ticket: 'B0005', status: 'Completed' },
      { date: '2026-04-12', service: 'Vaccination', ticket: 'C0012', status: 'Pending' }
    ]
  },{
    id: 'P-101',
    fullName: 'Juan Dela Cruz',
    age: 32,
    sex: 'Male',
    contact: '09171234567',
    address: 'Zone 1, Polangui, Bicol',
    history: [
      { date: '2026-03-10', service: 'Medical Consultation', ticket: 'A0011', status: 'Completed' },
      { date: '2026-01-15', service: 'Laboratory Test', ticket: 'L0004', status: 'Completed' }
    ]
  },
  {
    id: 'P-102',
    fullName: 'Maria Clara Santos',
    age: 27,
    sex: 'Female',
    contact: '09189876543',
    address: 'Zone 3, Polangui, Bicol',
    history: [
      { date: '2026-04-01', service: 'Dental Checkup', ticket: 'B0005', status: 'Completed' },
      { date: '2026-04-12', service: 'Vaccination', ticket: 'C0012', status: 'Pending' }
    ]
  },{
    id: 'P-101',
    fullName: 'Juan Dela Cruz',
    age: 32,
    sex: 'Male',
    contact: '09171234567',
    address: 'Zone 1, Polangui, Bicol',
    history: [
      { date: '2026-03-10', service: 'Medical Consultation', ticket: 'A0011', status: 'Completed' },
      { date: '2026-01-15', service: 'Laboratory Test', ticket: 'L0004', status: 'Completed' }
    ]
  },
  {
    id: 'P-102',
    fullName: 'Maria Clara Santos',
    age: 27,
    sex: 'Female',
    contact: '09189876543',
    address: 'Zone 3, Polangui, Bicol',
    history: [
      { date: '2026-04-01', service: 'Dental Checkup', ticket: 'B0005', status: 'Completed' },
      { date: '2026-04-12', service: 'Vaccination', ticket: 'C0012', status: 'Pending' }
    ]
  },
  
];

document.addEventListener('DOMContentLoaded', () => {
  const tableBody = document.getElementById('patientTableBody');
  const searchInput = document.querySelector('.search-container input');
  const totalRecordsCountEl = document.getElementById('totalRecordsCount');
  
  const modal = document.getElementById('patientModal');
  const closeModalBtn = document.getElementById('closeModalBtn');

  // Render Table Rows & Empty Placeholder
  function renderTable(data) {
    tableBody.innerHTML = '';
    
    // Dynamically update Total Records span count
    if (totalRecordsCountEl) {
      totalRecordsCountEl.textContent = data.length;
    }

    // Placeholder when array is empty or search yields 0 matches
    if (data.length === 0) {
      tableBody.innerHTML = `
        <tr>
          <td colspan="5" class="empty-table-placeholder">
            <div class="empty-state">
              <p class="empty-title">No Patient Records Found</p>
              <p class="empty-subtitle">Try adjusting your search criteria or register a new patient.</p>
            </div>
          </td>
        </tr>
      `;
      return;
    }

    data.forEach(patient => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${patient.fullName}</td>
        <td>${patient.age}</td>
        <td>${patient.sex}</td>
        <td>${patient.contact}</td>
        <td>
          <button class="view-btn" data-id="${patient.id}">View Details</button>
        </td>
      `;
      tableBody.appendChild(tr);
    });

    attachViewEvents();
  }

  // Open & Populate Modal
  function attachViewEvents() {
    document.querySelectorAll('.view-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const patientId = e.target.dataset.id;
        const patient = PATIENTS_DATA.find(p => p.id === patientId);

        if (patient) {
          document.getElementById('modalName').textContent = patient.fullName;
          document.getElementById('modalAgeSex').textContent = `${patient.age} / ${patient.sex}`;
          document.getElementById('modalContact').textContent = patient.contact;
          document.getElementById('modalAddress').textContent = patient.address;

          const historyBody = document.getElementById('modalHistoryBody');

          // Service History Empty State Placeholder
          if (!patient.history || patient.history.length === 0) {
            historyBody.innerHTML = `
              <tr>
                <td colspan="4" style="text-align: center; padding: 20px; color: #777; font-style: italic;">
                  No service history records available for this patient.
                </td>
              </tr>
            `;
          } else {
            historyBody.innerHTML = patient.history.map(item => `
              <tr>
                <td>${item.date}</td>
                <td>${item.service}</td>
                <td>${item.ticket}</td>
                <td><span class="status-badge status-${item.status.toLowerCase()}">${item.status}</span></td>
              </tr>
            `).join('');
          }

          modal.showModal();
        }
      });
    });
  }

  // Live Search Filter
  searchInput.addEventListener('input', (e) => {
    const query = e.target.value.toLowerCase().trim();
    const filtered = PATIENTS_DATA.filter(p => p.fullName.toLowerCase().includes(query));
    renderTable(filtered);
  });

  // Close Modal Handler
  closeModalBtn.addEventListener('click', () => modal.close());

  // Initial Table Load
  renderTable(PATIENTS_DATA);
});
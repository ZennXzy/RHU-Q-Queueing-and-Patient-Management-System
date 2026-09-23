// Default active departments list
const DEFAULT_DEPTS = [
  { id: 'Medical Consultation', name: 'Medical Consultation' },
  { id: 'Prenatal Checkup', name: 'Prenatal Checkup' },
  { id: 'Immunization / Vaccination', name: 'Immunization / Vaccination' },
  { id: 'Dental Checkup', name: 'Dental Checkup' },
  { id: 'Laboratory Request', name: 'Laboratory Request' }
];

// Available departments dictionary
const ALL_DEPTS = {
  'Medical Consultation': { id: 'Medical Consultation', name: 'Medical Consultation' },
  'Prenatal Checkup': { id: 'Prenatal Checkup', name: 'Prenatal Checkup' },
  'Immunization / Vaccination': { id: 'Immunization / Vaccination', name: 'Immunization / Vaccination' },
  'Dental Checkup': { id: 'Dental Checkup', name: 'Dental Checkup' },
  'Laboratory Request': { id: 'Laboratory Request', name: 'Laboratory Request' }
};

document.addEventListener('DOMContentLoaded', () => {
  const cardsContainer = document.querySelector('.cards-container');
  const addModal = document.getElementById('addCardModal');
  const closeModalBtn = document.getElementById('closeModalBtn');
  const addCardForm = document.getElementById('addCardForm');

  // Get active departments from localStorage
  function getActiveDepartments() {
    const stored = localStorage.getItem('activeDepartments');
    return stored ? JSON.parse(stored) : DEFAULT_DEPTS;
  }

  function saveActiveDepartments(depts) {
    localStorage.setItem('activeDepartments', JSON.stringify(depts));
  }

  // Render Cards in Settings Grid
  function renderSettingsCards() {
    const depts = getActiveDepartments();
    cardsContainer.innerHTML = '';

    depts.forEach(dept => {
      const cardEl = document.createElement('div');
      cardEl.className = 'dept-card';
      cardEl.dataset.id = dept.id;
      cardEl.innerHTML = `
        <span class="close-btn" data-id="${dept.id}">✕</span>
        <h2 class="dept-title">${dept.name}</h2>
      `;
      cardsContainer.appendChild(cardEl);
    });

    // Append Add Card Button
    const addCardEl = document.createElement('div');
    addCardEl.className = 'add-card';
    addCardEl.innerHTML = `<img src="../svg/add-queue-card-icon.svg" alt="add-queue-card-icon">`;
    addCardEl.addEventListener('click', () => addModal.showModal());
    cardsContainer.appendChild(addCardEl);

    attachCloseEvents();
  }

  function attachCloseEvents() {
    const closeBtns = document.querySelectorAll('.close-btn');
    closeBtns.forEach(btn => {
      btn.addEventListener('click', (e) => {
        const idToRemove = e.target.dataset.id;
        let depts = getActiveDepartments();
        depts = depts.filter(d => d.id !== idToRemove);
        saveActiveDepartments(depts);
        renderSettingsCards();
      });
    });
  }

  closeModalBtn.addEventListener('click', () => addModal.close());

  addCardForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const selectedId = document.getElementById('deptSelect').value;
    let depts = getActiveDepartments();

    if (!depts.some(d => d.id === selectedId) && ALL_DEPTS[selectedId]) {
      depts.push(ALL_DEPTS[selectedId]);
      saveActiveDepartments(depts);
      renderSettingsCards();
    }
    addModal.close();
  });

  if (!localStorage.getItem('activeDepartments')) {
    saveActiveDepartments(DEFAULT_DEPTS);
  }
  renderSettingsCards();
});
// Default active departments list
const DEFAULT_DEPTS = [
  { id: 'medical', name: 'Medical' },
  { id: 'dental', name: 'Dental' },
  { id: 'vaccine', name: 'Vaccine' }
];

// Available departments dictionary
const ALL_DEPTS = {
  medical: { id: 'medical', name: 'Medical' },
  dental: { id: 'dental', name: 'Dental' },
  vaccine: { id: 'vaccine', name: 'Vaccine' },
  laboratory: { id: 'laboratory', name: 'Laboratory' }
};

document.addEventListener('DOMContentLoaded', () => {
  const cardsContainer = document.querySelector('.cards-container');
  const addModal = document.getElementById('addCardModal');
  const closeModalBtn = document.getElementById('closeModalBtn');
  const addCardForm = document.getElementById('addCardForm');

  // 1. Get active departments from localStorage or initialize
  function getActiveDepartments() {
    const stored = localStorage.getItem('activeDepartments');
    return stored ? JSON.parse(stored) : DEFAULT_DEPTS;
  }

  function saveActiveDepartments(depts) {
    localStorage.setItem('activeDepartments', JSON.stringify(depts));
  }

  // 2. Render Cards in Settings Grid
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

    // Append Add Card Button at the end
    const addCardEl = document.createElement('div');
    addCardEl.className = 'add-card';
    addCardEl.innerHTML = `<img src="../svg/add-queue-card-icon.svg" alt="add-queue-card-icon">`;
    addCardEl.addEventListener('click', () => addModal.showModal());
    cardsContainer.appendChild(addCardEl);

    attachCloseEvents();
  }

  // 3. Attach Close Event Handler
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

  // 4. Modal actions
  closeModalBtn.addEventListener('click', () => addModal.close());

  addCardForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const selectedId = document.getElementById('deptSelect').value;
    let depts = getActiveDepartments();

    // Prevent duplicate additions
    if (!depts.some(d => d.id === selectedId) && ALL_DEPTS[selectedId]) {
      depts.push(ALL_DEPTS[selectedId]);
      saveActiveDepartments(depts);
      renderSettingsCards();
    }
    addModal.close();
  });

  // Initial load
  if (!localStorage.getItem('activeDepartments')) {
    saveActiveDepartments(DEFAULT_DEPTS);
  }
  renderSettingsCards();
});
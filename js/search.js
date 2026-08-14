document.addEventListener('DOMContentLoaded', () => {
  const searchInput = document.getElementById('patientSearchInput');
  const tableBody = document.getElementById('patientTableBody');

  // Debounce function to wait 300ms after the user stops typing
  function debounce(func, delay = 300) {
    let timeout;
    return (...args) => {
      clearTimeout(timeout);
      timeout = setTimeout(() => func(...args), delay);
    };
  }

  // Client-side row filter (Fast for small to medium lists)
  function filterTable(searchTerm) {
    const term = searchTerm.toLowerCase();
    const rows = tableBody.querySelectorAll('tr');
    let visibleCount = 0;

    rows.forEach(row => {
      // Ignore empty state row
      if (row.querySelector('.empty-table-placeholder')) return;

      const textContent = row.textContent.toLowerCase();
      if (textContent.includes(term)) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    // Handle "No matching results" state
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

  // Listen for input changes with 300ms delay
  if (searchInput) {
    searchInput.addEventListener('input', debounce((e) => {
      filterTable(e.target.value.trim());
    }, 300));
  }
});
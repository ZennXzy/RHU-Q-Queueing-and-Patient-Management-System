document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('display-settings-form');
  const cells = document.querySelectorAll('.checklist-cell');

  // Toggle active class visually when clicking checklist cell
  cells.forEach(cell => {
    const checkbox = cell.querySelector('input[type="checkbox"]');
    
    cell.addEventListener('click', (e) => {
      // Prevent double trigger if clicking directly on checkbox element
      if (e.target !== checkbox) {
        checkbox.checked = !checkbox.checked;
      }
      cell.classList.toggle('active', checkbox.checked);
    });
  });

  // Save Settings Form Submission
  if (form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();

      const selectedServices = Array.from(
        document.querySelectorAll('input[name="services[]"]:checked')
      ).map(cb => cb.value);

      try {
        const response = await fetch('../php/update_queue_card_settings.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ services: selectedServices })
        });

        const result = await response.json();

        if (result.status === 'success') {
          alert('Queue card settings saved successfully!');
        } else {
          alert('Error: ' + result.message);
        }
      } catch (error) {
        console.error('Save error:', error);
        alert('An error occurred while saving queue card settings.');
      }
    });
  }
});
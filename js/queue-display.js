document.addEventListener('DOMContentLoaded', () => {
  const minimizeBtn = document.querySelector('.minimize-button');

  // Optional: Fullscreen toggle key handler (Press F11 or double click container)
  document.addEventListener('keydown', (e) => {
    if (e.key === 'f' || e.key === 'F') {
      if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch((err) => console.log(err));
      } else {
        document.exitFullscreen().catch((err) => console.log(err));
      }
    }
  });
});

// Current Script: Filters columns but never toggles #no-services-msg
document.addEventListener('DOMContentLoaded', () => {
    const stored = localStorage.getItem('activeDepartments');
    if (stored) {
        const activeDepts = JSON.parse(stored).map(d => d.id || d.name);
        document.querySelectorAll('.dept-column').forEach(col => {
            const service = col.getAttribute('data-service');
            if (!activeDepts.includes(service)) {
                col.style.display = 'none';
            }
        });
    }
});
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
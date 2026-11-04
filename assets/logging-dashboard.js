(() => {
  'use strict';
  const page = document.querySelector('.ld-head');
  if (!page) return;
  const refreshMs = 60000;
  let timer = window.setInterval(() => {
    if (document.visibilityState === 'visible' && !document.querySelector('dialog[open]')) {
      window.location.reload();
    }
  }, refreshMs);
  document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible' && !timer) {
      timer = window.setInterval(() => window.location.reload(), refreshMs);
    } else if (document.visibilityState !== 'visible' && timer) {
      window.clearInterval(timer);
      timer = null;
    }
  });
})();

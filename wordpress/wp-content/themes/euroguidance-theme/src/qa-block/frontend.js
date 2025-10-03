(function () {
  function initQA(root) {
    root.querySelectorAll('.qa-item').forEach((item) => {
      const btn   = item.querySelector('.qa-summary');
      const panel = item.querySelector('.qa-content');
      if (!btn || !panel) return;

      // початковий стан
      if (item.dataset.open === 'true') {
        item.classList.add('is-open');
        btn.setAttribute('aria-expanded', 'true');
        panel.hidden = false;
      }

      btn.addEventListener('click', () => {
        const isOpen = item.classList.toggle('is-open');
        btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        panel.hidden = !isOpen;
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initQA(document));
  } else {
    initQA(document);
  }
})();

(function () {
  function initQA(root) {
    root.querySelectorAll('.qa-item').forEach((item) => {
      const btn = item.querySelector('.qa-summary');
      if (!btn) return;
      if (item.dataset.open === 'true') {
        item.classList.add('is-open');
        item.setAttribute('aria-expanded', 'true');
      }
      btn.addEventListener('click', () => {
        const isOpen = item.classList.toggle('is-open');
        item.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initQA(document));
  } else {
    initQA(document);
  }
})();

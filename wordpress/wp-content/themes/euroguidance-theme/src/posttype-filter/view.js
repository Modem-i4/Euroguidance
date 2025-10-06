(function () {
  function enhance(list) {
    var param = list.getAttribute('data-param') || 'type';
    var url = new URL(window.location.href);
    var current = url.searchParams.get(param) || 'all';

    // зберігаємо інші параметри при формуванні href
    list.querySelectorAll('li.cat-item').forEach(function (li) {
      var a = li.querySelector('a');
      if (!a) return;
      var key = li.getAttribute('data-key') || (a.search.match(new RegExp(param + '=([^&]+)')) || [,''])[1] || 'all';

      // побудуємо посилання, зберігаючи існуючі параметри
      var u = new URL(window.location.href);
      if (key === 'all') u.searchParams.delete(param); else u.searchParams.set(param, key);
      a.setAttribute('href', u.pathname + u.search + u.hash);

      // активний стан
      li.classList.toggle('is-active', (key === current) || (!url.searchParams.has(param) && key === 'all'));
      if ((key === current) || (!url.searchParams.has(param) && key === 'all')) {
        a.setAttribute('aria-current', 'page');
      } else {
        a.removeAttribute('aria-current');
      }
    });
  }

  function init() {
    document.querySelectorAll('.ntd-posttype-filter').forEach(enhance);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();

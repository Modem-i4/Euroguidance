(function () {
  var root = document.querySelector('.search-page-search');
  if (!root) return;

  var params = new URLSearchParams(window.location.search);
  var cat = params.get('cat');

  var form = root.querySelector('form.wp-block-search');
  var allTab = root.querySelector('.search-input-wrapper .wp-block-column:first-child');
  var showAllBtn = root.querySelector('.show-all-btn') || (allTab ? allTab.querySelector('.show-all-btn') : null);
  var list = root.querySelector('.wp-block-categories');
  if (!allTab || !list) return;

  // скидаємо стани
  allTab.classList.remove('is-active');
  if (showAllBtn) showAllBtn.classList.remove('is-active');
  list.querySelectorAll('.cat-item.is-active').forEach(function(li){ li.classList.remove('is-active'); });
  list.querySelectorAll('.cat-item a[aria-current]').forEach(function(a){ a.removeAttribute('aria-current'); });

  // виставляємо активне
  if (cat && /^\d+$/.test(cat) && list.querySelector('.cat-item-' + cat)) {
    var li = list.querySelector('.cat-item-' + cat);
    li.classList.add('is-active');
    var a = li.querySelector('a'); if (a) a.setAttribute('aria-current', 'true');
  } else {
    allTab.classList.add('is-active');
    if (showAllBtn) showAllBtn.classList.add('is-active');
  }

  // клік по "Всі" → видалити cat, зберегти s та інші hidden
  if (showAllBtn) {
    showAllBtn.addEventListener('click', function (e) {
      e.preventDefault();
      var base = (form && form.getAttribute('action')) || location.pathname;
      var p = new URLSearchParams(window.location.search);

      p.delete('cat'); // скидаємо категорію

      // зберігаємо поточний введений пошук, якщо є
      if (form) {
        var sInput = form.querySelector('input[name="s"]');
        if (sInput && sInput.value) p.set('s', sInput.value);

        // переносимо всі hidden-поля (крім cat)
        form.querySelectorAll('input[type="hidden"]').forEach(function (inp) {
          if (inp.name && inp.name !== 'cat' && inp.value) p.set(inp.name, inp.value);
        });
      }

      var url = base + (p.toString() ? '?' + p.toString() : '');
      window.location.assign(url);
    });
  }
})();

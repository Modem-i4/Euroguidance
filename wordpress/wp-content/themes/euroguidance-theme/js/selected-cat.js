(function () {
  var root = document.querySelector('.search-page-search');
  if (!root) return;

  var params = new URLSearchParams(window.location.search);
  var cat = params.get('cat');
  var type = params.get('type');

  var form = root.querySelector('form.wp-block-search');
  var allTab = root.querySelector('.search-input-wrapper .wp-block-column:first-child');
  var showAllBtns = Array.prototype.slice.call(root.querySelectorAll('.show-all-btn'));
  var list = root.querySelector('.wp-block-categories');
  if (!allTab || !list) return;

  var pathname = location.pathname.replace(/\/+$/,'');
  var isSearchPage = pathname === '/search' || params.has('s');

  if (!isSearchPage) {
    allTab.classList.remove('is-active');
    showAllBtns.forEach(function(b){ b.classList.remove('is-active'); });
    list.querySelectorAll('.cat-item.is-active').forEach(function(li){ li.classList.remove('is-active'); });
    list.querySelectorAll('.cat-item a[aria-current]').forEach(function(a){ a.removeAttribute('aria-current'); });
  } else {
    allTab.classList.remove('is-active');
    showAllBtns.forEach(function(b){ b.classList.remove('is-active'); });
  }

  var hasCat = cat && /^\d+$/.test(cat) && list.querySelector('.cat-item-' + cat);
  var hasType = isSearchPage && type && type !== 'all';
  var anyActive = !!list.querySelector('.cat-item.is-active, .cat-item a[aria-current]');

  if (hasCat) {
    var li = list.querySelector('.cat-item-' + cat);
    if (li) {
      li.classList.add('is-active');
      var a = li.querySelector('a'); if (a) a.setAttribute('aria-current', 'true');
    }
  } else {
    if (isSearchPage) {
      if (!hasType && !anyActive) {
        allTab.classList.add('is-active');
        showAllBtns.forEach(function(b){ b.classList.add('is-active'); });
      }
    } else {
      if (!hasType) {
        allTab.classList.add('is-active');
        showAllBtns.forEach(function(b){ b.classList.add('is-active'); });
      }
    }
  }

  showAllBtns.forEach(function(btn){
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      var base = pathname || '/';
      var p = new URLSearchParams(window.location.search);
      if (p.has('cat')) p.delete('cat');
      if (isSearchPage && p.has('type')) p.delete('type');
      if (p.has('paged')) p.delete('paged');

      if (form) {
        var sInput = form.querySelector('input[name="s"]');
        if (sInput && sInput.value) p.set('s', sInput.value);
        form.querySelectorAll('input[type="hidden"]').forEach(function (inp) {
          if (!inp.name || !inp.value) return;
          if (inp.name === 'cat' || inp.name === 'type' || inp.name === 'paged') return;
          p.set(inp.name, inp.value);
        });
      }

      var url = base + (p.toString() ? '?' + p.toString() : '');
      window.location.assign(url);
    });
  });
})();

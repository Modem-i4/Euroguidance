document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('.news-serach-block');
  if (form) {
    form.action = window.location.href.split('?')[0]; // чистий URL без ?s=...
  }
  // для кожного контейнера з кнопкою "Завантажити ще"
  document.querySelectorAll('.js-load-more a').forEach((btn) => {
    const query = btn.closest('.wp-block-query');
    if (!query) return;

    const list = query.querySelector('.wp-block-post-template');
    const nextLink = query.querySelector('.wp-block-query-pagination-next a, .wp-block-query-pagination-next');
    if (!list || !nextLink) {
      btn.remove(); // немає пагінації — немає сенсу
      return;
    }

    const getNextHref = () => {
      const a = query.querySelector('.wp-block-query-pagination-next a, .wp-block-query-pagination-next');
      return a && a.getAttribute('href');
    };

    const setLoading = (state) => {
      btn.disabled = state;
      btn.dataset.label ??= btn.textContent;
      btn.textContent = state ? 'Завантаження…' : btn.dataset.label;
    };

    btn.addEventListener('click', async () => {
      const href = getNextHref();
      if (!href) { btn.remove(); return; }

      setLoading(true);
      try {
        const res = await fetch(href, { credentials: 'same-origin' });
        const html = await res.text();

        // Парсимо наступну сторінку і дістаємо з неї ті ж елементи списку
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const nextQuery = doc.querySelector('.wp-block-query'); // перший Query на тій сторінці
        const nextList  = nextQuery?.querySelector('.wp-block-post-template');
        if (!nextList || !nextList.children.length) { btn.remove(); return; }

        // Додати <li> у поточний список
        [...nextList.children].forEach(li => list.appendChild(li));

        // Оновити посилання "Далі"
        const newNext = nextQuery.querySelector('.wp-block-query-pagination-next a, .wp-block-query-pagination-next');
        const newHref = newNext && newNext.getAttribute('href');

        // Якщо наступного лінка немає — ховаємо кнопку
        if (!newHref) {
          const pagNext = query.querySelector('.wp-block-query-pagination-next');
          if (pagNext) pagNext.remove();
          btn.remove();
        } else {
          // Замінити href у поточному “Next”, щоб наступний клік ішов далі
          const curNext = query.querySelector('.wp-block-query-pagination-next a, .wp-block-query-pagination-next');
          if (curNext) curNext.setAttribute('href', newHref);
        }
      } catch (e) {
        console.error('Load more failed:', e);
      } finally {
        setLoading(false);
      }
    });
  });
});

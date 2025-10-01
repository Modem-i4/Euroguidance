// frontend.js
(function () {
  const ROOT = '.ntd-logo-carousel';

  const clamp = (n, a, b) => Math.max(a, Math.min(b, n));

  function slideStarts(viewport, track, slides) {
    const baseLeft = track.getBoundingClientRect().left;
    const scrollLeft = viewport.scrollLeft;
    return slides.map((el) => {
      const r = el.getBoundingClientRect();
      return r.left - baseLeft + scrollLeft;
    });
  }

  function makePages(viewport, track, slides) {
    const starts = slideStarts(viewport, track, slides);
    const maxScroll = Math.max(0, track.scrollWidth - viewport.clientWidth);
    const pagesFromStarts = starts
      .filter((s) => s <= maxScroll + 0.5)
      .map((x) => Math.max(0, Math.min(maxScroll, x)));

    const pages = [...pagesFromStarts, maxScroll]
      .map((v) => Math.round(v))
      .sort((a, b) => a - b)
      .filter((v, i, arr) => i === 0 || v !== arr[i - 1]);

    return pages.length ? pages : [0];
  }

  function nearestIndex(x, arr) {
    let idx = 0;
    let min = Math.abs(x - arr[0]);
    for (let i = 1; i < arr.length; i++) {
      const d = Math.abs(x - arr[i]);
      if (d < min) { min = d; idx = i; }
    }
    return idx;
  }

  function setup(root) {
    const viewport = root.querySelector('.ntd-logo-carousel__viewport');
    const track = root.querySelector('.ntd-logo-carousel__track');
    const slides = Array.from(root.querySelectorAll('.ntd-logo-carousel__slide'));
    const prevBtn = root.querySelector('.ntd-logo-carousel__nav.is-prev');
    const nextBtn = root.querySelector('.ntd-logo-carousel__nav.is-next');
    const dotsHost = root.querySelector('.ntd-logo-carousel__dots');
    if (!viewport || !track || slides.length === 0) return;

    track.style.justifyContent = 'flex-start';

    let pages = makePages(viewport, track, slides);
    let active = 0;
    let autoTimer = null;
    let scrollEndTimer = null;
    let isProgrammaticScroll = false;

    function renderDots() {
      if (!dotsHost) return;
      dotsHost.innerHTML = '';
      pages.forEach((_, i) => {
        const b = document.createElement('button');
        b.className = 'ntd-logo-carousel__dot' + (i === active ? ' is-active' : '');
        b.type = 'button';
        b.setAttribute('data-index', String(i));
        b.setAttribute('aria-label', `Перейти до сторінки ${i + 1}`);
        b.addEventListener('click', () => {
          resetAuto();
          scrollToPage(i);
        });
        dotsHost.appendChild(b);
      });
    }

    function updateDots() {
      if (!dotsHost) return;
      dotsHost.querySelectorAll('.ntd-logo-carousel__dot').forEach((d, i) =>
        d.classList.toggle('is-active', i === active)
      );
    }

    function snapToNearest() {
      const i = nearestIndex(Math.round(viewport.scrollLeft), pages);
      if (Math.abs(viewport.scrollLeft - pages[i]) > 1) {
        isProgrammaticScroll = true;
        viewport.scrollTo({ left: pages[i], behavior: 'auto' });
      }
      active = i;
      updateDots();
    }

    function scrollToPage(i, smooth = true) {
      active = clamp(i, 0, pages.length - 1);
      isProgrammaticScroll = true;
      viewport.scrollTo({ left: pages[active], behavior: smooth ? 'smooth' : 'auto' });
      updateDots();
    }

    prevBtn && prevBtn.addEventListener('click', () => {
      resetAuto();
      const idx = nearestIndex(Math.round(viewport.scrollLeft), pages);
      scrollToPage(idx - 1);
    });

    nextBtn && nextBtn.addEventListener('click', () => {
      resetAuto();
      const idx = nearestIndex(Math.round(viewport.scrollLeft), pages);
      scrollToPage(idx + 1);
    });

    let ticking = false;
    viewport.addEventListener('scroll', () => {
      if (!ticking) {
        requestAnimationFrame(() => {
          const idx = nearestIndex(Math.round(viewport.scrollLeft), pages);
          if (idx !== active) {
            active = idx;
            updateDots();
          }
          ticking = false;
        });
        ticking = true;
      }
      clearTimeout(scrollEndTimer);
      scrollEndTimer = setTimeout(() => {
        snapToNearest();
        isProgrammaticScroll = false;
      }, 120);
    });

    // Drag with Pointer Events; pause auto while dragging
    let dragging = false;
    let startX = 0;
    let startLeft = 0;
    viewport.addEventListener('pointerdown', (e) => {
      dragging = true;
      root.classList.add('is-grabbing');
      startX = e.clientX;
      startLeft = viewport.scrollLeft;
      viewport.setPointerCapture(e.pointerId);
      stopAuto();
    });
    viewport.addEventListener('pointermove', (e) => {
      if (!dragging) return;
      const dx = e.clientX - startX;
      const max = track.scrollWidth - viewport.clientWidth;
      viewport.scrollLeft = clamp(startLeft - dx, 0, max);
    });
    function endDrag(e) {
      if (!dragging) return;
      dragging = false;
      root.classList.remove('is-grabbing');
      try { viewport.releasePointerCapture(e.pointerId); } catch (_) {}
      snapToNearest();
      resetAuto();
    }
    viewport.addEventListener('pointerup', endDrag);
    viewport.addEventListener('pointercancel', endDrag);

    // Auto paging
    function startAuto() {
      stopAuto();
      autoTimer = setInterval(() => {
        const idx = nearestIndex(Math.round(viewport.scrollLeft), pages);
        scrollToPage((idx + 1) % pages.length);
      }, 2000);
    }
    function stopAuto() {
      if (autoTimer) clearInterval(autoTimer);
      autoTimer = null;
    }
    function resetAuto() {
      stopAuto();
      startAuto();
    }
    root.addEventListener('mouseenter', stopAuto);
    root.addEventListener('mouseleave', startAuto);

    // Recompute on resize/images
    const reflow = () => {
      const prevLeft = viewport.scrollLeft;
      pages = makePages(viewport, track, slides);
      active = nearestIndex(prevLeft, pages);
      renderDots();
      updateDots();
      if (!isProgrammaticScroll) snapToNearest();
    };

    let rt;
    window.addEventListener('resize', () => {
      clearTimeout(rt);
      rt = setTimeout(reflow, 120);
    });
    slides.forEach((sl) => {
      const img = sl.querySelector('img');
      if (img && !img.complete) {
        img.addEventListener('load', reflow, { once: true });
      }
    });

    renderDots();
    updateDots();
    startAuto();
  }

  function init() {
    document.querySelectorAll(ROOT).forEach(setup);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();

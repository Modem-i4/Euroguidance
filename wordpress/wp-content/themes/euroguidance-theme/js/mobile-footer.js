
// const SEL = '.wp-block-navigation-submenu__toggle';

// const setExpanded = (btn, expanded) => {
// btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
// btn.classList.toggle('is-fast', expanded);
// };

// // Миттєво при натисканні
// document.addEventListener('pointerdown', (e) => {
// const btn = e.target.closest(SEL);
// if (!btn) return;
// const willOpen = btn.getAttribute('aria-expanded') !== 'true';
// setExpanded(btn, willOpen);
// }, { capture: true });

// // Синхронізуємо клас зі зміною aria (коли WP оновить свій стан)
// const mo = new MutationObserver((ml) => {
// for (const m of ml) {
//     if (m.type !== 'attributes' || m.attributeName !== 'aria-expanded') continue;
//     const btn = m.target;
//     btn.classList.toggle('is-fast', btn.getAttribute('aria-expanded') === 'true');
// }
// });

// const init = () => {
// document.querySelectorAll(SEL)
//     .forEach(btn => mo.observe(btn, { attributes: true, attributeFilter: ['aria-expanded'] }));
// };

// document.addEventListener('DOMContentLoaded', init);
// document.addEventListener('wp-dom-ready', init);
document.addEventListener('click', function (e) {
    const menuContainer = document.querySelector('.wp-block-navigation__responsive-container');
    if (!menuContainer) return;

    // якщо меню відкрите і клік поза його вмістом
    if (menuContainer.classList.contains('is-menu-open') &&
        !menuContainer.querySelector('.wp-block-navigation__responsive-dialog').contains(e.target)
    ) {
        // імітуємо дію стандартного закриття
        menuContainer.querySelector('[data-wp-on-async--click="actions.closeMenuOnClick"]')?.click();
    }
});
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll("footer li.open-on-click").forEach(li => {
    li.addEventListener("click", e => {
      if (window.innerWidth < 961) return;
      if (e.target.closest("a")) return;
      const firstLink = li.querySelector("ul li a");
      if (firstLink) {
        window.location.href = firstLink.href;
      }
    });
  });
});

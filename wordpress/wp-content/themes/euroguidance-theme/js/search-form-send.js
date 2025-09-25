document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('.search-form');

  // реєстрація кнопки для посилання форми пошуку
  const btn  = document.querySelector('.btn-start-search');
  if (form && btn) {
    btn.addEventListener('click', (e) => {
      form.submit();
    });
  }
});
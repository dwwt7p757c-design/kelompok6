'use strict';

(() => {
  const toggle = document.querySelector('[data-menu-toggle]');
  const nav = document.querySelector('[data-primary-nav]');
  if (toggle && nav) {
    toggle.addEventListener('click', () => {
      const open = nav.classList.toggle('is-open');
      toggle.setAttribute('aria-expanded', String(open));
      document.body.classList.toggle('menu-open', open);
    });
    nav.addEventListener('click', (event) => {
      if (event.target instanceof HTMLAnchorElement) {
        nav.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('menu-open');
      }
    });
    window.addEventListener('resize', () => {
      if (window.innerWidth > 640) {
        nav.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('menu-open');
      }
    });
  }

  document.querySelectorAll('form[onsubmit]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      const inlineHandler = form.getAttribute('onsubmit') || '';
      if (inlineHandler.includes('confirm(') && !window.confirm('Delete this product?')) {
        event.preventDefault();
      }
    });
  });

  document.querySelectorAll('input[type="number"][name^="qty["]').forEach((input) => {
    input.addEventListener('change', () => {
      const min = Number(input.min || 0);
      const max = Number(input.max || 99);
      const value = Number(input.value || 0);
      input.value = String(Math.max(min, Math.min(max, value)));
    });
  });
})();

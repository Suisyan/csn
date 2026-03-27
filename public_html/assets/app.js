document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-auto-focus]').forEach((element) => {
    if (element instanceof HTMLElement) {
      element.focus();
    }
  });
});

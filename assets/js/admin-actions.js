document.addEventListener('submit', (event) => {
  const form = event.target.closest('form[data-confirm]');

  if (!form) {
    return;
  }

  const message = form.dataset.confirm || 'Continue with this action?';

  if (!window.confirm(message)) {
    event.preventDefault();
  }
});


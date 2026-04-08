document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-auto-focus]').forEach((element) => {
    if (element instanceof HTMLElement) {
      element.focus();
    }
  });

  const modal = document.querySelector('[data-order-modal]');
  const modalBody = document.querySelector('[data-order-modal-body]');

  if (!(modal instanceof HTMLElement) || !(modalBody instanceof HTMLElement)) {
    return;
  }

  const closeModal = () => {
    modal.style.display = 'none';
    modal.hidden = true;
    document.body.classList.remove('has-admin-modal');
    modalBody.innerHTML = '<div class="admin-order-modal__loading">受注明細を読み込み中です。</div>';
  };

  const openModal = async (url) => {
    modal.style.display = 'grid';
    modal.hidden = false;
    document.body.classList.add('has-admin-modal');
    modalBody.innerHTML = '<div class="admin-order-modal__loading">受注明細を読み込み中です。</div>';

    try {
      const response = await fetch(url, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      if (!response.ok) {
        throw new Error('failed');
      }

      modalBody.innerHTML = await response.text();
    } catch (error) {
      modalBody.innerHTML = '<div class="admin-order-modal__empty">受注明細の読み込みに失敗しました。</div>';
    }
  };

  document.querySelectorAll('[data-order-modal-link]').forEach((element) => {
    element.addEventListener('click', (event) => {
      const link = event.currentTarget;
      if (!(link instanceof HTMLAnchorElement)) {
        return;
      }

      const url = link.dataset.orderModalUrl;
      if (!url) {
        return;
      }

      event.preventDefault();
      openModal(url);
    });
  });

  document.querySelectorAll('[data-order-modal-close]').forEach((element) => {
    element.addEventListener('click', (event) => {
      event.preventDefault();
      closeModal();
    });
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && !modal.hidden) {
      closeModal();
    }
  });
});

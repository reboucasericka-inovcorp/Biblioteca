/**
 * Toast não-bloqueante para feedback ao utilizador.
 * @param {string} msg - Mensagem a exibir
 * @param {'success'|'error'} type - Tipo de toast (success=verde, error=vermelho)
 */
export function showToast(msg, type = 'success') {
  const toast = document.createElement('div');
  toast.className = `alert shadow-lg fixed bottom-4 right-4 z-50 max-w-sm transition-opacity ${
    type === 'error' ? 'alert-error' : 'alert-success'
  }`;
  const span = document.createElement('span');
  span.textContent = msg;
  toast.appendChild(span);
  document.body.appendChild(toast);
  setTimeout(() => {
    toast.style.opacity = '0';
    setTimeout(() => toast.remove(), 200);
  }, 3000);
}

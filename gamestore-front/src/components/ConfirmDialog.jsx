import { useEffect, useRef } from 'react';
import Alert from './Alert';

/**
 * Janela de confirmação para ações destrutivas, feita com o <dialog> nativo:
 * o navegador já cuida de prender o foco dentro dela e de fechar com Esc.
 */
export default function ConfirmDialog({
  open,
  title,
  confirmLabel = 'Confirmar',
  busyLabel = 'Aguarde…',
  busy = false,
  errors = [],
  onConfirm,
  onCancel,
  children,
}) {
  const ref = useRef(null);

  useEffect(() => {
    const dialog = ref.current;
    if (!dialog) return;
    if (open && !dialog.open) dialog.showModal();
    if (!open && dialog.open) dialog.close();
  }, [open]);

  function handleCancelEvent(event) {
    // Esc: em vez de fechar sozinho, passa pelo nosso estado (e é ignorado durante a ação).
    event.preventDefault();
    if (!busy) onCancel();
  }

  function handleBackdropClick(event) {
    if (event.target === ref.current && !busy) onCancel();
  }

  return (
    <dialog
      ref={ref}
      className="dialog"
      aria-labelledby="dialog-title"
      onCancel={handleCancelEvent}
      onClick={handleBackdropClick}
    >
      <div className="dialog__body">
        <h2 id="dialog-title">{title}</h2>
        <div className="dialog__text">{children}</div>
        {errors.length > 0 && <Alert messages={errors} />}
        <div className="dialog__actions">
          <button type="button" className="btn btn--ghost" onClick={onCancel} disabled={busy}>
            Cancelar
          </button>
          <button type="button" className="btn btn--danger" onClick={onConfirm} disabled={busy}>
            {busy ? busyLabel : confirmLabel}
          </button>
        </div>
      </div>
    </dialog>
  );
}

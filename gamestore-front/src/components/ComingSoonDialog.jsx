import { useEffect, useRef } from 'react';

/**
 * Aviso de "em desenvolvimento" para recursos que ainda não têm tela (compra, carrinho...).
 * Usa o <dialog> nativo, como o ConfirmDialog: Esc e clique fora fecham.
 */
export default function ComingSoonDialog({ open, onClose, gameTitle }) {
  const ref = useRef(null);

  useEffect(() => {
    const dialog = ref.current;
    if (!dialog) return;
    if (open && !dialog.open) dialog.showModal();
    if (!open && dialog.open) dialog.close();
  }, [open]);

  function handleCancelEvent(event) {
    event.preventDefault();
    onClose();
  }

  function handleBackdropClick(event) {
    if (event.target === ref.current) onClose();
  }

  return (
    <dialog
      ref={ref}
      className="dialog"
      aria-labelledby="coming-soon-title"
      onCancel={handleCancelEvent}
      onClick={handleBackdropClick}
    >
      <div className="dialog__body">
        <p className="dialog__icon" aria-hidden="true">
          🚧
        </p>
        <h2 id="coming-soon-title">Em desenvolvimento</h2>
        <div className="dialog__text">
          <p>
            {gameTitle ? (
              <>
                Ainda não dá para comprar <strong>{gameTitle}</strong>.
              </>
            ) : (
              'O carrinho ainda não está disponível.'
            )}{' '}
            A compra, o carrinho e a biblioteca de jogos estão sendo desenvolvidos e chegam em breve.
          </p>
        </div>
        <div className="dialog__actions">
          <button type="button" className="btn btn--primary" onClick={onClose} autoFocus>
            Entendi
          </button>
        </div>
      </div>
    </dialog>
  );
}

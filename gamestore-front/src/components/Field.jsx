/**
 * Campo de formulário: rótulo + controle + dica/erro, já ligados para leitores de tela.
 * Por padrão renderiza <input>; use as="select" ou as="textarea" para os outros.
 */
export default function Field({ id, label, error, hint, className = '', as: Control = 'input', children, ...props }) {
  const hintId = `${id}-hint`;
  const errorId = `${id}-error`;
  const showHint = Boolean(hint) && !error;

  let describedBy;
  if (error) describedBy = errorId;
  else if (showHint) describedBy = hintId;

  return (
    <div className={`field ${className}`.trim()}>
      <label htmlFor={id}>{label}</label>
      <Control
        id={id}
        name={id}
        className="input"
        aria-invalid={error ? 'true' : undefined}
        aria-describedby={describedBy}
        {...props}
      >
        {children}
      </Control>
      {showHint && (
        <p id={hintId} className="field__hint">
          {hint}
        </p>
      )}
      {error && (
        <p id={errorId} className="field__error">
          {error}
        </p>
      )}
    </div>
  );
}

/** Aviso em destaque. type: "error" | "success" | "info". */
export default function Alert({ type = 'error', messages = [], children }) {
  const role = type === 'error' ? 'alert' : 'status';

  return (
    <div className={`alert alert--${type}`} role={role}>
      {children}
      {messages.length === 1 && <p>{messages[0]}</p>}
      {messages.length > 1 && (
        <ul>
          {messages.map((message) => (
            <li key={message}>{message}</li>
          ))}
        </ul>
      )}
    </div>
  );
}

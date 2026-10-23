const LABELS = {
  ComingSoon: 'Em breve',
  Discontinued: 'Descontinuado',
};

/** Etiqueta para jogos que não estão à venda. Jogo disponível não mostra nada. */
export default function StatusBadge({ status, className = '' }) {
  const label = LABELS[status];
  if (!label) return null;

  return <span className={`status-badge ${className}`.trim()}>{label}</span>;
}

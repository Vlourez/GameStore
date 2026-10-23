import { formatRating } from '../utils/format';

/** Nota de 0 a 5 em estrelas. O texto escondido garante que leitor de tela leia "Nota 4 de 5". */
export default function Stars({ value }) {
  const filled = Math.min(5, Math.max(0, Math.round(value)));

  return (
    <span className="stars">
      <span aria-hidden="true">
        {'★'.repeat(filled)}
        {'☆'.repeat(5 - filled)}
      </span>
      <span className="visually-hidden">Nota {Number.isInteger(value) ? value : formatRating(value)} de 5</span>
    </span>
  );
}

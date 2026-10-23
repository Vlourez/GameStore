const brl = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });

export function formatPrice(value) {
  return brl.format(Number(value) || 0);
}

/** Percentual de desconto (0 quando não há desconto). */
export function discountPercent(price, currentPrice) {
  if (!price || currentPrice >= price) return 0;
  return Math.round((1 - currentPrice / price) * 100);
}

const DATE_ONLY = /^(\d{4})-(\d{2})-(\d{2})$/;

/**
 * A API devolve datas em UTC como "2024-03-15 18:00:00" (sem o Z).
 * Datas sem hora (releaseDate, birthDate) vêm como "2024-03-15".
 */
export function parseApiDate(value) {
  if (!value) return null;
  const match = DATE_ONLY.exec(value);
  if (match) return new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3]));
  const iso = value.replace(' ', 'T');
  const date = new Date(/(?:Z|[+-]\d{2}:?\d{2})$/i.test(iso) ? iso : `${iso}Z`);
  return Number.isNaN(date.getTime()) ? null : date;
}

export function formatDate(value) {
  const date = parseApiDate(value);
  return date ? date.toLocaleDateString('pt-BR') : '';
}

/** Nota média no padrão brasileiro: 4.5 vira "4,5". */
export function formatRating(value) {
  return Number(value).toFixed(1).replace('.', ',');
}

export function firstName(fullName) {
  return (fullName ?? '').trim().split(/\s+/)[0] ?? '';
}

/** Data de hoje em UTC no formato YYYY-MM-DD (é o fuso que o back usa para validar). */
export function todayUtc() {
  return new Date().toISOString().slice(0, 10);
}

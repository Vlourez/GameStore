// Lógica do catálogo: converte entre a URL (?busca=...), o estado da tela e os parâmetros da API.

export const PAGE_SIZE = 12;
export const DEFAULT_SORT = 'recentes';

// Sem orderBy a API ordena por data de criação, do mais antigo ao mais novo.
// Por isso "mais recentes" pede descending=true.
export const SORTS = {
  recentes: { label: 'Mais recentes', orderBy: undefined, descending: true },
  lancamento: { label: 'Lançamento mais novo', orderBy: 'releaseDate', descending: true },
  'menor-preco': { label: 'Menor preço', orderBy: 'price', descending: false },
  'maior-preco': { label: 'Maior preço', orderBy: 'price', descending: true },
  titulo: { label: 'Título (A a Z)', orderBy: 'title', descending: false },
  avaliacao: { label: 'Melhor avaliados', orderBy: 'rating', descending: true },
};

/** Lê a URL com tolerância: valores inválidos voltam ao padrão em vez de quebrar a tela. */
export function readCatalogParams(searchParams) {
  const page = Number.parseInt(searchParams.get('pagina') ?? '', 10);
  const sort = searchParams.get('ordem') ?? '';

  return {
    search: (searchParams.get('busca') ?? '').trim(),
    categoryId: searchParams.get('categoria') ?? '',
    sort: Object.hasOwn(SORTS, sort) ? sort : DEFAULT_SORT,
    page: Number.isInteger(page) && page > 0 ? page : 1,
  };
}

/** Monta a URL só com o que foge do padrão (URL limpa quando não há filtros). */
export function toSearchParams({ search, categoryId, sort, page }) {
  const params = new URLSearchParams();
  if (search) params.set('busca', search);
  if (categoryId) params.set('categoria', categoryId);
  if (sort && sort !== DEFAULT_SORT) params.set('ordem', sort);
  if (page > 1) params.set('pagina', String(page));
  return params;
}

/** Converte o estado da tela nos parâmetros de GET /api/games. */
export function toApiParams({ search, categoryId, sort, page }) {
  const { orderBy, descending } = SORTS[sort];
  return {
    search,
    categoryId,
    orderBy,
    descending: descending ? 'true' : undefined,
    page,
    pageSize: PAGE_SIZE,
  };
}

/** Texto do resumo de resultados ("12 jogos" ou "Mostrando 13 a 24 de 57 jogos"). */
export function describeResults({ page, count, total }) {
  if (total === 0) return '';
  const noun = total === 1 ? 'jogo' : 'jogos';
  if (page === 1 && total <= PAGE_SIZE) return `${total} ${noun}`;
  const start = (page - 1) * PAGE_SIZE + 1;
  return `Mostrando ${start} a ${start + count - 1} de ${total} ${noun}`;
}

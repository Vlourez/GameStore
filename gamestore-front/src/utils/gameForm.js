// Lógica compartilhada pelos formulários de cadastro e edição de jogo.

export const FIELD_ORDER = [
  'title',
  'developer',
  'publisher',
  'description',
  'price',
  'releaseDate',
  'categoryId',
  'coverImageUrl',
  'tags',
];

export const EMPTY_FORM = {
  title: '',
  developer: '',
  publisher: '',
  description: '',
  price: '',
  releaseDate: '',
  categoryId: '',
  coverImageUrl: '',
  tags: '',
};

/** "aventura, cozy, " vira ["aventura", "cozy"]. */
export function parseTags(text) {
  return text
    .split(',')
    .map((tag) => tag.trim())
    .filter(Boolean);
}

// Espelha o CreateGameRequestValidator do back (o de edição usa as mesmas regras).
export function validateGameForm(form) {
  const errors = {};
  const tags = parseTags(form.tags);

  if (!form.title.trim()) errors.title = 'Informe o título.';
  else if (form.title.trim().length > 200) errors.title = 'O título deve ter no máximo 200 caracteres.';

  if (!form.developer.trim()) errors.developer = 'Informe a desenvolvedora.';
  if (!form.publisher.trim()) errors.publisher = 'Informe a publicadora.';
  if (!form.description.trim()) errors.description = 'Escreva uma descrição.';

  const price = Number(form.price);
  if (form.price === '' || Number.isNaN(price) || price <= 0) errors.price = 'Informe um preço maior que zero.';

  if (!form.releaseDate) errors.releaseDate = 'Informe a data de lançamento.';
  if (!form.categoryId) errors.categoryId = 'Escolha uma categoria.';

  if (form.coverImageUrl.length > 500) errors.coverImageUrl = 'O endereço da capa deve ter no máximo 500 caracteres.';

  if (tags.length > 20) errors.tags = 'Use no máximo 20 tags.';
  else if (tags.some((tag) => tag.length > 50)) errors.tags = 'Cada tag deve ter no máximo 50 caracteres.';

  return errors;
}

/** Converte o que está na tela no corpo que a API espera (POST e PUT /api/games). */
export function formToPayload(form) {
  return {
    title: form.title.trim(),
    description: form.description.trim(),
    developer: form.developer.trim(),
    publisher: form.publisher.trim(),
    price: Number(form.price),
    releaseDate: form.releaseDate,
    categoryId: form.categoryId,
    coverImageUrl: form.coverImageUrl.trim(),
    tags: parseTags(form.tags),
  };
}

/** Converte o jogo devolvido por GET /api/games/{id} nos valores iniciais do formulário. */
export function gameToForm(game) {
  return {
    title: game.title ?? '',
    developer: game.developer ?? '',
    publisher: game.publisher ?? '',
    description: game.description ?? '',
    price: game.price === undefined || game.price === null ? '' : String(game.price),
    releaseDate: (game.releaseDate ?? '').slice(0, 10),
    categoryId: game.categoryId ?? '',
    coverImageUrl: game.coverImageUrl ?? '',
    tags: (game.tags ?? []).join(', '),
  };
}

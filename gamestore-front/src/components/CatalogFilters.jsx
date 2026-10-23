import { useEffect, useState } from 'react';
import { SORTS } from '../utils/catalog';
import Field from './Field';

const SEARCH_DELAY_MS = 400;

/**
 * Busca, categoria e ordenação do catálogo.
 * A busca espera a pessoa parar de digitar antes de avisar a Home, para não
 * consultar a API a cada letra.
 */
export default function CatalogFilters({
  search,
  categoryId,
  sort,
  categories,
  canClear,
  onSearchChange,
  onCategoryChange,
  onSortChange,
  onClear,
}) {
  const [text, setText] = useState(search);

  // Se a URL mudar por fora (botão voltar, "limpar filtros"), o campo acompanha.
  useEffect(() => {
    setText(search);
  }, [search]);

  useEffect(() => {
    const wanted = text.trim();
    if (wanted === search) return undefined;
    const timer = setTimeout(() => onSearchChange(wanted), SEARCH_DELAY_MS);
    return () => clearTimeout(timer);
  }, [text, search, onSearchChange]);

  return (
    <form className="filters" role="search" aria-label="Filtros do catálogo" onSubmit={(event) => event.preventDefault()}>
      <Field
        id="busca"
        className="field--search"
        label="Buscar jogos"
        type="search"
        placeholder="Título, desenvolvedora ou publicadora"
        autoComplete="off"
        value={text}
        onChange={(event) => setText(event.target.value)}
      />
      <Field
        id="categoria"
        as="select"
        label="Categoria"
        value={categoryId}
        onChange={(event) => onCategoryChange(event.target.value)}
      >
        <option value="">Todas</option>
        {categories.map((category) => (
          <option key={category.id} value={category.id}>
            {category.name}
          </option>
        ))}
      </Field>
      <Field id="ordem" as="select" label="Ordenar por" value={sort} onChange={(event) => onSortChange(event.target.value)}>
        {Object.entries(SORTS).map(([key, { label }]) => (
          <option key={key} value={key}>
            {label}
          </option>
        ))}
      </Field>
      {canClear && (
        <button type="button" className="btn btn--ghost filters__clear" onClick={onClear}>
          Limpar filtros
        </button>
      )}
    </form>
  );
}

export default function Pagination({ page, totalPages, onPageChange }) {
  if (totalPages <= 1) return null;

  return (
    <nav className="pagination" aria-label="Paginação do catálogo">
      <button type="button" className="btn btn--ghost" onClick={() => onPageChange(page - 1)} disabled={page <= 1}>
        Anterior
      </button>
      <p className="pagination__status">
        Página {page} de {totalPages}
      </p>
      <button
        type="button"
        className="btn btn--ghost"
        onClick={() => onPageChange(page + 1)}
        disabled={page >= totalPages}
      >
        Próxima
      </button>
    </nav>
  );
}

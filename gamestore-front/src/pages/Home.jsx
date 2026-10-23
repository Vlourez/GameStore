import { useCallback, useEffect, useMemo, useState } from 'react';
import { Link, useLocation, useSearchParams } from 'react-router-dom';
import Alert from '../components/Alert';
import CatalogFilters from '../components/CatalogFilters';
import ConfirmDialog from '../components/ConfirmDialog';
import GameCard from '../components/GameCard';
import Pagination from '../components/Pagination';
import { useAuth } from '../hooks/useAuth';
import { usePageTitle } from '../hooks/usePageTitle';
import { api } from '../services/api';
import { describeResults, readCatalogParams, toApiParams, toSearchParams } from '../utils/catalog';

export default function Home() {
  usePageTitle('Catálogo');

  const { isAdmin } = useAuth();
  const location = useLocation();
  const [searchParams, setSearchParams] = useSearchParams();

  // Tudo que o catálogo mostra vem da URL: busca, categoria, ordem e página.
  const params = useMemo(() => readCatalogParams(searchParams), [searchParams]);
  const apiParams = useMemo(() => toApiParams(params), [params]);
  const [attempt, setAttempt] = useState(0);
  const requestKey = useMemo(() => JSON.stringify([apiParams, attempt]), [apiParams, attempt]);

  // Guarda a última resposta junto com a "chave" do pedido que a gerou.
  // Assim a lista antiga continua na tela (esmaecida) enquanto a nova carrega.
  const [result, setResult] = useState(null);
  const [categories, setCategories] = useState([]);
  const [notice, setNotice] = useState(location.state?.notice ?? null);

  // Exclusão (só admin): guarda o jogo escolhido até a pessoa confirmar.
  const [gameToDelete, setGameToDelete] = useState(null);
  const [deleting, setDeleting] = useState(false);
  const [deleteErrors, setDeleteErrors] = useState([]);

  useEffect(() => {
    const controller = new AbortController();
    api
      .get('/api/games', { params: apiParams, signal: controller.signal })
      .then((data) => {
        if (!controller.signal.aborted) setResult({ key: requestKey, data, error: null });
      })
      .catch((error) => {
        if (error.name === 'AbortError' || controller.signal.aborted) return;
        setResult((current) => ({ key: requestKey, data: current?.data ?? null, error }));
      });
    return () => controller.abort();
  }, [apiParams, requestKey]);

  useEffect(() => {
    const controller = new AbortController();
    api
      .get('/api/categories', { signal: controller.signal })
      .then(setCategories)
      .catch(() => {
        // Sem categorias o filtro some, mas o catálogo continua funcionando.
      });
    return () => controller.abort();
  }, []);

  const data = result?.data ?? null;
  const fetching = result !== null && result.key !== requestKey;
  const error = result?.key === requestKey ? result.error : null;
  const games = data?.items ?? [];

  // Página fora do intervalo (URL antiga, ou o último jogo da última página foi excluído):
  // volta para a última página que existe.
  useEffect(() => {
    if (!data || data.page !== params.page) return;
    if (data.items.length === 0 && data.totalCount > 0 && params.page > 1) {
      setSearchParams(toSearchParams({ ...params, page: Math.max(1, data.totalPages) }), { replace: true });
    }
  }, [data, params, setSearchParams]);

  const updateUrl = useCallback(
    (changes, options) => {
      setNotice(null);
      setSearchParams(toSearchParams({ ...params, page: 1, ...changes }), options);
    },
    [params, setSearchParams],
  );

  const handleSearchChange = useCallback((search) => updateUrl({ search }, { replace: true }), [updateUrl]);

  function handlePageChange(page) {
    updateUrl({ page });
    window.scrollTo({ top: 0 });
  }

  function retry() {
    setAttempt((n) => n + 1);
  }

  function askToDelete(game) {
    setDeleteErrors([]);
    setGameToDelete(game);
  }

  function cancelDelete() {
    if (!deleting) setGameToDelete(null);
  }

  async function confirmDelete() {
    const game = gameToDelete;
    setDeleting(true);
    setDeleteErrors([]);
    try {
      await api.delete(`/api/games/${game.id}`);
      // Tira o card da tela na hora (sem esperar a API) e recarrega em seguida só para
      // sincronizar total e número de páginas.
      setResult((current) => {
        if (!current?.data) return current;
        const totalCount = Math.max(0, current.data.totalCount - 1);
        return {
          ...current,
          data: {
            ...current.data,
            items: current.data.items.filter((item) => item.id !== game.id),
            totalCount,
            totalPages: Math.ceil(totalCount / current.data.pageSize),
          },
        };
      });
      setNotice(`“${game.title}” foi excluído do catálogo.`);
      setGameToDelete(null);
      setAttempt((n) => n + 1);
    } catch (err) {
      setDeleteErrors(err.messages ?? [err.message]);
    } finally {
      setDeleting(false);
    }
  }

  function adminActions(game) {
    if (!isAdmin) return null;
    return (
      <>
        <Link to={`/jogos/${game.id}/editar`} className="btn btn--ghost btn--sm" aria-label={`Editar ${game.title}`}>
          Editar
        </Link>
        <button
          type="button"
          className="btn btn--danger-outline btn--sm"
          aria-label={`Excluir ${game.title}`}
          onClick={() => askToDelete(game)}
        >
          Excluir
        </button>
      </>
    );
  }

  const canClear = Boolean(params.search || params.categoryId);
  const redirecting = data && data.items.length === 0 && data.totalCount > 0 && params.page > 1;
  const initialLoading = data === null && !error;
  const showEmpty = data !== null && !error && games.length === 0 && !redirecting;

  return (
    <>
      <div className="page-head">
        <h1>Catálogo</h1>
        <p aria-live="polite">
          {data ? describeResults({ page: data.page, count: games.length, total: data.totalCount }) : ''}
        </p>
      </div>

      <CatalogFilters
        search={params.search}
        categoryId={params.categoryId}
        sort={params.sort}
        categories={categories}
        canClear={canClear}
        onSearchChange={handleSearchChange}
        onCategoryChange={(categoryId) => updateUrl({ categoryId })}
        onSortChange={(sort) => updateUrl({ sort })}
        onClear={() => updateUrl({ search: '', categoryId: '' })}
      />

      {notice && <Alert type="success" messages={[notice]} />}

      {(initialLoading || redirecting) && (
        <p className="state" role="status">
          Carregando jogos…
        </p>
      )}

      {error && (
        <div className="state">
          <Alert messages={error.messages} />
          <button type="button" className="btn btn--ghost" onClick={retry}>
            Tentar de novo
          </button>
        </div>
      )}

      {showEmpty && canClear && (
        <section className="state">
          <h2>Nenhum jogo encontrado</h2>
          <p>Tente outro termo de busca ou outra categoria.</p>
          <button type="button" className="btn btn--ghost" onClick={() => updateUrl({ search: '', categoryId: '' })}>
            Limpar filtros
          </button>
        </section>
      )}

      {showEmpty && !canClear && (
        <section className="state">
          <h2>O catálogo está vazio</h2>
          <p>Ainda não há jogos cadastrados.</p>
          {isAdmin && (
            <Link to="/novo-jogo" className="btn btn--primary">
              Cadastrar o primeiro jogo
            </Link>
          )}
        </section>
      )}

      {games.length > 0 && !error && !redirecting && (
        <>
          <ul className={`game-grid${fetching ? ' is-fetching' : ''}`} aria-busy={fetching}>
            {games.map((game) => (
              <GameCard
                key={game.id}
                game={game}
                linkState={{ catalogSearch: location.search }}
                actions={adminActions(game)}
              />
            ))}
          </ul>
          <Pagination page={data.page} totalPages={data.totalPages} onPageChange={handlePageChange} />
        </>
      )}

      <ConfirmDialog
        open={gameToDelete !== null}
        title={`Excluir “${gameToDelete?.title ?? ''}”?`}
        confirmLabel="Excluir jogo"
        busyLabel="Excluindo…"
        busy={deleting}
        errors={deleteErrors}
        onConfirm={confirmDelete}
        onCancel={cancelDelete}
      >
        <p>O jogo sai do catálogo e deixa de ficar disponível para compra. Esta ação não pode ser desfeita pelo site.</p>
      </ConfirmDialog>
    </>
  );
}

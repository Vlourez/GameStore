import { useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import Alert from '../components/Alert';
import GameForm from '../components/GameForm';
import { usePageTitle } from '../hooks/usePageTitle';
import { api } from '../services/api';
import { gameToForm } from '../utils/gameForm';

export default function EditarJogo() {
  usePageTitle('Editar jogo');

  const { id } = useParams();
  const navigate = useNavigate();
  const [game, setGame] = useState(null);
  const [error, setError] = useState(null);

  useEffect(() => {
    const controller = new AbortController();
    api
      .get(`/api/games/${id}`, { signal: controller.signal })
      .then(setGame)
      .catch((err) => {
        if (err.name !== 'AbortError') setError(err);
      });
    return () => controller.abort();
  }, [id]);

  async function handleSubmit(payload) {
    const updated = await api.put(`/api/games/${id}`, payload);
    navigate('/', { state: { notice: `“${updated.title}” foi atualizado.` } });
  }

  if (error) {
    return (
      <section className="state">
        <h1>{error.status === 404 ? 'Jogo não encontrado' : 'Não foi possível abrir o jogo'}</h1>
        {error.status === 404 ? (
          <p>Ele pode ter sido excluído ou o endereço está incorreto.</p>
        ) : (
          <Alert messages={error.messages} />
        )}
        <Link to="/" className="btn btn--ghost">
          Voltar ao catálogo
        </Link>
      </section>
    );
  }

  if (!game) {
    return (
      <p className="state" role="status">
        Carregando jogo…
      </p>
    );
  }

  // O back guarda um preço promocional fixo: se o novo preço ficar igual ou menor, a promoção cai.
  const priceHint =
    game.discountPrice !== null && game.discountPrice !== undefined
      ? 'Este jogo está em promoção. Se o novo preço ficar igual ou menor que o preço promocional, a promoção é removida.'
      : undefined;

  return (
    <section className="panel">
      <h1>Editar jogo</h1>
      <p className="panel__lead">Altere os dados e salve. As mudanças aparecem no catálogo na hora.</p>
      <GameForm
        key={game.id}
        initialForm={gameToForm(game)}
        priceHint={priceHint}
        submitLabel="Salvar alterações"
        submittingLabel="Salvando…"
        onSubmit={handleSubmit}
      />
    </section>
  );
}

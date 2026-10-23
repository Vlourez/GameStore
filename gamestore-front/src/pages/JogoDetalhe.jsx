import { useEffect, useState } from 'react';
import { Link, useLocation, useParams } from 'react-router-dom';
import Alert from '../components/Alert';
import ComingSoonDialog from '../components/ComingSoonDialog';
import GameCover from '../components/GameCover';
import Stars from '../components/Stars';
import StatusBadge from '../components/StatusBadge';
import { useAuth } from '../hooks/useAuth';
import { usePageTitle } from '../hooks/usePageTitle';
import { useReviews } from '../hooks/useReviews';
import { api } from '../services/api';
import { discountPercent, formatDate, formatPrice, formatRating } from '../utils/format';

const UNAVAILABLE_TEXT = {
  ComingSoon: 'Este jogo ainda não foi lançado.',
  Discontinued: 'Este jogo foi descontinuado e não está mais à venda.',
};

export default function JogoDetalhe() {
  const { id } = useParams();
  // key={id}: ao trocar de jogo, o estado da página começa do zero.
  return <GameDetail key={id} id={id} />;
}

function GameDetail({ id }) {
  const { isAdmin } = useAuth();
  const location = useLocation();
  const [game, setGame] = useState(null);
  const [error, setError] = useState(null);
  const [showComingSoon, setShowComingSoon] = useState(false);

  usePageTitle(game?.title ?? 'Jogo');

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

  // Volta para o catálogo com os mesmos filtros e página de onde a pessoa veio.
  const backTo = `/${location.state?.catalogSearch ?? ''}`;

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

  const percent = discountPercent(game.price, game.currentPrice);
  const unavailableText = UNAVAILABLE_TEXT[game.status];

  return (
    <>
      <p className="back-link">
        <Link to={backTo}>Voltar ao catálogo</Link>
      </p>

      <article className="detail">
        <div className="detail__cover">
          <GameCover title={game.title} url={game.coverImageUrl} lazy={false} />
          <StatusBadge status={game.status} className="status-badge--corner" />
        </div>

        <div className="detail__info">
          <h1>{game.title}</h1>

          <p className="detail__rating">
            {game.reviewCount > 0 ? (
              <>
                <Stars value={game.averageRating} /> <strong>{formatRating(game.averageRating)}</strong> (
                {game.reviewCount} {game.reviewCount === 1 ? 'avaliação' : 'avaliações'})
                {game.isRecommendedByMajority && ' Recomendado pela maioria dos jogadores.'}
              </>
            ) : (
              'Ainda sem avaliações'
            )}
          </p>

          <p className="detail__price">
            {percent > 0 && <span className="badge">-{percent}%</span>}
            {percent > 0 && <span className="price__old">{formatPrice(game.price)}</span>}
            <span>{formatPrice(game.currentPrice)}</span>
          </p>
          {unavailableText && <p className="detail__unavailable">{unavailableText}</p>}

          {!unavailableText && (
            <div className="detail__buy">
              <button type="button" className="btn btn--primary btn--lg" onClick={() => setShowComingSoon(true)}>
                Comprar
              </button>
              <button type="button" className="btn btn--ghost btn--lg" onClick={() => setShowComingSoon(true)}>
                Adicionar ao carrinho
              </button>
            </div>
          )}

          <dl className="detail__facts">
            <dt>Desenvolvedora</dt>
            <dd>{game.developer}</dd>
            <dt>Publicadora</dt>
            <dd>{game.publisher}</dd>
            <dt>Lançamento</dt>
            <dd>{formatDate(game.releaseDate)}</dd>
            <dt>Categoria</dt>
            <dd>{game.categoryName}</dd>
          </dl>

          {game.tags.length > 0 && (
            <ul className="tag-list" aria-label="Tags">
              {game.tags.map((tag) => (
                <li key={tag} className="chip">
                  {tag}
                </li>
              ))}
            </ul>
          )}

          {isAdmin && (
            <div className="detail__admin">
              <Link to={`/jogos/${game.id}/editar`} className="btn btn--ghost">
                Editar jogo
              </Link>
            </div>
          )}
        </div>

        <section className="detail__about" aria-labelledby="about-title">
          <h2 id="about-title">Sobre o jogo</h2>
          <p>{game.description}</p>
        </section>
      </article>

      <ReviewsSection gameId={game.id} />

      <ComingSoonDialog open={showComingSoon} onClose={() => setShowComingSoon(false)} gameTitle={game.title} />
    </>
  );
}

function ReviewsSection({ gameId }) {
  const { reviews, status, error, retry, loadMore, loadingMore, moreError } = useReviews(gameId);

  return (
    <section className="reviews" aria-labelledby="reviews-title">
      <h2 id="reviews-title">
        Avaliações{status === 'ready' && reviews.totalCount > 0 ? ` (${reviews.totalCount})` : ''}
      </h2>

      {status === 'loading' && (
        <p className="reviews__note" role="status">
          Carregando avaliações…
        </p>
      )}

      {status === 'error' && (
        <div className="reviews__error">
          <Alert messages={error.messages} />
          <button type="button" className="btn btn--ghost" onClick={retry}>
            Tentar de novo
          </button>
        </div>
      )}

      {status === 'ready' && reviews.items.length === 0 && (
        <p className="reviews__note">Este jogo ainda não tem avaliações.</p>
      )}

      {status === 'ready' && reviews.items.length > 0 && (
        <>
          <ul className="review-list">
            {reviews.items.map((review) => (
              <ReviewItem key={review.id} review={review} />
            ))}
          </ul>
          {moreError && <Alert messages={moreError.messages} />}
          {reviews.hasNextPage && (
            <button type="button" className="btn btn--ghost" onClick={loadMore} disabled={loadingMore}>
              {loadingMore ? 'Carregando…' : 'Ver mais avaliações'}
            </button>
          )}
        </>
      )}
    </section>
  );
}

function ReviewItem({ review }) {
  return (
    <li className="review">
      <div className="review__head">
        <Stars value={review.rating} />
        <h3 className="review__title">{review.title}</h3>
        <span className={`tag ${review.isRecommended ? 'tag--yes' : 'tag--no'}`}>
          {review.isRecommended ? 'Recomenda' : 'Não recomenda'}
        </span>
      </div>
      <p className="review__meta">
        {review.customerName}, em {formatDate(review.createdAt)}
      </p>
      <p className="review__body">{review.body}</p>
    </li>
  );
}

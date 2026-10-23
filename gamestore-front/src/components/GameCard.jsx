import { Link } from 'react-router-dom';
import { discountPercent, formatPrice, formatRating } from '../utils/format';
import GameCover from './GameCover';
import StatusBadge from './StatusBadge';

export default function GameCard({ game, actions, linkState }) {
  const percent = discountPercent(game.price, game.currentPrice);
  const hasReviews = game.reviewCount > 0;
  const rating = formatRating(game.averageRating);

  return (
    <li className="game-card">
      <div className="game-card__cover">
        <GameCover title={game.title} url={game.coverImageUrl} />
        <StatusBadge status={game.status} className="status-badge--corner" />
        {percent > 0 && <span className="badge game-card__badge">-{percent}%</span>}
      </div>

      <div className="game-card__body">
        <h2 className="game-card__title">
          <Link to={`/jogos/${game.id}`} state={linkState} className="game-card__link">
            {game.title}
          </Link>
        </h2>
        <p className="game-card__meta">{game.developer}</p>
        <p className="chip">{game.categoryName}</p>

        <div className="game-card__footer">
          <p className="price">
            {percent > 0 && <span className="price__old">{formatPrice(game.price)}</span>}
            <span>{formatPrice(game.currentPrice)}</span>
          </p>
          <p className="rating">
            {hasReviews ? (
              <>
                <span aria-hidden="true">★ {rating}</span>{' '}
                <span className="visually-hidden">Nota {rating} de 5, </span>(
                {game.reviewCount} {game.reviewCount === 1 ? 'avaliação' : 'avaliações'})
              </>
            ) : (
              'Sem avaliações'
            )}
          </p>
        </div>
      </div>

      {actions && <div className="game-card__actions">{actions}</div>}
    </li>
  );
}

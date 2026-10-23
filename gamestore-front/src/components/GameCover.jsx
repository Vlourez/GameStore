import { useState } from 'react';

/** Capa do jogo. Sem imagem (ou se ela falhar ao carregar), mostra a inicial do título. */
export default function GameCover({ title, url, lazy = true }) {
  const [failed, setFailed] = useState(false);

  if (!url || failed) {
    return (
      <span className="cover-initial" aria-hidden="true">
        {title.charAt(0).toUpperCase()}
      </span>
    );
  }

  return <img src={url} alt="" loading={lazy ? 'lazy' : 'eager'} onError={() => setFailed(true)} />;
}

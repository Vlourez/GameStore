import { useEffect } from 'react';

/** Atualiza o título da aba (ajuda quem usa leitor de tela e quem tem várias abas abertas). */
export function usePageTitle(title) {
  useEffect(() => {
    document.title = title ? `${title} | GameStore` : 'GameStore';
  }, [title]);
}

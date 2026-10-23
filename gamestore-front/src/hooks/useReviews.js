import { useCallback, useEffect, useState } from 'react';
import { api } from '../services/api';

const EMPTY = { items: [], page: 0, hasNextPage: false, totalCount: 0 };

/**
 * Avaliações de um jogo, de PAGE_SIZE em PAGE_SIZE.
 * status: "loading" (primeira carga), "ready" ou "error" (a primeira carga falhou).
 * Se só o "ver mais" falhar, a lista continua na tela e o erro vai em moreError.
 */
export function useReviews(gameId, pageSize = 5) {
  const [reviews, setReviews] = useState(EMPTY);
  const [status, setStatus] = useState('loading');
  const [error, setError] = useState(null);
  const [loadingMore, setLoadingMore] = useState(false);
  const [moreError, setMoreError] = useState(null);
  const [attempt, setAttempt] = useState(0);

  useEffect(() => {
    const controller = new AbortController();
    api
      .get(`/api/games/${gameId}/reviews`, { params: { page: 1, pageSize }, signal: controller.signal })
      .then((result) => {
        if (controller.signal.aborted) return;
        setReviews({ items: result.items, page: result.page, hasNextPage: result.hasNextPage, totalCount: result.totalCount });
        setStatus('ready');
      })
      .catch((err) => {
        if (err.name === 'AbortError') return;
        setError(err);
        setStatus('error');
      });
    return () => controller.abort();
  }, [gameId, pageSize, attempt]);

  const retry = useCallback(() => {
    setStatus('loading');
    setError(null);
    setAttempt((n) => n + 1);
  }, []);

  const loadMore = useCallback(async () => {
    setLoadingMore(true);
    setMoreError(null);
    try {
      const result = await api.get(`/api/games/${gameId}/reviews`, { params: { page: reviews.page + 1, pageSize } });
      setReviews((current) => ({
        items: [...current.items, ...result.items],
        page: result.page,
        hasNextPage: result.hasNextPage,
        totalCount: result.totalCount,
      }));
    } catch (err) {
      setMoreError(err);
    } finally {
      setLoadingMore(false);
    }
  }, [gameId, pageSize, reviews.page]);

  return { reviews, status, error, retry, loadMore, loadingMore, moreError };
}

import { useCallback, useEffect, useMemo, useState } from 'react';
import { api, setUnauthorizedHandler } from '../services/api';
import { clearToken, getToken, isTokenExpired, setToken } from '../services/tokenStorage';
import { AuthContext } from './AuthContext';

/**
 * Guarda quem está logado e expõe login / cadastro / logout para o app inteiro.
 * O token fica no localStorage; ao abrir o site, ele é validado em GET /api/customers/me.
 */
export default function AuthProvider({ children }) {
  // Decide uma única vez, ao montar: há token? ele já venceu?
  const [initial] = useState(() => {
    const token = getToken();
    if (token && isTokenExpired(token)) {
      clearToken();
      return { needsCheck: false, expired: true };
    }
    return { needsCheck: Boolean(token), expired: false };
  });

  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(initial.needsCheck);
  const [sessionExpired, setSessionExpired] = useState(initial.expired);

  const logout = useCallback(() => {
    clearToken();
    setUser(null);
    setSessionExpired(false);
  }, []);

  // Qualquer 401 com token (vencido, inválido) derruba a sessão.
  useEffect(() => {
    setUnauthorizedHandler(() => {
      clearToken();
      setUser(null);
      setSessionExpired(true);
    });
    return () => setUnauthorizedHandler(null);
  }, []);

  // Ao abrir o site com um token salvo, confirma com a API e carrega o perfil (inclui o role).
  useEffect(() => {
    if (!initial.needsCheck) return undefined;

    const controller = new AbortController();
    api
      .get('/api/customers/me', { signal: controller.signal })
      .then(setUser)
      .catch(() => {
        // 401: o handler acima já encerrou a sessão. Falha de rede: mantém o token para tentar depois.
      })
      .finally(() => {
        if (!controller.signal.aborted) setLoading(false);
      });

    return () => controller.abort();
  }, [initial.needsCheck]);

  const startSession = useCallback((data) => {
    setToken(data.accessToken);
    setUser(data.customer);
    setSessionExpired(false);
    return data.customer;
  }, []);

  const login = useCallback(
    async (email, password) => startSession(await api.post('/api/auth/login', { email, password })),
    [startSession],
  );

  const register = useCallback(
    async (payload) => startSession(await api.post('/api/auth/register', payload)),
    [startSession],
  );

  const value = useMemo(
    () => ({
      user,
      loading,
      sessionExpired,
      isAuthenticated: user !== null,
      isAdmin: user?.role === 'Admin',
      login,
      register,
      logout,
    }),
    [user, loading, sessionExpired, login, register, logout],
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

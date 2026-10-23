import { Link, Navigate, Outlet, useLocation } from 'react-router-dom';
import { useAuth } from '../hooks/useAuth';

/**
 * Protege um grupo de rotas.
 *   <Route element={<RequireAuth />}>                 exige estar logado
 *   <Route element={<RequireAuth role="Admin" />}>    exige ser administrador
 */
export default function RequireAuth({ role }) {
  const { user, loading } = useAuth();
  const location = useLocation();

  if (loading) {
    return (
      <p className="state" role="status">
        Verificando sua sessão…
      </p>
    );
  }

  if (!user) {
    // Guarda de onde o usuário veio para voltar depois do login.
    return <Navigate to="/entrar" replace state={{ from: location }} />;
  }

  if (role && user.role !== role) {
    return (
      <section className="state">
        <h1>Acesso restrito</h1>
        <p>Esta área é só para administradores.</p>
        <Link to="/" className="btn btn--ghost">
          Voltar ao catálogo
        </Link>
      </section>
    );
  }

  return <Outlet />;
}

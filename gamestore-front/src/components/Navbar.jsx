import { useState } from 'react';
import { Link, NavLink, useNavigate } from 'react-router-dom';
import { useAuth } from '../hooks/useAuth';
import { firstName } from '../utils/format';
import ComingSoonDialog from './ComingSoonDialog';
import Logo from './Logo';

export default function Navbar() {
  const { user, isAdmin, loading, logout } = useAuth();
  const navigate = useNavigate();
  const [showComingSoon, setShowComingSoon] = useState(false);

  function handleLogout() {
    logout();
    navigate('/');
  }

  let account = null;
  if (!loading && user) {
    account = (
      <>
        <span className="nav-greeting">Olá, {firstName(user.fullName)}</span>
        <button type="button" className="btn btn--ghost" onClick={handleLogout}>
          Sair
        </button>
      </>
    );
  } else if (!loading) {
    account = (
      <>
        <NavLink to="/entrar" className="nav-link">
          Entrar
        </NavLink>
        <Link to="/cadastro" className="btn btn--primary">
          Criar conta
        </Link>
      </>
    );
  }

  return (
    <header className="site-header">
      <div className="container site-header__inner">
        <Link to="/" className="brand">
          <Logo />
          <span>GameStore</span>
        </Link>

        <nav className="nav" aria-label="Principal">
          <NavLink to="/" end className="nav-link">
            Catálogo
          </NavLink>
          {isAdmin && (
            <NavLink to="/novo-jogo" className="nav-link">
              Cadastrar jogo
            </NavLink>
          )}
        </nav>

        <div className="nav-user">
          <button type="button" className="nav-cart" aria-label="Carrinho" onClick={() => setShowComingSoon(true)}>
            <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
              <circle cx="9" cy="20" r="1.5" />
              <circle cx="18" cy="20" r="1.5" />
              <path d="M2 3h3l2.7 12.4a2 2 0 0 0 2 1.6h8.6a2 2 0 0 0 2-1.6L22 7H6" />
            </svg>
          </button>
          {account}
        </div>
      </div>

      <ComingSoonDialog open={showComingSoon} onClose={() => setShowComingSoon(false)} />
    </header>
  );
}

import { Link } from 'react-router-dom';
import { usePageTitle } from '../hooks/usePageTitle';

export default function NotFound() {
  usePageTitle('Página não encontrada');

  return (
    <section className="state">
      <h1>Página não encontrada</h1>
      <p>O endereço que você abriu não existe ou foi movido.</p>
      <Link to="/" className="btn btn--primary">
        Ir para o catálogo
      </Link>
    </section>
  );
}

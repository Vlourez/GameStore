import { useNavigate } from 'react-router-dom';
import GameForm from '../components/GameForm';
import { usePageTitle } from '../hooks/usePageTitle';
import { api } from '../services/api';

export default function NovoJogo() {
  usePageTitle('Cadastrar jogo');
  const navigate = useNavigate();

  async function handleSubmit(payload) {
    const game = await api.post('/api/games', payload);
    navigate('/', { state: { notice: `“${game.title}” foi cadastrado no catálogo.` } });
  }

  return (
    <section className="panel">
      <h1>Cadastrar jogo</h1>
      <p className="panel__lead">Preencha os dados do jogo. Ele aparece no catálogo assim que for salvo.</p>
      <GameForm submitLabel="Salvar jogo" submittingLabel="Salvando…" onSubmit={handleSubmit} />
    </section>
  );
}

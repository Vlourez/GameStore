import { BrowserRouter, Route, Routes } from 'react-router-dom';
import Layout from './components/Layout';
import RequireAuth from './components/RequireAuth';
import AuthProvider from './context/AuthProvider';
import EditarJogo from './pages/EditarJogo';
import Home from './pages/Home';
import JogoDetalhe from './pages/JogoDetalhe';
import Login from './pages/Login';
import NotFound from './pages/NotFound';
import NovoJogo from './pages/NovoJogo';
import Register from './pages/Register';

export default function App() {
  return (
    <BrowserRouter>
      <AuthProvider>
        <Routes>
          <Route element={<Layout />}>
            <Route index element={<Home />} />
            <Route path="entrar" element={<Login />} />
            <Route path="cadastro" element={<Register />} />
            <Route path="jogos/:id" element={<JogoDetalhe />} />

            {/* Só administradores */}
            <Route element={<RequireAuth role="Admin" />}>
              <Route path="novo-jogo" element={<NovoJogo />} />
              <Route path="jogos/:id/editar" element={<EditarJogo />} />
            </Route>

            <Route path="*" element={<NotFound />} />
          </Route>
        </Routes>
      </AuthProvider>
    </BrowserRouter>
  );
}

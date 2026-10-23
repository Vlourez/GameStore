import { useState } from 'react';
import { Link, Navigate, useLocation } from 'react-router-dom';
import Alert from '../components/Alert';
import Field from '../components/Field';
import { useAuth } from '../hooks/useAuth';
import { usePageTitle } from '../hooks/usePageTitle';

const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

function validate({ email, password }) {
  const errors = {};
  if (!email.trim()) errors.email = 'Informe o seu e-mail.';
  else if (!EMAIL_PATTERN.test(email.trim())) errors.email = 'Esse e-mail não parece válido.';
  if (!password) errors.password = 'Informe a sua senha.';
  return errors;
}

function messagesFor(err) {
  if (err.status === 401) {
    return [err.message === 'Account is deactivated.' ? 'Esta conta está desativada.' : 'E-mail ou senha incorretos.'];
  }
  return err.messages ?? [err.message];
}

export default function Login() {
  usePageTitle('Entrar');

  const { user, login, sessionExpired } = useAuth();
  const location = useLocation();
  const [form, setForm] = useState({ email: '', password: '' });
  const [fieldErrors, setFieldErrors] = useState({});
  const [apiErrors, setApiErrors] = useState([]);
  const [submitting, setSubmitting] = useState(false);

  // Se veio de uma página protegida, volta para ela depois de entrar.
  const from = location.state?.from;
  const destination = from ? `${from.pathname}${from.search}` : '/';

  // Já está logado (inclusive logo após um login bem-sucedido): sai desta tela.
  if (user) return <Navigate to={destination} replace />;

  function handleChange(event) {
    const { name, value } = event.target;
    setForm((current) => ({ ...current, [name]: value }));
    // Ao corrigir um campo, a mensagem de erro dele some na hora.
    setFieldErrors((current) => (current[name] ? { ...current, [name]: undefined } : current));
  }

  async function handleSubmit(event) {
    event.preventDefault();
    const errors = validate(form);
    setFieldErrors(errors);
    setApiErrors([]);

    const firstInvalid = ['email', 'password'].find((key) => errors[key]);
    if (firstInvalid) {
      document.getElementById(firstInvalid)?.focus();
      return;
    }

    setSubmitting(true);
    try {
      await login(form.email.trim(), form.password);
    } catch (err) {
      setApiErrors(messagesFor(err));
      setSubmitting(false);
    }
  }

  return (
    <section className="auth">
      <h1>Entrar</h1>
      <p className="auth__lead">Acesse sua conta para comprar jogos e montar sua biblioteca.</p>

      {sessionExpired && <Alert type="info" messages={['Sua sessão expirou. Entre novamente para continuar.']} />}
      {apiErrors.length > 0 && <Alert messages={apiErrors} />}

      <form className="form" onSubmit={handleSubmit} noValidate>
        <Field
          id="email"
          label="E-mail"
          type="email"
          autoComplete="email"
          value={form.email}
          onChange={handleChange}
          error={fieldErrors.email}
        />
        <Field
          id="password"
          label="Senha"
          type="password"
          autoComplete="current-password"
          value={form.password}
          onChange={handleChange}
          error={fieldErrors.password}
        />
        <button type="submit" className="btn btn--primary btn--block" disabled={submitting}>
          {submitting ? 'Entrando…' : 'Entrar'}
        </button>
      </form>

      <p className="auth__alt">
        Ainda não tem conta? <Link to="/cadastro">Criar conta</Link>
      </p>
    </section>
  );
}

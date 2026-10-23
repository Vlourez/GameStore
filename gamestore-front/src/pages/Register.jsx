import { useState } from 'react';
import { Link, Navigate } from 'react-router-dom';
import Alert from '../components/Alert';
import Field from '../components/Field';
import { useAuth } from '../hooks/useAuth';
import { usePageTitle } from '../hooks/usePageTitle';
import { todayUtc } from '../utils/format';

const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const FIELD_ORDER = ['fullName', 'email', 'password', 'confirmPassword', 'birthDate'];

// Mesmas regras do back (RegisterRequestValidator), para o usuário ver o erro antes de enviar.
function validate({ fullName, email, password, confirmPassword, birthDate }) {
  const errors = {};

  if (!fullName.trim()) errors.fullName = 'Informe o seu nome completo.';
  else if (fullName.trim().length > 100) errors.fullName = 'O nome deve ter no máximo 100 caracteres.';

  if (!email.trim()) errors.email = 'Informe o seu e-mail.';
  else if (!EMAIL_PATTERN.test(email.trim())) errors.email = 'Esse e-mail não parece válido.';

  if (password.length < 8) errors.password = 'A senha precisa ter pelo menos 8 caracteres.';
  else if (new TextEncoder().encode(password).length > 72) errors.password = 'A senha é longa demais (máximo de 72 bytes).';
  else if (!/[A-Z]/.test(password)) errors.password = 'A senha precisa ter pelo menos uma letra maiúscula.';
  else if (!/[0-9]/.test(password)) errors.password = 'A senha precisa ter pelo menos um número.';

  if (confirmPassword !== password) errors.confirmPassword = 'As senhas não são iguais.';

  if (birthDate && birthDate > todayUtc()) errors.birthDate = 'A data de nascimento não pode ser no futuro.';

  return errors;
}

export default function Register() {
  usePageTitle('Criar conta');

  const { user, register } = useAuth();
  const [form, setForm] = useState({ fullName: '', email: '', password: '', confirmPassword: '', birthDate: '' });
  const [fieldErrors, setFieldErrors] = useState({});
  const [apiErrors, setApiErrors] = useState([]);
  const [submitting, setSubmitting] = useState(false);

  // Conta criada: a API já devolve o token, então o usuário entra logado.
  if (user) return <Navigate to="/" replace />;

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

    const firstInvalid = FIELD_ORDER.find((key) => errors[key]);
    if (firstInvalid) {
      document.getElementById(firstInvalid)?.focus();
      return;
    }

    setSubmitting(true);
    try {
      await register({
        fullName: form.fullName.trim(),
        email: form.email.trim(),
        password: form.password,
        birthDate: form.birthDate || null,
      });
    } catch (err) {
      if (err.status === 409) {
        setFieldErrors({ email: 'Já existe uma conta com esse e-mail.' });
        document.getElementById('email')?.focus();
      } else {
        setApiErrors(err.messages ?? [err.message]);
      }
      setSubmitting(false);
    }
  }

  return (
    <section className="auth">
      <h1>Criar conta</h1>
      <p className="auth__lead">Leva menos de um minuto e você já pode explorar o catálogo logado.</p>

      {apiErrors.length > 0 && <Alert messages={apiErrors} />}

      <form className="form" onSubmit={handleSubmit} noValidate>
        <Field
          id="fullName"
          label="Nome completo"
          autoComplete="name"
          value={form.fullName}
          onChange={handleChange}
          error={fieldErrors.fullName}
        />
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
          autoComplete="new-password"
          hint="Mínimo de 8 caracteres, com uma letra maiúscula e um número."
          value={form.password}
          onChange={handleChange}
          error={fieldErrors.password}
        />
        <Field
          id="confirmPassword"
          label="Confirmar senha"
          type="password"
          autoComplete="new-password"
          value={form.confirmPassword}
          onChange={handleChange}
          error={fieldErrors.confirmPassword}
        />
        <Field
          id="birthDate"
          label="Data de nascimento (opcional)"
          type="date"
          autoComplete="bday"
          max={todayUtc()}
          value={form.birthDate}
          onChange={handleChange}
          error={fieldErrors.birthDate}
        />
        <button type="submit" className="btn btn--primary btn--block" disabled={submitting}>
          {submitting ? 'Criando conta…' : 'Criar conta'}
        </button>
      </form>

      <p className="auth__alt">
        Já tem conta? <Link to="/entrar">Entrar</Link>
      </p>
    </section>
  );
}

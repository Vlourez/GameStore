import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { api } from '../services/api';
import { EMPTY_FORM, FIELD_ORDER, formToPayload, validateGameForm } from '../utils/gameForm';
import Alert from './Alert';
import Field from './Field';

/**
 * Formulário de jogo, usado no cadastro e na edição.
 * Quem usa decide o que fazer com os dados: onSubmit(payload) recebe o corpo pronto para a API
 * e deve lançar o erro se a chamada falhar (o formulário mostra a mensagem).
 */
export default function GameForm({ initialForm = EMPTY_FORM, submitLabel, submittingLabel, priceHint, onSubmit }) {
  const [form, setForm] = useState(initialForm);
  const [fieldErrors, setFieldErrors] = useState({});
  const [apiErrors, setApiErrors] = useState([]);
  const [submitting, setSubmitting] = useState(false);

  const [categories, setCategories] = useState([]);
  const [categoriesLoading, setCategoriesLoading] = useState(true);
  const [categoriesError, setCategoriesError] = useState(null);

  useEffect(() => {
    const controller = new AbortController();
    api
      .get('/api/categories', { signal: controller.signal })
      .then(setCategories)
      .catch((err) => {
        if (err.name !== 'AbortError') setCategoriesError(err);
      })
      .finally(() => {
        if (!controller.signal.aborted) setCategoriesLoading(false);
      });
    return () => controller.abort();
  }, []);

  function handleChange(event) {
    const { name, value } = event.target;
    setForm((current) => ({ ...current, [name]: value }));
    // Ao corrigir um campo, a mensagem de erro dele some na hora.
    setFieldErrors((current) => (current[name] ? { ...current, [name]: undefined } : current));
  }

  async function handleSubmit(event) {
    event.preventDefault();
    const errors = validateGameForm(form);
    setFieldErrors(errors);
    setApiErrors([]);

    const firstInvalid = FIELD_ORDER.find((key) => errors[key]);
    if (firstInvalid) {
      document.getElementById(firstInvalid)?.focus();
      return;
    }

    setSubmitting(true);
    try {
      await onSubmit(formToPayload(form));
    } catch (err) {
      setApiErrors(err.messages ?? [err.message]);
    } finally {
      setSubmitting(false);
    }
  }

  const noCategories = !categoriesLoading && !categoriesError && categories.length === 0;

  return (
    <>
      {categoriesError && <Alert messages={categoriesError.messages} />}
      {noCategories && (
        <Alert
          type="info"
          messages={['Ainda não há categorias. Rode o seed do back ou crie uma categoria antes de salvar jogos.']}
        />
      )}
      {apiErrors.length > 0 && <Alert messages={apiErrors} />}

      <form className="form" onSubmit={handleSubmit} noValidate>
        <Field id="title" label="Título" value={form.title} onChange={handleChange} error={fieldErrors.title} />

        <div className="form-row">
          <Field
            id="developer"
            label="Desenvolvedora"
            value={form.developer}
            onChange={handleChange}
            error={fieldErrors.developer}
          />
          <Field
            id="publisher"
            label="Publicadora"
            value={form.publisher}
            onChange={handleChange}
            error={fieldErrors.publisher}
          />
        </div>

        <Field
          id="description"
          as="textarea"
          rows={4}
          label="Descrição"
          value={form.description}
          onChange={handleChange}
          error={fieldErrors.description}
        />

        <div className="form-row">
          <Field
            id="price"
            label="Preço (R$)"
            type="number"
            step="0.01"
            min="0"
            inputMode="decimal"
            hint={priceHint}
            value={form.price}
            onChange={handleChange}
            error={fieldErrors.price}
          />
          <Field
            id="releaseDate"
            label="Data de lançamento"
            type="date"
            value={form.releaseDate}
            onChange={handleChange}
            error={fieldErrors.releaseDate}
          />
        </div>

        <Field
          id="categoryId"
          as="select"
          label="Categoria"
          value={form.categoryId}
          onChange={handleChange}
          error={fieldErrors.categoryId}
          disabled={categoriesLoading || categories.length === 0}
        >
          <option value="">{categoriesLoading ? 'Carregando categorias…' : 'Escolha uma categoria'}</option>
          {categories.map((category) => (
            <option key={category.id} value={category.id}>
              {category.name}
            </option>
          ))}
        </Field>

        <Field
          id="coverImageUrl"
          label="Endereço da imagem de capa (opcional)"
          type="url"
          placeholder="https://"
          value={form.coverImageUrl}
          onChange={handleChange}
          error={fieldErrors.coverImageUrl}
        />

        <Field
          id="tags"
          label="Tags (opcional)"
          hint="Separe por vírgula. Exemplo: aventura, mundo aberto, cooperativo"
          value={form.tags}
          onChange={handleChange}
          error={fieldErrors.tags}
        />

        <div className="form-actions">
          <button type="submit" className="btn btn--primary" disabled={submitting}>
            {submitting ? submittingLabel : submitLabel}
          </button>
          <Link to="/" className="btn btn--ghost">
            Cancelar
          </Link>
        </div>
      </form>
    </>
  );
}

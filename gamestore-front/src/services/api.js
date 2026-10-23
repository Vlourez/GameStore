import { getToken } from './tokenStorage';

const BASE_URL = (import.meta.env.VITE_API_URL ?? 'http://localhost/GameStoreAPI-PHP/public').replace(/\/+$/, '');

/** Erro devolvido pela API (ou falha de conexão, com status 0). */
export class ApiError extends Error {
  constructor(status, message, errors = []) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
    this.errors = errors;
  }

  /** Lista de textos para mostrar ao usuário (a validação 422 devolve vários). */
  get messages() {
    return this.errors.length > 0 ? this.errors : [this.message];
  }
}

let unauthorizedHandler = null;

/** O AuthProvider registra aqui o que fazer quando um token deixa de valer (401). */
export function setUnauthorizedHandler(handler) {
  unauthorizedHandler = handler;
}

function buildQuery(params) {
  if (!params) return '';
  const search = new URLSearchParams();
  for (const [key, value] of Object.entries(params)) {
    if (value !== undefined && value !== null && value !== '') search.append(key, String(value));
  }
  const query = search.toString();
  return query ? `?${query}` : '';
}

async function request(path, { method = 'GET', body, params, signal } = {}) {
  const token = getToken();
  const headers = { Accept: 'application/json' };
  if (body !== undefined) headers['Content-Type'] = 'application/json';
  if (token) headers.Authorization = `Bearer ${token}`;

  let response;
  try {
    response = await fetch(`${BASE_URL}${path}${buildQuery(params)}`, {
      method,
      headers,
      body: body !== undefined ? JSON.stringify(body) : undefined,
      signal,
    });
  } catch (err) {
    if (err?.name === 'AbortError') throw err;
    throw new ApiError(
      0,
      'Não foi possível conectar à API. Verifique se o Apache e o MySQL estão ligados e se o VITE_API_URL está correto.',
    );
  }

  // 204 No Content (DELETE, reembolso, desconto...) não tem corpo para ler.
  if (response.status === 204) return null;

  const text = await response.text();
  let data = null;
  let notJson = false;
  if (text) {
    try {
      data = JSON.parse(text);
    } catch {
      notJson = true; // ex.: o PHP imprimiu um erro em HTML
    }
  }

  if (!response.ok) {
    // Só conta como "sessão vencida" se a requisição levou um token.
    if (response.status === 401 && token && unauthorizedHandler) unauthorizedHandler();
    const message = data?.message ?? `Resposta inesperada da API (HTTP ${response.status}).`;
    throw new ApiError(response.status, message, Array.isArray(data?.errors) ? data.errors : []);
  }

  if (notJson) throw new ApiError(response.status, 'A API devolveu uma resposta que não é JSON.');
  return data;
}

export const api = {
  get: (path, options) => request(path, { ...options, method: 'GET' }),
  post: (path, body, options) => request(path, { ...options, method: 'POST', body }),
  put: (path, body, options) => request(path, { ...options, method: 'PUT', body }),
  patch: (path, body, options) => request(path, { ...options, method: 'PATCH', body }),
  delete: (path, options) => request(path, { ...options, method: 'DELETE' }),
};

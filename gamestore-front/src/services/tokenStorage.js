// Guarda o token JWT. Usa o localStorage (sobrevive ao recarregar a página) e,
// se o navegador bloquear o armazenamento, mantém o token só em memória.

const KEY = 'gamestore.token';
let memoryToken = null;

export function getToken() {
  if (memoryToken) return memoryToken;
  try {
    return localStorage.getItem(KEY);
  } catch {
    return null;
  }
}

export function setToken(token) {
  memoryToken = token;
  try {
    localStorage.setItem(KEY, token);
  } catch {
    // Armazenamento indisponível: o login vale até recarregar a página.
  }
}

export function clearToken() {
  memoryToken = null;
  try {
    localStorage.removeItem(KEY);
  } catch {
    // Nada a limpar.
  }
}

/**
 * Lê o campo "exp" do JWT (sem validar a assinatura, isso é trabalho do back)
 * para evitar mandar à API um token que já venceu.
 */
export function isTokenExpired(token) {
  try {
    const payload = token.split('.')[1];
    const base64 = payload.replace(/-/g, '+').replace(/_/g, '/');
    const { exp } = JSON.parse(atob(base64));
    return typeof exp === 'number' && exp * 1000 <= Date.now();
  } catch {
    return false; // token ilegível: deixa o servidor decidir
  }
}

# GameStore (front-end)

Interface em React para a **GameStoreAPI** (PHP). Vite + React 19 + React Router.

## Como rodar

1. Suba a API (Apache e MySQL no XAMPP) e confirme que ela responde, por exemplo em
   `http://localhost/GameStoreAPI-PHP/public/api/games`.
2. Copie `.env.example` para `.env` e ajuste a URL se o seu caminho for diferente:
   ```
   VITE_API_URL=http://localhost/GameStoreAPI-PHP/public
   ```
3. Instale e rode:
   ```bash
   npm install
   npm run dev
   ```
   O site abre em `http://localhost:5173`.

> O CORS da API já libera qualquer origem por padrão. Para restringir, defina
> `CORS_ALLOWED_ORIGIN=http://localhost:5173` no `.env` da API.

## Contas de teste

O seed da API cria um administrador (veja o README da API para e-mail e senha).
Qualquer conta criada em **Criar conta** é cliente comum.

## Rotas

| Rota | Acesso | O que faz |
| --- | --- | --- |
| `/` | público | Catálogo com busca, filtro por categoria, ordenação e paginação |
| `/jogos/:id` | público | Página do jogo: descrição, preço, tags e avaliações |
| `/entrar` | público | Login |
| `/cadastro` | público | Criação de conta (já entra logado) |
| `/novo-jogo` | **admin** | Cadastro de jogo |
| `/jogos/:id/editar` | **admin** | Edição de jogo (formulário preenchido com os dados atuais) |

No catálogo, o admin vê **Editar** e **Excluir** em cada card. A exclusão pede confirmação e remove o jogo do catálogo
(exclusão lógica no back: o registro não é apagado do banco).

Quem não está logado e abre uma rota protegida é levado a `/entrar` e volta para ela depois de entrar.
Cliente comum que abre uma rota de admin vê "Acesso restrito".

## Catálogo e filtros

Busca, categoria, ordem e página ficam na própria URL, por exemplo
`/?busca=zelda&categoria=<id>&ordem=menor-preco&pagina=2`. Por isso o botão voltar do navegador funciona,
a página recarrega no mesmo ponto e dá para mandar o link para alguém. São 12 jogos por página, dos mais
recentes para os mais antigos (a API, sem ordem definida, devolve do mais antigo para o mais novo, então o
front pede a ordem invertida). Ao clicar em um jogo, a página dele abre e o botão "Voltar ao catálogo"
mantém os filtros.

## Estrutura

```
src/
  services/
    api.js            cliente HTTP único: URL base, Authorization, 204, erros {message, errors}, 401
    tokenStorage.js   guarda o JWT (localStorage) e detecta token vencido
  context/
    AuthContext.js    contexto
    AuthProvider.jsx  login, cadastro, logout e sessão (valida o token em /customers/me)
  hooks/              useAuth, usePageTitle, useReviews
  components/         Layout, Navbar, RequireAuth, Field, Alert, GameCard, GameCover, GameForm, ConfirmDialog,
                      CatalogFilters, Pagination, StatusBadge, Stars, Logo
  pages/              Home, JogoDetalhe, Login, Register, NovoJogo, EditarJogo, NotFound
  utils/format.js     preço em R$, datas da API (UTC), nome
  utils/gameForm.js   validação e conversão do formulário de jogo (cadastro e edição)
  utils/catalog.js    filtros do catálogo: lê e monta a URL e os parâmetros da API
  index.css           tokens de design e estilos globais
```

## Como falar com a API

Use sempre o `api` de `src/services/api.js`, nunca `fetch` direto:

```js
import { api } from '../services/api';

const page = await api.get('/api/games', { params: { page: 1, search: 'zelda' } });
const game = await api.post('/api/games', { title: '...' });   // já vai com o token
await api.delete(`/api/wishlist/${gameId}`);                   // 204: devolve null
```

Em caso de erro ele lança um `ApiError` com `status`, `message` e `messages` (lista para exibir):

```js
try { await api.post(...); } catch (err) { setErrors(err.messages); }
```

## Decisões de design

Tema escuro violeta-tinta com um único acento amarelo, usado só em preço, botão principal e foco.
Fontes: Chakra Petch (marca, títulos, preços) e Figtree (texto). As cores e medidas ficam como
variáveis no topo de `src/index.css`.

## Roadmap

- [x] Limpeza do template, `.env`, cliente HTTP, autenticação
- [x] Login, cadastro, rotas protegidas
- [x] Catálogo: paginação, busca, filtro por categoria, ordenação
- [x] Detalhe do jogo e lista de avaliações
- [ ] Carrinho, checkout, biblioteca, lista de desejos, compras e reembolso, perfil
- [x] Admin: editar e excluir jogos
- [ ] Admin: status do jogo, desconto e categorias
- [ ] Acabamento: README com capturas de tela, deploy

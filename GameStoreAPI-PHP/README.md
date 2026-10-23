# GameStoreAPI — PHP (Clean Architecture)

Reescrita completa da **GameStoreAPI** original (.NET / C#, Clean Architecture com EF Core + PostgreSQL) em **PHP puro**, mantendo a mesma separação de camadas, agora usando **MySQL** via **PDO** — pronta para rodar em **XAMPP** com **phpMyAdmin**.

Sem frameworks. Sem dependências de terceiros (nem para JWT, nem para UUID). Apenas PHP 8.1+, PDO e Composer para o autoload PSR-4.

## Arquitetura

A estrutura de pastas espelha o projeto original:

```
src/
  Domain/          # Entidades, enums, exceções e interfaces de repositório (não depende de mais nada)
    Common/        # BaseEntity, DomainException, PagedResult
    Entities/      # Game, Category, Customer, Purchase, PurchaseItem, LibraryEntry, WishlistEntry, Review, Achievement
    Enums/         # GameStatus, PurchaseStatus, AchievementType
    Interfaces/    # Contratos de repositório + UnitOfWorkInterface

  Application/     # Casos de uso, orquestra o Domain
    DTOs/          # Request/ e Response/ (objetos simples de transporte)
    Interfaces/    # Contratos dos serviços
    Services/      # AuthService, GameService, CategoryService, PurchaseService,
                    # ReviewService, WishlistService, LibraryService, CustomerService, AchievementService
    Validators/    # Validação de entrada (equivalente ao FluentValidation)
    Mappings/      # GameStoreMapper (Entidade -> Response DTO)
    Exceptions/    # NotFoundException, ConflictException, ValidationException, etc.

  Infrastructure/  # Implementação de acesso a dados
    Database/      # Connection (PDO singleton)
    Repositories/  # Implementações PDO/MySQL de cada repositório + UnitOfWork

  API/             # Camada HTTP
    Http/          # Request / Response (wrappers simples)
    Middleware/     # AuthMiddleware (JWT), ExceptionHandler
    Controllers/    # Um controller por recurso
    Router.php      # Roteador com suporte a parâmetros, auth e roles

  Support/         # Uuid, Env (.env loader), Jwt (HS256 sem lib externa)

public/
  index.php        # Front controller — registra todas as rotas e inicia o app
  .htaccess

database/
  schema.sql       # Schema completo (importar pelo phpMyAdmin)
  seed.php         # Popula categorias, jogos de exemplo e um usuário admin
```

O fluxo de uma requisição é o mesmo do projeto original:
**Router → Middleware (auth) → Controller → Service (Application) → UnitOfWork/Repository (Infrastructure) → Entidade (Domain)**, com o `Mapper` convertendo a entidade final em um DTO de resposta.

## Instalação no XAMPP

1. **Copie a pasta do projeto** para `C:\xampp\htdocs\GameStoreAPI-PHP` (Windows) ou `/opt/lampp/htdocs/GameStoreAPI-PHP` (Linux).

2. **Configure o Apache** para apontar para a pasta `public/` como raiz do site (recomendado), ou simplesmente acesse via `http://localhost/GameStoreAPI-PHP/public/` — o `.htaccess` da raiz também redireciona automaticamente para `public/` caso você aponte o VirtualHost para a raiz do projeto.

3. **Crie o banco de dados** pelo phpMyAdmin:
   - Abra `http://localhost/phpmyadmin`
   - Vá em **Importar** → selecione o arquivo `database/schema.sql` → **Executar**
   - Isso cria o banco `gamestore_db` e todas as tabelas.

4. **Configure o `.env`**:
   ```bash
   copy .env.example .env      # Windows
   cp .env.example .env        # Linux/Mac
   ```
   Ajuste `DB_USER` / `DB_PASS` se o seu MySQL do XAMPP não usar `root` sem senha (padrão do XAMPP). **Troque o `JWT_SECRET`** por um valor aleatório de pelo menos 32 caracteres — por exemplo:
   ```bash
   php -r "echo bin2hex(random_bytes(32));"
   ```
   Fora de `APP_ENV=local`, a API se recusa a iniciar com o segredo de exemplo. Para liberar só o seu front no CORS, defina `CORS_ALLOWED_ORIGIN=http://localhost:5173` (aceita várias origens separadas por vírgula; o padrão `*` libera qualquer origem).

5. **(Opcional, recomendado) Rode o Composer** para gerar o autoload otimizado:
   ```bash
   composer install
   ```
   O projeto **não tem dependências externas** — isso apenas gera `vendor/autoload.php`. Se você não rodar o Composer, o projeto funciona do mesmo jeito graças ao autoloader de fallback em `autoload.php`.

6. **Popule dados de exemplo** (categorias, jogos e um usuário admin):
   ```bash
   php database/seed.php
   ```
   Isso cria o login administrador:
   - **email:** `admin@gamestore.com`
   - **senha:** `Admin@123`

7. **Pronto.** Teste no navegador ou Postman/Insomnia:
   ```
   GET http://localhost/GameStoreAPI-PHP/public/api/games
   GET http://localhost/GameStoreAPI-PHP/public/api/categories
   POST http://localhost/GameStoreAPI-PHP/public/api/auth/login
   ```

## Principais endpoints

| Método | Rota | Auth | Descrição |
|---|---|---|---|
| POST | `/api/auth/register` | – | Cria uma conta |
| POST | `/api/auth/login` | – | Retorna um JWT |
| POST | `/api/auth/change-password` | ✅ | Troca a senha |
| GET | `/api/games` | – | Catálogo paginado (filtros: `search`, `categoryId`, `minPrice`, `maxPrice`, `status`, `orderBy`, `descending`, `page`, `pageSize`) |
| GET | `/api/games/{id}` | – | Detalhes de um jogo |
| GET | `/api/games/recommendations` | ✅ | Recomendações personalizadas |
| POST / PUT | `/api/games` / `/api/games/{id}` | ✅ Admin | Criar/editar jogo |
| PATCH | `/api/games/{id}/status` | ✅ Admin | Altera o status (`ComingSoon`, `Available`, `Discontinued`) — body: `{ "status": "Available" }` |
| PATCH / DELETE | `/api/games/{id}/discount` | ✅ Admin | Aplicar/remover desconto |
| GET / POST / PUT / DELETE | `/api/categories...` | leitura livre, escrita ✅ Admin | CRUD de categorias |
| GET / POST | `/api/purchases` | ✅ | Histórico / novo checkout |
| POST | `/api/purchases/{id}/refund` | ✅ | Reembolsar compra |
| GET | `/api/library` | ✅ | Jogos possuídos |
| POST | `/api/library/play-session` | ✅ | Registrar tempo jogado |
| GET / POST / DELETE | `/api/wishlist...` | ✅ | Lista de desejos |
| GET | `/api/games/{gameId}/reviews` | – | Avaliações de um jogo |
| POST / PUT / DELETE | `/api/reviews...` | ✅ | Criar/editar/remover avaliação (exige possuir o jogo) |
| GET / PUT | `/api/customers/me` | ✅ | Perfil do usuário logado |
| GET | `/api/customers/me/achievements` | ✅ | Conquistas do usuário |

Rotas marcadas com ✅ exigem o header `Authorization: Bearer {accessToken}`. Rotas "Admin" exigem que o usuário logado tenha `role = Admin` (o usuário criado pelo seed já é Admin).

## Convenções da API (para quem consome)

- **Autenticação:** `Authorization: Bearer {accessToken}`. O token vale 8 horas; não há refresh token.
- **Respostas sem corpo (`204 No Content`):** todos os `DELETE`, o reembolso, `discount`, `status`, `play-session` e `change-password`. Quem consome não deve chamar `res.json()` nesses casos.
- **Erros:** `{ "message": "...", "errors": ["..."] }` — `errors` só aparece em validação (422) e é uma lista de textos. Status usados: 400 (regra de negócio / JSON inválido), 401, 403, 404, 409, 422 e 500.
- **Datas:** vêm em UTC no formato `2024-03-15 18:00:00` (sem `Z`). Em JavaScript: `new Date(s.replace(' ', 'T') + 'Z')`. `releaseDate` e `birthDate` são só data (`YYYY-MM-DD`).
- **Carrinho:** não existe no back; o front monta a lista e envia tudo de uma vez em `POST /api/purchases` (`{ "gameIds": [...], "paymentMethod": "..." }`).
- **Regras de negócio relevantes:** só jogos `Available` podem ser comprados; comprar um jogo o remove da wishlist; reembolsar remove o jogo da biblioteca; uma categoria com jogos não pode ser apagada; em `PUT /api/games/{id}`, omitir `tags` mantém as atuais e enviar `[]` remove todas.

## Notas de implementação

- **Sem ORM**: os repositórios usam PDO puro com prepared statements. As entidades de domínio expõem métodos `create()` (nova instância, com regras de negócio) e `hydrate()` (reconstrução a partir de uma linha do banco), preservando encapsulamento.
- **JWT sem biblioteca**: `Support\Jwt` implementa HS256 manualmente (`hash_hmac`) — suficiente e seguro para o escopo do projeto, evitando depender do Packagist.
- **Regras de negócio** que existiam nas entidades C# (ex.: desconto deve ser menor que o preço, reembolso só de compra concluída, avaliação exige 1–5 estrelas) foram mantidas nos métodos das entidades PHP, lançando `DomainException`.
- **Transações**: o `UnitOfWork::transactional()` envolve o checkout de compra (criação da compra + itens + entradas na biblioteca) em uma transação MySQL, com rollback automático em caso de erro — equivalente ao `SaveChanges` do EF Core dentro de um escopo.
- **Conquistas (Achievements)**: `AchievementService::checkAndGrant()` é chamado após compra e após avaliação, concedendo automaticamente `FirstPurchase`, `Collector` e `ReviewWriter` quando as condições são atingidas.

## Smoke test

Com o servidor rodando e o seed aplicado, execute (precisa do PHP CLI, que já vem no XAMPP):

```bash
php tests/smoke.php http://localhost/GameStoreAPI-PHP/public
```

O script cria um usuário descartável e verifica as principais regras (auth, wishlist, compra, reembolso, validações, permissões de admin). Veja `CHANGELOG.md` para as correções mais recentes.

## Testado

Este projeto foi executado de ponta a ponta durante o desenvolvimento (servidor embutido do PHP + MySQL/MariaDB), cobrindo: registro/login, checkout com transação, biblioteca, wishlist, avaliações, conquistas automáticas, aplicação/remoção de desconto, autorização por papel (403 para não-admin, 201 para admin), validação de entrada (422) e reembolso.

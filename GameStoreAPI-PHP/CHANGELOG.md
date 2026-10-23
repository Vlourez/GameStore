# Changelog

## Correções do back-end (revisão geral)

### Segurança e configuração
- **`POST /api/games` voltou a exigir login de Admin.** Estava aberta (sem `auth`) por causa de um ajuste temporário.
- **`JWT_SECRET`:** novo valor aleatório no `.env`. A API agora avisa (em `APP_ENV=local`) ou se recusa a iniciar (fora de local) quando o segredo é curto ou ainda é o do exemplo.
- **Nome do banco padronizado em `gamestore_db`** (`schema.sql`, `.env.example`, `Connection`, `seed.php`), coerente com o `.env` que já era usado.
- **CORS em um lugar só** (`CorsMiddleware`), configurável por `CORS_ALLOWED_ORIGIN` (uma origem, várias separadas por vírgula, ou `*`). O preflight responde 204 antes de tocar no banco. O header `Allow-Headers` ficou apenas com `Content-Type, Authorization` — o back só lê `Authorization: Bearer`.
- **Header `Authorization` mais robusto:** regra no `public/.htaccess` e fallback no `Request` (`REDIRECT_HTTP_AUTHORIZATION` / `getallheaders`) para setups do Apache que "perdem" o header.
- Adicionados `.gitignore` (ignora `.env`, `vendor/` e logs) e `.gitattributes`.

### Bugs que geravam erro 500
- **Wishlist:** remover e adicionar o mesmo jogo de novo falhava (chave única). A remoção agora é definitiva, e linhas antigas "apagadas" são limpas ao re-adicionar.
- **Reviews:** apagar e recriar a review do mesmo jogo falhava pelo mesmo motivo. Corrigido.
- **Categorias:** recriar uma categoria com o slug de uma apagada falhava. O slug de categorias apagadas é liberado.
- **Jogos:** data de lançamento inválida/vazia, URL de capa longa demais, preço acima do limite da coluna e tags em excesso agora retornam **422** com mensagem clara.
- **`GET /api/games?status=Foo`** retorna 422 em vez de 500.
- **JSON inválido no corpo** retorna 400 (`Request body must be a valid JSON object.`) em vez de ser tratado como corpo vazio.
- Em `APP_DEBUG=true`, erros 500 mostram a mensagem real (ex.: falha de conexão com o banco). Em produção continua a mensagem genérica.

### Validações que faltavam
- Troca de senha (mesmas regras do cadastro, máximo de 72 bytes por causa do bcrypt, e a nova não pode ser igual à atual).
- Perfil (`fullName`, `avatarUrl`, `birthDate` real e não futura) e cadastro (`birthDate`).
- Desconto (`discountPrice > 0`), edição de review, wishlist (`gameId`), play-session (`minutes` de 1 a 1440), compra (máx. 50 jogos, `paymentMethod` até 50 caracteres).

### Regras de negócio
- **Reembolso remove o jogo da biblioteca** (dentro de uma transação), permitindo comprar de novo.
- **Só jogos `Available` podem ser comprados.**
- **Comprar um jogo o remove da wishlist.**
- **Categoria com jogos não pode ser apagada** (400).
- **Novo endpoint `PATCH /api/games/{id}/status`** (Admin) — body `{ "status": "ComingSoon" | "Available" | "Discontinued" }`.
- `PUT /api/games/{id}`: omitir `tags` mantém as atuais; enviar `[]` remove todas.
- Se o preço de um jogo for editado para um valor menor ou igual ao desconto vigente, o desconto é removido.

### Organização
- `public/index.php` reescrito: uma instrução por linha, rotas agrupadas, inicialização dentro de `try/catch` (falhas de boot respondem JSON com CORS).
- `tests/smoke.php`: teste de ponta a ponta contra a API real, cobrindo cada correção acima.
- README atualizado (nome do banco, CORS, `JWT_SECRET`, novo endpoint e convenções da API).

### Depois de atualizar
```bash
composer dump-autoload      # opcional: inclui as classes novas no autoload otimizado
php tests/smoke.php http://localhost/GameStoreAPI-PHP/public
```

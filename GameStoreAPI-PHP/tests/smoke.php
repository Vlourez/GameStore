<?php

declare(strict_types=1);

/**
 * Smoke test da GameStoreAPI — roda contra a API real (servidor + MySQL no ar, seed aplicado).
 *
 * Uso:
 *   php tests/smoke.php [base-url] [admin-email] [admin-senha]
 *   php tests/smoke.php http://localhost/GameStoreAPI-PHP/public
 *
 * Cria dados descartáveis (um usuário "smoke_*", uma categoria e um jogo) e limpa o que dá para limpar.
 * Sai com código 0 se tudo passou, 1 se algo falhou, 2 se a API não respondeu.
 */

if (PHP_SAPI !== 'cli') {
    exit("Execute pela linha de comando: php tests/smoke.php <base-url>\n");
}

$base = rtrim($argv[1] ?? 'http://localhost/GameStoreAPI-PHP/public', '/');
$adminEmail = $argv[2] ?? 'admin@gamestore.com';
$adminPass = $argv[3] ?? 'Admin@123';

$passed = 0;
$failed = 0;

/** @return array{status:int, json:mixed, raw:string, headers:array<string,string>} */
function call(string $method, string $path, array|string|null $body = null, ?string $token = null, array $extraHeaders = []): array
{
    global $base;

    $headers = ['Accept: application/json'];
    $content = '';
    if (is_array($body)) {
        $content = (string) json_encode($body);
        $headers[] = 'Content-Type: application/json';
    } elseif (is_string($body)) {
        $content = $body;
        $headers[] = 'Content-Type: application/json';
    }
    if ($token !== null) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    foreach ($extraHeaders as $h) {
        $headers[] = $h;
    }

    $ctx = stream_context_create(['http' => [
        'method' => $method,
        'header' => implode("\r\n", $headers),
        'content' => $content,
        'ignore_errors' => true,
        'timeout' => 20,
    ]]);

    $raw = @file_get_contents($base . $path, false, $ctx);

    $status = 0;
    $respHeaders = [];
    foreach ($http_response_header ?? [] as $line) {
        if (preg_match('#^HTTP/\S+\s+(\d{3})#', $line, $m)) {
            $status = (int) $m[1];
        } elseif (str_contains($line, ':')) {
            [$k, $v] = explode(':', $line, 2);
            $respHeaders[strtolower(trim($k))] = trim($v);
        }
    }

    $raw = $raw === false ? '' : $raw;

    return ['status' => $status, 'json' => json_decode($raw, true), 'raw' => $raw, 'headers' => $respHeaders];
}

function check(string $label, bool $ok, ?array $r = null): void
{
    global $passed, $failed;

    if ($ok) {
        $passed++;
        echo "  [OK]     {$label}\n";
        return;
    }

    $failed++;
    echo "  [FALHOU] {$label}\n";
    if ($r !== null) {
        echo "           -> HTTP {$r['status']} " . substr($r['raw'], 0, 300) . "\n";
    }
}

/** @param mixed $list */
function hasGame(mixed $list, string $gameId): bool
{
    return is_array($list) && in_array($gameId, array_column($list, 'gameId'), true);
}

$rand = bin2hex(random_bytes(4));

echo "GameStoreAPI smoke test -> {$base}\n\n";

// ---------------------------------------------------------------- conectividade / CORS
echo "Conectividade e CORS\n";
$r = call('GET', '/api/categories');
if ($r['status'] !== 200) {
    fwrite(STDERR, "A API não respondeu 200 em /api/categories (HTTP {$r['status']}).\n");
    fwrite(STDERR, "Confira a URL, se Apache e MySQL estão ligados, o .env e se o banco foi importado.\n{$r['raw']}\n");
    exit(2);
}
check('GET /api/categories é público', true);

$r = call('OPTIONS', '/api/games', null, null, [
    'Origin: http://localhost:5173',
    'Access-Control-Request-Method: POST',
    'Access-Control-Request-Headers: authorization,content-type',
]);
check('CORS: preflight responde 204 com Allow-Origin', $r['status'] === 204 && isset($r['headers']['access-control-allow-origin']), $r);

// ---------------------------------------------------------------- auth
echo "\nAutenticação\n";
$r = call('POST', '/api/games', ['title' => 'x']);
check('POST /api/games sem token -> 401', $r['status'] === 401, $r);

$r = call('POST', '/api/auth/login', '{json invalido');
check('JSON inválido -> 400', $r['status'] === 400, $r);

$r = call('POST', '/api/auth/login', ['email' => $adminEmail, 'password' => $adminPass]);
$adminToken = $r['json']['accessToken'] ?? null;
check('login do admin', $r['status'] === 200 && is_string($adminToken), $r);
if (!is_string($adminToken)) {
    fwrite(STDERR, "Sem login de admin não dá para continuar. Rodou `php database/seed.php`?\n");
    exit(2);
}

$r = call('GET', '/api/customers/me', null, $adminToken);
check('admin tem role Admin', ($r['json']['role'] ?? '') === 'Admin', $r);

$email = "smoke_{$rand}@example.com";
$pass = 'Smoke1234';

$r = call('POST', '/api/auth/register', ['fullName' => 'Smoke Test', 'email' => $email, 'password' => 'weak']);
check('registro com senha fraca -> 422', $r['status'] === 422, $r);

$r = call('POST', '/api/auth/register', ['fullName' => 'Smoke Test', 'email' => $email, 'password' => $pass, 'birthDate' => '2024-02-31']);
check('registro com data de nascimento inválida -> 422', $r['status'] === 422, $r);

$r = call('POST', '/api/auth/register', ['fullName' => 'Smoke Test', 'email' => $email, 'password' => $pass]);
$userToken = $r['json']['accessToken'] ?? null;
check('registro válido -> 201', $r['status'] === 201 && is_string($userToken), $r);
if (!is_string($userToken)) {
    exit(2);
}

$r = call('POST', '/api/auth/change-password', ['currentPassword' => $pass, 'newPassword' => 'abc'], $userToken);
check('troca de senha com senha nova fraca -> 422', $r['status'] === 422, $r);

$r = call('PUT', '/api/customers/me', ['fullName' => 'Smoke', 'birthDate' => 'not-a-date'], $userToken);
check('perfil com data inválida -> 422', $r['status'] === 422, $r);

$r = call('POST', '/api/library/play-session', ['gameId' => 'x', 'minutes' => 0], $userToken);
check('play-session com 0 minutos -> 422', $r['status'] === 422, $r);

$r = call('POST', '/api/categories', ['name' => 'X', 'description' => 'X', 'slug' => 'x'], $userToken);
check('usuário comum criando categoria -> 403', $r['status'] === 403, $r);

// ---------------------------------------------------------------- catálogo (admin)
echo "\nCatálogo (admin)\n";
$slug = "smoke-{$rand}";
$r = call('POST', '/api/categories', ['name' => "Smoke {$rand}", 'description' => 'Categoria de teste', 'slug' => $slug], $adminToken);
$catId = $r['json']['id'] ?? null;
check('criar categoria -> 201', $r['status'] === 201 && is_string($catId), $r);
if (!is_string($catId)) {
    exit(2);
}

$game = [
    'title' => "Smoke Game {$rand}",
    'description' => 'Jogo de teste',
    'developer' => 'Dev',
    'publisher' => 'Pub',
    'price' => 50.0,
    'releaseDate' => '2024-01-01',
    'categoryId' => $catId,
    'coverImageUrl' => '',
];

$r = call('POST', '/api/games', array_merge($game, ['releaseDate' => '2024-13-45']), $adminToken);
check('criar jogo com data inválida -> 422 (antes era 500)', $r['status'] === 422, $r);

$r = call('POST', '/api/games', array_merge($game, ['tags' => ['smoke', 'test']]), $adminToken);
$gameId = $r['json']['id'] ?? null;
check('criar jogo -> 201', $r['status'] === 201 && is_string($gameId), $r);
if (!is_string($gameId)) {
    exit(2);
}
check('tags salvas na criação', ($r['json']['tags'] ?? null) === ['smoke', 'test'], $r);

$r = call('PUT', "/api/games/{$gameId}", $game, $adminToken);
check('editar sem enviar tags mantém as tags', $r['status'] === 200 && ($r['json']['tags'] ?? null) === ['smoke', 'test'], $r);

$r = call('PUT', "/api/games/{$gameId}", array_merge($game, ['tags' => []]), $adminToken);
check('editar com tags [] remove todas', $r['status'] === 200 && ($r['json']['tags'] ?? null) === [], $r);

$r = call('GET', '/api/games?status=Foo');
check('filtro status inválido -> 422 (antes era 500)', $r['status'] === 422, $r);

$r = call('PATCH', "/api/games/{$gameId}/discount", ['discountPrice' => 0], $adminToken);
check('desconto 0 -> 422', $r['status'] === 422, $r);

$r = call('PATCH', "/api/games/{$gameId}/discount", ['discountPrice' => 40], $adminToken);
check('aplicar desconto válido -> 204', $r['status'] === 204, $r);

$r = call('DELETE', "/api/games/{$gameId}/discount", null, $adminToken);
check('remover desconto -> 204', $r['status'] === 204, $r);

$r = call('DELETE', "/api/categories/{$catId}", null, $adminToken);
check('apagar categoria com jogos -> 400', $r['status'] === 400, $r);

// ---------------------------------------------------------------- status do jogo
echo "\nStatus do jogo\n";
$r = call('PATCH', "/api/games/{$gameId}/status", ['status' => 'Foo'], $adminToken);
check('status inválido -> 422', $r['status'] === 422, $r);

$r = call('PATCH', "/api/games/{$gameId}/status", ['status' => 'Discontinued'], $adminToken);
check('marcar como Discontinued -> 204', $r['status'] === 204, $r);

$r = call('POST', '/api/purchases', ['gameIds' => [$gameId], 'paymentMethod' => 'CreditCard'], $userToken);
check('comprar jogo Discontinued -> 400', $r['status'] === 400, $r);

$r = call('PATCH', "/api/games/{$gameId}/status", ['status' => 'Available'], $adminToken);
check('voltar para Available -> 204', $r['status'] === 204, $r);

// ---------------------------------------------------------------- wishlist
echo "\nWishlist\n";
$r = call('POST', '/api/wishlist', ['gameId' => $gameId], $userToken);
check('adicionar à wishlist -> 201', $r['status'] === 201, $r);

$r = call('DELETE', "/api/wishlist/{$gameId}", null, $userToken);
check('remover da wishlist -> 204', $r['status'] === 204, $r);

$r = call('POST', '/api/wishlist', ['gameId' => $gameId], $userToken);
check('adicionar de novo após remover -> 201 (antes era 500)', $r['status'] === 201, $r);

// ---------------------------------------------------------------- compra / biblioteca
echo "\nCompra e biblioteca\n";
$r = call('POST', '/api/purchases', ['gameIds' => [$gameId], 'paymentMethod' => 'CreditCard'], $userToken);
$purchaseId = $r['json']['id'] ?? null;
check('comprar jogo -> 201', $r['status'] === 201 && is_string($purchaseId), $r);
if (!is_string($purchaseId)) {
    exit(2);
}

$r = call('GET', '/api/wishlist', null, $userToken);
check('comprar remove o jogo da wishlist', $r['status'] === 200 && !hasGame($r['json'], $gameId), $r);

$r = call('GET', '/api/library', null, $userToken);
check('jogo aparece na biblioteca', $r['status'] === 200 && hasGame($r['json'], $gameId), $r);

// ---------------------------------------------------------------- reviews
echo "\nReviews\n";
$review = ['gameId' => $gameId, 'rating' => 5, 'title' => 'Ótimo', 'body' => 'Muito bom', 'isRecommended' => true];
$r = call('POST', '/api/reviews', $review, $userToken);
$reviewId = $r['json']['id'] ?? null;
check('criar review -> 201', $r['status'] === 201 && is_string($reviewId), $r);

if (is_string($reviewId)) {
    $r = call('PUT', "/api/reviews/{$reviewId}", ['rating' => 9, 'title' => 'x', 'body' => 'y', 'isRecommended' => true], $userToken);
    check('editar review com nota 9 -> 422', $r['status'] === 422, $r);

    $r = call('DELETE', "/api/reviews/{$reviewId}", null, $userToken);
    check('apagar review -> 204', $r['status'] === 204, $r);

    $r = call('POST', '/api/reviews', $review, $userToken);
    check('criar review de novo após apagar -> 201 (antes era 500)', $r['status'] === 201, $r);
}

// ---------------------------------------------------------------- reembolso
echo "\nReembolso\n";
$r = call('POST', "/api/purchases/{$purchaseId}/refund", null, $userToken);
check('reembolsar -> 204', $r['status'] === 204, $r);

$r = call('GET', '/api/library', null, $userToken);
check('reembolso remove o jogo da biblioteca', $r['status'] === 200 && !hasGame($r['json'], $gameId), $r);

$r = call('POST', '/api/purchases', ['gameIds' => [$gameId], 'paymentMethod' => 'CreditCard'], $userToken);
check('comprar de novo após reembolso -> 201', $r['status'] === 201, $r);

// ---------------------------------------------------------------- limpeza e slug reutilizável
echo "\nLimpeza\n";
$r = call('DELETE', "/api/games/{$gameId}", null, $adminToken);
check('apagar jogo -> 204', $r['status'] === 204, $r);

$r = call('DELETE', "/api/categories/{$catId}", null, $adminToken);
check('apagar categoria vazia -> 204', $r['status'] === 204, $r);

$r = call('POST', '/api/categories', ['name' => "Smoke {$rand} bis", 'description' => 'Reuso de slug', 'slug' => $slug], $adminToken);
$cat2 = $r['json']['id'] ?? null;
check('recriar categoria com o mesmo slug -> 201 (antes era 500)', $r['status'] === 201 && is_string($cat2), $r);
if (is_string($cat2)) {
    call('DELETE', "/api/categories/{$cat2}", null, $adminToken);
}

echo "\n----------------------------------------\n";
echo "Passou: {$passed}   Falhou: {$failed}\n";
echo "(O usuário {$email} fica no banco: a API não tem endpoint para apagar usuários.)\n";

exit($failed === 0 ? 0 : 1);

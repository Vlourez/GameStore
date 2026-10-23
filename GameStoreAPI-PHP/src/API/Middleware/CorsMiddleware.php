<?php

declare(strict_types=1);

namespace GameStore\API\Middleware;

use GameStore\Support\Env;

/**
 * Único ponto de configuração de CORS da API.
 *
 * Roda ANTES de qualquer acesso a banco/rotas, para que o preflight (OPTIONS)
 * e as respostas de erro sempre carreguem os headers corretos.
 *
 * Configuração via .env:
 *   CORS_ALLOWED_ORIGIN=*                              (qualquer origem — padrão em dev)
 *   CORS_ALLOWED_ORIGIN=http://localhost:5173          (uma origem)
 *   CORS_ALLOWED_ORIGIN=http://localhost:5173,https://meusite.com   (várias)
 */
final class CorsMiddleware
{
    public static function handle(): void
    {
        $configured = trim((string) Env::get('CORS_ALLOWED_ORIGIN', '*'));
        $allowed = array_values(array_filter(array_map('trim', explode(',', $configured))));

        if ($allowed === [] || in_array('*', $allowed, true)) {
            header('Access-Control-Allow-Origin: *');
        } else {
            header('Vary: Origin');
            $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
            if ($origin !== '' && in_array($origin, $allowed, true)) {
                header('Access-Control-Allow-Origin: ' . $origin);
            }
        }

        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Max-Age: 86400');

        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }
}

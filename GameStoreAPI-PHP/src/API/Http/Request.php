<?php

declare(strict_types=1);

namespace GameStore\API\Http;

use GameStore\Application\Exceptions\BusinessRuleException;

final class Request
{
    /** @var array<string,mixed> */
    private array $routeParams = [];

    /** @var array<string,mixed>|null */
    private ?array $jsonBody = null;

    public ?string $authenticatedCustomerId = null;
    public ?string $authenticatedRole = null;

    public function __construct(
        public readonly string $method,
        public readonly string $path,
        /** @var array<string,mixed> */
        public readonly array $query,
        private readonly string $rawBody,
        /** @var array<string,string> */
        private readonly array $headers
    ) {
    }

    public static function capture(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        if ($scriptDir !== '' && str_starts_with($path, $scriptDir)) {
            $path = substr($path, strlen($scriptDir));
        }
        $path = '/' . ltrim($path, '/');
        $path = rtrim($path, '/');
        if ($path === '') {
            $path = '/';
        }

        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace('_', '-', strtolower(substr($key, 5)));
                $headers[$name] = (string) $value;
            }
        }
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $headers['content-type'] = (string) $_SERVER['CONTENT_TYPE'];
        }

        // Alguns setups do Apache (CGI/FastCGI, ou após o rewrite do .htaccess da raiz)
        // não entregam o Authorization em HTTP_AUTHORIZATION. Tentamos as alternativas.
        if (!isset($headers['authorization'])) {
            if (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $headers['authorization'] = (string) $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            } elseif (function_exists('getallheaders')) {
                foreach (getallheaders() as $name => $value) {
                    if (strtolower((string) $name) === 'authorization') {
                        $headers['authorization'] = (string) $value;
                        break;
                    }
                }
            }
        }

        $rawBody = file_get_contents('php://input') ?: '';

        return new self($method, $path, $_GET, $rawBody, $headers);
    }

    public function header(string $name): ?string
    {
        return $this->headers[strtolower($name)] ?? null;
    }

    public function bearerToken(): ?string
    {
        $auth = $this->header('Authorization');
        if ($auth !== null && preg_match('/^Bearer\s+(.+)$/i', $auth, $m)) {
            return $m[1];
        }

        return null;
    }

    /** @return array<string,mixed> */
    public function json(): array
    {
        if ($this->jsonBody !== null) {
            return $this->jsonBody;
        }

        if (trim($this->rawBody) === '') {
            return $this->jsonBody = [];
        }

        $decoded = json_decode($this->rawBody, true);

        if (!is_array($decoded)) {
            throw new BusinessRuleException('Request body must be a valid JSON object.');
        }

        return $this->jsonBody = $decoded;
    }

    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function routeParam(string $name): ?string
    {
        return $this->routeParams[$name] ?? null;
    }

    public function queryParam(string $name, mixed $default = null): mixed
    {
        return $this->query[$name] ?? $default;
    }
}

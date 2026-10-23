<?php

declare(strict_types=1);

namespace GameStore\API;

use GameStore\API\Http\Request;
use GameStore\API\Http\Response;
use GameStore\API\Middleware\AuthMiddleware;
use GameStore\API\Middleware\ExceptionHandler;
use Throwable;

final class Router
{
    /** @var array<int,array{method:string,pattern:string,regex:string,params:string[],handler:callable,auth:bool,roles:string[]}> */
    private array $routes = [];

    public function __construct(private readonly AuthMiddleware $authMiddleware)
    {
    }

    /** @param string[] $roles */
    public function get(string $pattern, callable $handler, bool $auth = false, array $roles = []): void
    {
        $this->add('GET', $pattern, $handler, $auth, $roles);
    }

    /** @param string[] $roles */
    public function post(string $pattern, callable $handler, bool $auth = false, array $roles = []): void
    {
        $this->add('POST', $pattern, $handler, $auth, $roles);
    }

    /** @param string[] $roles */
    public function put(string $pattern, callable $handler, bool $auth = false, array $roles = []): void
    {
        $this->add('PUT', $pattern, $handler, $auth, $roles);
    }

    /** @param string[] $roles */
    public function patch(string $pattern, callable $handler, bool $auth = false, array $roles = []): void
    {
        $this->add('PATCH', $pattern, $handler, $auth, $roles);
    }

    /** @param string[] $roles */
    public function delete(string $pattern, callable $handler, bool $auth = false, array $roles = []): void
    {
        $this->add('DELETE', $pattern, $handler, $auth, $roles);
    }

    /** @param string[] $roles */
    private function add(string $method, string $pattern, callable $handler, bool $auth, array $roles): void
    {
        $paramNames = [];
        $regex = preg_replace_callback('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', function ($m) use (&$paramNames) {
            $paramNames[] = $m[1];
            return '([^/]+)';
        }, $pattern);

        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'regex' => '#^' . $regex . '$#',
            'params' => $paramNames,
            'handler' => $handler,
            'auth' => $auth,
            'roles' => $roles,
        ];
    }

    public function dispatch(Request $request): void
    {
        // CORS é tratado por CorsMiddleware (antes do roteamento); este OPTIONS é só uma rede de segurança.
        if ($request->method === 'OPTIONS') {
            Response::noContent();
            return;
        }

        try {
            $matchedPattern = false;

            foreach ($this->routes as $route) {
                if (!preg_match($route['regex'], $request->path, $matches)) {
                    continue;
                }

                if ($route['method'] !== $request->method) {
                    $matchedPattern = true;
                    continue;
                }

                array_shift($matches);
                $params = array_combine($route['params'], $matches);
                $request->setRouteParams($params ?: []);

                if ($route['auth']) {
                    $this->authMiddleware->handle($request, $route['roles']);
                }

                ($route['handler'])($request);
                return;
            }

            if ($matchedPattern) {
                Response::json(['message' => 'Method not allowed.'], 405);
                return;
            }

            Response::json(['message' => 'Route not found.'], 404);
        } catch (Throwable $e) {
            ExceptionHandler::handle($e);
        }
    }
}

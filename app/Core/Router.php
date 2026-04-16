<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class Router
{
    /**
     * @var array<int, array{method:string, pattern:string, regex:string, params:array<int,string>, handler:callable|array, middleware:array<int,string|array>}>
     */
    private array $routes = [];

    /** @var array<int, string|array{0:string,1?:array<int,mixed>}> */
    private array $groupMiddleware = [];
    private string $groupPrefix = '';

    public function get(string $pattern, callable|array $handler, array $middleware = []): self
    {
        return $this->add('GET', $pattern, $handler, $middleware);
    }

    public function post(string $pattern, callable|array $handler, array $middleware = []): self
    {
        return $this->add('POST', $pattern, $handler, $middleware);
    }

    public function put(string $pattern, callable|array $handler, array $middleware = []): self
    {
        return $this->add('PUT', $pattern, $handler, $middleware);
    }

    public function patch(string $pattern, callable|array $handler, array $middleware = []): self
    {
        return $this->add('PATCH', $pattern, $handler, $middleware);
    }

    public function delete(string $pattern, callable|array $handler, array $middleware = []): self
    {
        return $this->add('DELETE', $pattern, $handler, $middleware);
    }

    /**
     * Agrupa rutas aplicando prefijo y middleware comunes.
     */
    public function group(string $prefix, array $middleware, callable $callback): void
    {
        $previousPrefix = $this->groupPrefix;
        $previousMw     = $this->groupMiddleware;

        $this->groupPrefix    = $previousPrefix . $prefix;
        $this->groupMiddleware = array_merge($previousMw, $middleware);

        $callback($this);

        $this->groupPrefix    = $previousPrefix;
        $this->groupMiddleware = $previousMw;
    }

    private function add(string $method, string $pattern, callable|array $handler, array $middleware = []): self
    {
        $fullPattern = $this->groupPrefix . $pattern;
        $fullPattern = '/' . trim($fullPattern, '/');
        if ($fullPattern === '/') {
            $fullPattern = '/';
        }

        $params = [];
        $regex = preg_replace_callback('/\{([a-zA-Z_][a-zA-Z0-9_]*)(?::([^}]+))?\}/', function ($m) use (&$params) {
            $params[] = $m[1];
            $rule = $m[2] ?? '[^/]+';
            return '(' . $rule . ')';
        }, $fullPattern);
        $regex = '#^' . $regex . '$#';

        $this->routes[] = [
            'method'     => $method,
            'pattern'    => $fullPattern,
            'regex'      => $regex,
            'params'     => $params,
            'handler'    => $handler,
            'middleware' => array_merge($this->groupMiddleware, $middleware),
        ];

        return $this;
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->method();
        $uri    = $request->uri();

        // HEAD → GET
        if ($method === 'HEAD') {
            $method = 'GET';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            if (!preg_match($route['regex'], $uri, $matches)) {
                continue;
            }

            array_shift($matches);
            $params = [];
            foreach ($route['params'] as $i => $name) {
                $params[$name] = $matches[$i] ?? null;
            }
            $request->setParams($params);

            // Pipeline de middleware
            $pipeline = function (Request $req) use ($route) {
                return $this->runHandler($route['handler'], $req);
            };
            foreach (array_reverse($route['middleware']) as $mw) {
                $pipeline = function (Request $req) use ($mw, $pipeline) {
                    return $this->runMiddleware($mw, $req, $pipeline);
                };
            }

            return $pipeline($request);
        }

        return $this->notFound($request);
    }

    private function runHandler(callable|array $handler, Request $request): Response
    {
        if (is_array($handler) && count($handler) === 2 && is_string($handler[0])) {
            [$class, $action] = $handler;
            $instance = new $class();
            $result = $instance->$action($request);
        } elseif (is_callable($handler)) {
            $result = $handler($request);
        } else {
            throw new RuntimeException('Handler inválido.');
        }

        if ($result instanceof Response) {
            return $result;
        }
        if (is_array($result) || is_object($result)) {
            return Response::json($result);
        }
        return Response::html((string) $result);
    }

    /**
     * @param string|array{0:string,1?:array<int,mixed>} $mw
     */
    private function runMiddleware(mixed $mw, Request $request, callable $next): Response
    {
        $args = [];
        if (is_array($mw)) {
            $class = $mw[0];
            $args  = $mw[1] ?? [];
        } else {
            $class = $mw;
        }
        if (!class_exists($class)) {
            throw new RuntimeException("Middleware no existe: $class");
        }
        $instance = new $class();
        return $instance->handle($request, $next, ...$args);
    }

    private function notFound(Request $request): Response
    {
        if ($request->isJson()) {
            return Response::json(['error' => 'Not Found'], 404);
        }
        $view = BASE_PATH . '/app/Views/errors/404.php';
        $html = is_file($view) ? self::render($view) : '<h1>404</h1>';
        return Response::html($html, 404);
    }

    private static function render(string $path): string
    {
        ob_start();
        include $path;
        return (string) ob_get_clean();
    }
}

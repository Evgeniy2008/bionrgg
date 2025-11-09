<?php
declare(strict_types=1);

namespace App\Http;

use App\Exceptions\HttpException;

final class Router
{
    /** @var array<string, list<Route>> */
    private array $routes = [];

    public function add(string $method, string $pattern, callable $handler): void
    {
        $method = strtoupper($method);
        $this->routes[$method][] = new Route($pattern, $handler);
    }

    public function dispatch(Request $request): mixed
    {
        $method = $request->method();
        $path = rtrim($request->path(), '/') ?: '/';

        if ($method === 'OPTIONS') {
            return JsonResponse::success()->send();
        }

        foreach ($this->routes[$method] ?? [] as $route) {
            $match = $route->matches($path);
            if ($match === null) {
                continue;
            }

            return ($route->handler)($request, $match);
        }

        throw HttpException::notFound('Route not found: ' . $method . ' ' . $path);
    }
}

final class Route
{
    private string $regex;
    /** @var list<string> */
    private array $params = [];

    public function __construct(
        private readonly string $pattern,
        public readonly callable $handler
    ) {
        $this->compile();
    }

    private function compile(): void
    {
        $pattern = rtrim($this->pattern, '/') ?: '/';
        $regex = preg_replace_callback('/\{([a-zA-Z0-9_]+)\}/', function (array $matches): string {
            $this->params[] = $matches[1];
            return '([a-zA-Z0-9_\-]+)';
        }, $pattern);

        $this->regex = '#^' . $regex . '$#';
    }

    public function matches(string $path): ?array
    {
        if (!preg_match($this->regex, $path, $matches)) {
            return null;
        }

        array_shift($matches);
        return array_combine($this->params, $matches) ?: [];
    }
}















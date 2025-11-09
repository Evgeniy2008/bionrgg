<?php

namespace App\Core;

use App\Http\Request;
use App\Http\Response;
use Closure;
use RuntimeException;

class Router
{
    /**
     * @var array<string, array<int, array{regex:string, variables:array<int,string>, handler:Closure}>>
     */
    private array $routes = [];

    public function get(string $path, Closure $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, Closure $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, Closure $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    public function delete(string $path, Closure $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    public function dispatch(Request $request, Response $response): void
    {
        $method = $request->method();
        $path = rtrim($request->path(), '/') ?: '/';

        $matched = $this->match($method, $path);
        if ($matched === null) {
            $response->status(404)->json([
                'success' => false,
                'message' => 'Not found'
            ]);
            return;
        }

        [$handler, $variables] = $matched;
        foreach ($variables as $key => $value) {
            $request->setAttribute($key, $value);
        }

        $handler($request, $response);
    }

    private function match(string $method, string $path): ?array
    {
        foreach ($this->routes[$method] ?? [] as $route) {
            if (preg_match($route['regex'], $path, $matches)) {
                $params = [];
                foreach ($route['variables'] as $variable) {
                    if (isset($matches[$variable])) {
                        $params[$variable] = $matches[$variable];
                    }
                }

                return [$route['handler'], $params];
            }
        }

        return null;
    }

    private function addRoute(string $method, string $path, Closure $handler): void
    {
        $normalizedPath = rtrim($path, '/') ?: '/';
        $variables = [];
        $placeholderPrefix = '__PARAM__';

        $pattern = preg_replace_callback('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', function ($matches) use (&$variables, $placeholderPrefix) {
            $variables[] = $matches[1];
            return $placeholderPrefix . count($variables);
        }, $normalizedPath);

        $pattern = preg_quote($pattern, '/');

        foreach ($variables as $index => $name) {
            $placeholder = preg_quote($placeholderPrefix . ($index + 1), '/');
            $pattern = str_replace($placeholder, '(?P<' . $name . '>[^/]+)', $pattern);
        }

        $regex = '/^' . $pattern . '$/u';

        $this->routes[$method][] = [
            'regex' => $regex,
            'variables' => $variables,
            'handler' => $handler,
        ];
    }
}



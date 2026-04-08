<?php

declare(strict_types=1);

namespace App;

final class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function dispatch(string $method, string $path): void
    {
        foreach ($this->routes[$method] ?? [] as $route) {
            $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if (!preg_match($pattern, $path, $matches)) {
                continue;
            }

            $params = array_filter(
                $matches,
                static fn (string|int $key): bool => is_string($key),
                ARRAY_FILTER_USE_KEY
            );

            [$class, $action] = $route['handler'];
            $controller = new $class();
            $controller->{$action}($params);

            return;
        }

        http_response_code(404);
        echo render('layout', [
            'title' => '404 Not Found',
            'content' => '<section class="page-block"><div class="page-header"><h1 class="page-title">ページが見つかりません</h1><p class="lead">指定されたURLを確認してください。</p></div></section>',
        ]);
    }

    private function add(string $method, string $path, array $handler): void
    {
        $this->routes[$method][] = [
            'path' => $path,
            'handler' => $handler,
        ];
    }
}

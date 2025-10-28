<?php
declare(strict_types=1);

namespace App\Kernel;

final class Router
{
    private array $routes = [
        'GET'  => [],
        'POST' => [],
    ];

    public function get(string $pattern, array $handler): void  { $this->routes['GET'][$pattern]  = $handler; }
    public function post(string $pattern, array $handler): void { $this->routes['POST'][$pattern] = $handler; }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';
        foreach ($this->routes[$method] ?? [] as $pattern => $handler) {
            $regex = '#^' . preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $pattern) . '$#';
            if (preg_match($regex, $path, $m)) {
                $params = array_filter($m, fn($k)=>!is_int($k), ARRAY_FILTER_USE_KEY);
                $this->invoke($handler[0], $handler[1], $params);
                return;
            }
        }
        Response::error('Route non trouvée', 404);
    }

    private function invoke(string $class, string $method, array $params): void
    {
        $controller = new $class();
        $ref = new \ReflectionMethod($controller, $method);

        // Valeur par défaut : PUBLIC
        $requiredRole = 'PUBLIC';
        foreach ($ref->getAttributes(IsGranted::class) as $attr) {
            /** @var IsGranted $inst */
            $inst = $attr->newInstance();
            $requiredRole = $inst->role;
        }

        $claims = Auth::ensureGranted($requiredRole);

        $args = [];
        foreach ($ref->getParameters() as $p) {
            $name = $p->getName();
            if ($name === 'claims') { $args[] = $claims; continue; }
            $args[] = $params[$name] ?? null;
        }

        $ref->invokeArgs($controller, $args);
    }
}

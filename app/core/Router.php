<?php
class Router
{
    private $routes = [];

    public function get($path, $action)
    {
        $this->routes['GET'][$this->normalize($path)] = $action;
    }

    public function post($path, $action)
    {
        $this->routes['POST'][$this->normalize($path)] = $action;
    }

    public function dispatch($method, $uri)
    {
        $path = $this->normalize(parse_url($uri, PHP_URL_PATH));
        $basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
        if ($basePath !== '' && strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }
        $path = $this->normalize($path);
        $action = $this->routes[$method][$path] ?? null;
        if (!$action) {
            http_response_code(404);
            echo 'Page not found';
            return;
        }
        [$controller, $method] = $action;
        if (!class_exists($controller) || !method_exists($controller, $method)) {
            http_response_code(500);
            echo 'Controller error';
            return;
        }
        $instance = new $controller();
        $instance->$method();
    }

    private function normalize($path)
    {
        $path = rtrim($path, '/');
        return $path === '' ? '/' : $path;
    }
}

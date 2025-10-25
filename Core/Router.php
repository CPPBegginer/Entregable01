<?php
namespace Core;

use Exception;

class Router {
    protected $routes = [];
    
    public function add($method, $uri, $controller) {
        $this->routes[$method][$uri] = $controller;
    }
    
    public function dispatch($uri) {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($uri, PHP_URL_PATH);
        
        // Buscar ruta exacta primero
        if (isset($this->routes[$method][$uri])) {
            return $this->callAction($this->routes[$method][$uri]);
        }
        
        // Buscar rutas con parámetros
        foreach ($this->routes[$method] as $route => $controller) {
            if (strpos($route, '{') !== false) {
                $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $route);
                $pattern = "#^" . $pattern . "$#";
                
                if (preg_match($pattern, $uri, $matches)) {
                    array_shift($matches); // Remover el match completo
                    return $this->callAction($controller, $matches);
                }
            }
        }
        
        // 404 - No encontrado
        http_response_code(404);
        echo "Página no encontrada - 404";
    }
    
    private function callAction($controllerAction, $params = []) {
        if (empty($controllerAction)) {
        throw new Exception("Controller action no puede estar vacío");
    }
        list($controller, $action) = explode('@', $controllerAction);
        
        $controller = "App\\Controllers\\" . $controller;
        if (!class_exists($controller)) {
        throw new Exception("Controlador no encontrado: $controller");
    }
        $controllerInstance = new $controller();
        if (!method_exists($controllerInstance, $action)) {
        throw new Exception("Método no encontrado: $action en $controller");
    }
        
        return call_user_func_array([$controllerInstance, $action], $params);
    }
}
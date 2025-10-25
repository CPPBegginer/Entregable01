<?php
// Punto de entrada único del sistema
require_once __DIR__ . '/../core/bootstrap.php';

use Core\Router;
use Core\Database;

// Inicializar base de datos
try {
    $db = Database::getInstance();
} catch (Exception $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

// Configurar rutas
$router = new Router();

// Incluir las rutas definidas
require_once __DIR__ . '/../config/routes.php';

// Manejar la solicitud actual
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remover el subdirectorio si existe (para WAMP/XAMPP)
$basePath = '/grifo-app';
if (strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}

// Manejar la raíz
if ($uri === '') {
    $uri = '/';
}

// CAPTURAR LA SALIDA del router
ob_start();
try {
    $router->dispatch($uri);
    $output = ob_get_clean();
    
    // Verificar si hay output
    if (empty(trim($output))) {
        // Si no hay output, mostrar un mensaje de debug
        if ($_SERVER['REQUEST_URI'] === '/admin/dashboard') {
            echo "<h1>⚠️ Debug: Router ejecutado pero sin output</h1>";
        } else {
            echo $output; // Output vacío para otras rutas
        }
    } else {
        echo $output; // Output normal
    }
    
} catch (Exception $e) {
    ob_end_clean(); // Limpiar buffer en caso de error
    http_response_code(500);
    echo "<h1>Error en la aplicación</h1>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
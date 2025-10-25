<?php
// Mostrar todos los errores (solo en desarrollo)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Autocargador de clases personalizado
spl_autoload_register(function ($class) {
    $prefixes = [
        'App\\' => __DIR__ . '/../app/',
        'Core\\' => __DIR__ . '/'
    ];
    
    foreach ($prefixes as $prefix => $base_dir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) === 0) {
            $relative_class = substr($class, $len);
            $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
            
            if (file_exists($file)) {
                require $file;
            } else {
                // Mostrar error de archivo no encontrado
                die("❌ Archivo no encontrado: $file para la clase: $class");
            }
            break;
        }
    }
});

// Autocargador de clases personalizado
spl_autoload_register(function ($class) {
    $prefixes = [
        'App\\' => __DIR__ . '/../app/',
        'Core\\' => __DIR__ . '/'
    ];
    
    foreach ($prefixes as $prefix => $base_dir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) === 0) {
            $relative_class = substr($class, $len);
            $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
            
            if (file_exists($file)) {
                require $file;
            }
            break;
        }
    }
});

// Función helper para vistas
function view($path, $data = []) {
    extract($data);
    require_once __DIR__ . "/../app/Views/{$path}.php";
}

// Función helper para redirecciones
function redirect($url) {
    header("Location: $url");
    exit;
}

// Función para cargar configuración
function config($key = null) {
    static $config = null;
    
    if ($config === null) {
        $config = [
            'db' => [
                'host' => 'localhost',
                'database' => 'grifo_app',
                'username' => 'root',
                'password' => '',
                'charset' => 'utf8mb4'
            ],
            'app' => [
                'name' => 'Sistema de Grifo',
                'env' => 'development',
                'debug' => true
            ]
        ];
        
        // Sobrescribir con .env si existe
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                switch ($key) {
                    case 'DB_HOST': $config['db']['host'] = $value; break;
                    case 'DB_NAME': $config['db']['database'] = $value; break;
                    case 'DB_USER': $config['db']['username'] = $value; break;
                    case 'DB_PASS': $config['db']['password'] = $value; break;
                    case 'DB_CHARSET': $config['db']['charset'] = $value; break;
                    case 'APP_NAME': $config['app']['name'] = $value; break;
                    case 'APP_ENV': $config['app']['env'] = $value; break;
                    case 'APP_DEBUG': $config['app']['debug'] = (bool)$value; break;
                }
            }
        }
    }
    
    if ($key === null) {
        return $config;
    }
    
    $keys = explode('.', $key);
    $value = $config;
    
    foreach ($keys as $k) {
        if (isset($value[$k])) {
            $value = $value[$k];
        } else {
            return null;
        }
    }
    
    return $value;
}

// Inicializar sesión
$session = new Core\Session();

// Cargar mensajes flash para todas las vistas
function old($key, $default = '') {
    $session = new Core\Session();
    $old = $session->getFlash('old') ?? [];
    return $old[$key] ?? $default;
}
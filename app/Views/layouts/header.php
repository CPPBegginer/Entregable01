<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Sistema de Grifo' ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/auth.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <?= $extra_css ?? '' ?>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">⛽ Sistema de Grifo</div>
            <nav class="nav-links">
                <?php 
                use Core\Auth;
                $auth = new Auth();
                $user = $auth->user();
                ?>
                
                <?php if ($user): ?>
                    <span>Hola, <?= htmlspecialchars($user['name']) ?></span>
                    <?php if ($auth->isAttendant()): ?>
                        <a href="/attendant/dashboard" class="btn">Dashboard</a>
                    <?php endif; ?>
                    <?php if ($auth->isAdmin() || $auth->isManager()): ?>
                        <a href="/admin/dashboard" class="btn btn-warning">Admin</a>
                    <?php endif; ?>
                    <a href="/logout" class="btn btn-danger">Cerrar Sesión</a>
                <?php else: ?>
                    <a href="/login" class="btn">Iniciar Sesión</a>
                    <a href="/register" class="btn btn-success">Registrarse</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    
    <main class="container">
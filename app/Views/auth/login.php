<?php
use Core\Auth;
$auth = new Auth();
?>
<div class="auth-container">
    <div class="auth-card">
        <h1 class="auth-title">⛽ Iniciar Sesión</h1>

        <?php if (isset($error) && $error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="/login">
            <div class="form-group">
                <label class="form-label">Email:</label>
                <input type="email" name="email" class="form-control" 
                       value="<?= htmlspecialchars($email ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Contraseña:</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success" style="width: 100%;">Entrar al Sistema</button>
        </form>
        
        <div class="auth-links">
            <p>¿No tienes cuenta? <a href="/register">Regístrate aquí</a></p>
        </div>
    </div>
</div>
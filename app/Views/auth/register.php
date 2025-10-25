<div class="auth-container">
    <div class="auth-card">
        <h1 class="auth-title">⛽ Registrarse</h1>

        <?php if (isset($error) && $error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="/register">
            <div class="form-group">
                <label class="form-label">Nombre completo:</label>
                <input type="text" name="name" class="form-control" 
                       value="<?= htmlspecialchars($old['name'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Email:</label>
                <input type="email" name="email" class="form-control" 
                       value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Contraseña:</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Rol:</label>
                <select name="role" class="form-control">
                    <option value="attendant">Despachador</option>
                    <option value="manager">Gerente</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-success" style="width: 100%;">Registrarse</button>
        </form>
        
        <div class="auth-links">
            <p>¿Ya tienes cuenta? <a href="/login">Inicia sesión aquí</a></p>
        </div>
    </div>
</div>
<?php
namespace App\Controllers;

use App\Models\User;
use Core\Auth;
use Core\Session;

class AuthController {
    protected $auth;
    protected $session;
    protected $userModel;

    public function __construct() {
        $this->auth = new Auth();
        $this->session = new Session();
        $this->userModel = new User();
    }
    
    public function showLogin() {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        // Si ya está logueado, redirigir al dashboard apropiado
        if ($this->auth->check()) {
            $this->redirectToDashboard();
        }
        
        // Usar el layout con CSS separado
        include __DIR__ . '/../Views/layouts/header.php';
        ?>
        <div class="auth-container">
            <div class="auth-card">
                <h1 class="auth-title">⛽ Iniciar Sesión</h1>

                <?php if ($error = $this->session->getFlash('error')): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="/login">
                    <div class="form-group">
                        <label class="form-label">Email:</label>
                        <input type="email" name="email" class="form-control" 
                               value="<?= htmlspecialchars($this->session->getFlash('email') ?? '') ?>" required>
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
        <?php
        include __DIR__ . '/../Views/layouts/footer.php';
    }
    
    public function login() {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // Validaciones básicas
        if (empty($email) || empty($password)) {
            $this->session->setFlash('error', 'Email y contraseña son requeridos');
            $this->session->setFlash('email', $email);
            redirect('/login');
        }

        if ($this->auth->attempt($email, $password)) {
            $this->session->setFlash('success', '¡Bienvenido de vuelta!');
            $this->redirectToDashboard();
        } else {
            $this->session->setFlash('error', 'Credenciales incorrectas');
            $this->session->setFlash('email', $email);
            redirect('/login');
        }
    }
    
    public function showRegister() {
        error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
        if ($this->auth->check()) {
            $this->redirectToDashboard();
        }
        
        include __DIR__ . '/../Views/layouts/header.php';
        ?>
        <div class="auth-container">
            <div class="auth-card">
                <h1 class="auth-title">⛽ Registrarse</h1>

                <?php if ($error = $this->session->getFlash('error')): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="/register">
                    <div class="form-group">
                        <label class="form-label">Nombre completo:</label>
                        <input type="text" name="name" class="form-control" 
                               value="<?= htmlspecialchars($this->session->getFlash('old')['name'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email:</label>
                        <input type="email" name="email" class="form-control" 
                               value="<?= htmlspecialchars($this->session->getFlash('old')['email'] ?? '') ?>" required>
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
        <?php
        include __DIR__ . '/../Views/layouts/footer.php';
    }
    
    public function register() {
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'role' => $_POST['role'] ?? 'attendant'
        ];

        // Validaciones
        $errors = $this->validateRegistration($data);

        if (!empty($errors)) {
            $this->session->setFlash('error', implode('<br>', $errors));
            $this->session->setFlash('old', $data);
            redirect('/register');
        }

        // Registrar usuario
        if ($this->auth->register($data)) {
            $this->session->setFlash('success', '¡Registro exitoso! Bienvenido al sistema');
            $this->redirectToDashboard();
        } else {
            $this->session->setFlash('error', 'Error al crear la cuenta. Intenta nuevamente.');
            redirect('/register');
        }
    }
    
    public function logout() {
        $this->auth->logout();
        $this->session->setFlash('success', 'Sesión cerrada correctamente');
        redirect('/');
    }

    private function validateRegistration($data) {
        $errors = [];

        // Validar nombre
        if (empty($data['name'])) {
            $errors[] = 'El nombre es requerido';
        } elseif (strlen($data['name']) < 2) {
            $errors[] = 'El nombre debe tener al menos 2 caracteres';
        }

        // Validar email
        if (empty($data['email'])) {
            $errors[] = 'El email es requerido';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El email no es válido';
        } elseif ($this->userModel->emailExists($data['email'])) {
            $errors[] = 'Este email ya está registrado';
        }

        // Validar contraseña
        if (empty($data['password'])) {
            $errors[] = 'La contraseña es requerida';
        } elseif (strlen($data['password']) < 6) {
            $errors[] = 'La contraseña debe tener al menos 6 caracteres';
        }

        return $errors;
    }

    private function redirectToDashboard() {
        $user = $this->auth->user();
        
        if ($this->auth->isAdmin()) {
            redirect('/admin/dashboard');
        } elseif ($this->auth->isManager()) {
            redirect('/admin/dashboard');
        } elseif ($this->auth->isAttendant()) {
            redirect('/attendant/dashboard');
        } else {
            redirect('/');
        }
    }
}
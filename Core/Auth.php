<?php
namespace Core;

use App\Models\User;

class Auth {
    private $session;
    private $userModel;

    public function __construct() {
        $this->session = new Session();
        $this->userModel = new User();
    }

    public function attempt($email, $password) {
        $user = $this->userModel->findByEmail($email);
        
        if ($user && password_verify($password, $user['password'])) {
            // Guardar usuario en sesión (sin la contraseña)
            unset($user['password']);
            $this->session->set('user', $user);
            
            // Actualizar último login
            $this->userModel->updateLastLogin($user['id']);
            
            return true;
        }
        
        return false;
    }

    public function user() {
        return $this->session->get('user');
    }

    public function id() {
        $user = $this->user();
        return $user['id'] ?? null;
    }

    public function check() {
        return $this->session->has('user');
    }

    public function isAdmin() {
        $user = $this->user();
        return $user && $user['role'] === 'admin';
    }

    public function isAttendant() {
        $user = $this->user();
        return $user && $user['role'] === 'attendant';
    }

    public function isManager() {
        $user = $this->user();
        return $user && $user['role'] === 'manager';
    }

    public function logout() {
        $this->session->remove('user');
        $this->session->destroy();
    }

    public function register($userData) {
        // Hash de la contraseña
        $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        
        // Crear usuario
        $userId = $this->userModel->create($userData);
        
        if ($userId) {
            // Iniciar sesión automáticamente después del registro
            $user = $this->userModel->find($userId);
            unset($user['password']);
            $this->session->set('user', $user);
            return true;
        }
        
        return false;
    }
}
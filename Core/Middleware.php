<?php
namespace Core;

class Middleware {
    protected $auth;
    protected $session;

    public function __construct() {
        $this->auth = new Auth();
        $this->session = new Session();
    }

    public function auth() {
        if (!$this->auth->check()) {
            $this->session->setFlash('error', 'Debes iniciar sesión para acceder a esta página');
            header('Location: /login');
            exit;
        }
        return true;
    }

    public function guest() {
        if ($this->auth->check()) {
            header('Location: /');
            exit;
        }
        return true;
    }

    public function role($allowedRoles) {
        $this->auth();
        
        $userRole = $this->auth->user()['role'];
        if (!in_array($userRole, (array)$allowedRoles)) {
            $this->session->setFlash('error', 'No tienes permisos para acceder a esta página');
            header('Location: /');
            exit;
        }
        return true;
    }

    public function admin() {
        return $this->role('admin');
    }

    public function attendant() {
        return $this->role(['admin', 'attendant']);
    }

    public function manager() {
        return $this->role(['admin', 'manager']);
    }
}
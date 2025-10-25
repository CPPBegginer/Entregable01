<?php
namespace App\Models;

class User extends Model {
    protected $table = 'users';

    public function findByEmail($email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = ?";
        return $this->db->fetch($sql, [$email]);
    }

    public function getAttendants() {
        $sql = "SELECT * FROM {$this->table} WHERE role = 'attendant' AND is_active = TRUE";
        return $this->db->fetchAll($sql);
    }

    public function getAdmins() {
        $sql = "SELECT * FROM {$this->table} WHERE role = 'admin' AND is_active = TRUE";
        return $this->db->fetchAll($sql);
    }

    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = ?";
        $params = [$email];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result['count'] > 0;
    }

    public function updateLastLogin($userId) {
        $sql = "UPDATE {$this->table} SET updated_at = NOW() WHERE id = ?";
        return $this->db->query($sql, [$userId]);
    }
}
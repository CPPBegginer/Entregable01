<?php
namespace App\Models;

use Core\Database;

abstract class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Métodos CRUD básicos
    public function all() {
        $sql = "SELECT * FROM {$this->table}";
        return $this->db->fetchAll($sql);
    }

    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->fetch($sql, [$id]);
    }

    public function create($data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $this->db->query($sql, $data);
        
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $set = [];
        foreach ($data as $column => $value) {
            $set[] = "{$column} = :{$column}";
        }
        $set = implode(', ', $set);
        
        $data['id'] = $id;
        $sql = "UPDATE {$this->table} SET {$set} WHERE {$this->primaryKey} = :id";
        
        return $this->db->query($sql, $data);
    }

    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->query($sql, [$id]);
    }

    // Métodos de consulta
    public function fetch($sql, $params = []) {
        return $this->db->fetch($sql, $params);
    }

    public function fetchAll($sql, $params = []) {
        return $this->db->fetchAll($sql, $params);
    }

    public function query($sql, $params = []) {
        return $this->db->query($sql, $params);
    }

    public function count($conditions = [], $params = []) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        
        if (!empty($conditions)) {
            $where = implode(' AND ', $conditions);
            $sql .= " WHERE {$where}";
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result['count'] ?? 0;
    }

    // Métodos adicionales útiles
    public function exists($conditions = [], $params = []) {
        return $this->count($conditions, $params) > 0;
    }

    public function getLastInsertId() {
        return $this->db->lastInsertId();
    }

    public function beginTransaction() {
        return $this->db->getConnection()->beginTransaction();
    }

    public function commit() {
        return $this->db->getConnection()->commit();
    }

    public function rollBack() {
        return $this->db->getConnection()->rollBack();
    }
}
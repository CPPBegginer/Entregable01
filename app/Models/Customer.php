<?php
namespace App\Models;

class Customer extends Model {
    protected $table = 'customers';

    public function createCustomer($data) {
        return $this->create($data);
    }

    public function findByNameOrPlate($search) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE name LIKE ? OR vehicle_plate LIKE ? 
                ORDER BY name LIMIT 10";
        $searchTerm = "%{$search}%";
        return $this->fetchAll($sql, [$searchTerm, $searchTerm]);
    }

    public function getRegularCustomers() {
        $sql = "SELECT * FROM {$this->table} WHERE customer_type = 'regular' ORDER BY name";
        return $this->fetchAll($sql);
    }

    public function getCorporateCustomers() {
        $sql = "SELECT * FROM {$this->table} WHERE customer_type = 'corporate' ORDER BY name";
        return $this->fetchAll($sql);
    }
}
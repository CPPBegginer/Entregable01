<?php
namespace App\Models;

class Supplier extends Model {
    protected $table = 'suppliers';

    public function getActiveSuppliers() {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = TRUE ORDER BY name";
        return $this->fetchAll($sql);
    }

    public function getSupplierStats($supplierId) {
        $sql = "SELECT 
                COUNT(*) as total_supplies,
                SUM(liters) as total_liters,
                SUM(total_cost) as total_amount
                FROM fuel_supplies 
                WHERE supplier_id = ?";
        return $this->fetch($sql, [$supplierId]);
    }
}
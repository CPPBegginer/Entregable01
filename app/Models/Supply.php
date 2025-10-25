<?php
namespace App\Models;

class Supply extends Model {
    protected $table = 'fuel_supplies';

    public function createSupply($data) {
        return $this->create($data);
    }

    public function getRecentSupplies($limit = 10) {
        $sql = "SELECT fs.*, s.name as supplier_name, ft.name as fuel_name
                FROM {$this->table} fs
                JOIN suppliers s ON fs.supplier_id = s.id
                JOIN fuel_types ft ON fs.fuel_type_id = ft.id
                ORDER BY fs.supply_date DESC, fs.created_at DESC
                LIMIT ?";
        return $this->fetchAll($sql, [$limit]);
    }

    public function getSuppliesByDateRange($startDate, $endDate) {
        $sql = "SELECT fs.*, s.name as supplier_name, ft.name as fuel_name
                FROM {$this->table} fs
                JOIN suppliers s ON fs.supplier_id = s.id
                JOIN fuel_types ft ON fs.fuel_type_id = ft.id
                WHERE fs.supply_date BETWEEN ? AND ?
                ORDER BY fs.supply_date DESC";
        return $this->fetchAll($sql, [$startDate, $endDate]);
    }
}
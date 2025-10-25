<?php
namespace App\Models;

class Inventory extends Model {
    protected $table = 'fuel_inventory';

    public function getFullInventory() {
        $sql = "SELECT fi.*, ft.name as fuel_name, ft.color_code,
                       (fi.current_liters / fi.max_capacity * 100) as capacity_percentage,
                       CASE 
                           WHEN fi.current_liters <= fi.min_threshold THEN 'low'
                           WHEN fi.current_liters <= (fi.min_threshold * 2) THEN 'warning' 
                           ELSE 'normal'
                       END as stock_status
                FROM {$this->table} fi
                JOIN fuel_types ft ON fi.fuel_type_id = ft.id
                WHERE ft.is_active = TRUE
                ORDER BY ft.name";
        return $this->fetchAll($sql);
    }

    public function updateInventory($fuelTypeId, $litersSold) {
        $sql = "UPDATE {$this->table} SET current_liters = current_liters - ? WHERE fuel_type_id = ?";
        return $this->query($sql, [$litersSold, $fuelTypeId]);
    }

    public function addInventory($fuelTypeId, $liters) {
        $sql = "UPDATE {$this->table} SET current_liters = current_liters + ? WHERE fuel_type_id = ?";
        return $this->query($sql, [$liters, $fuelTypeId]);
    }

    public function getLowStockItems() {
        $sql = "SELECT fi.*, ft.name as fuel_name
                FROM {$this->table} fi
                JOIN fuel_types ft ON fi.fuel_type_id = ft.id
                WHERE fi.current_liters <= fi.min_threshold
                ORDER BY fi.current_liters ASC";
        return $this->fetchAll($sql);
    }

    public function updatePrice($fuelTypeId, $newPrice, $changedBy, $reason = '') {
        // Primero guardar el historial
        $current = $this->fetch("SELECT price_per_liter FROM {$this->table} WHERE fuel_type_id = ?", [$fuelTypeId]);
        
        if ($current) {
            $this->query(
                "INSERT INTO price_history (fuel_type_id, old_price, new_price, changed_by, reason) VALUES (?, ?, ?, ?, ?)",
                [$fuelTypeId, $current['price_per_liter'], $newPrice, $changedBy, $reason]
            );
        }

        // Luego actualizar el precio
        $sql = "UPDATE {$this->table} SET price_per_liter = ? WHERE fuel_type_id = ?";
        return $this->query($sql, [$newPrice, $fuelTypeId]);
    }
}
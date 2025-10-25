<?php
namespace App\Models;

class FuelType extends Model {
    protected $table = 'fuel_types';

    public function getActiveFuelTypes() {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = TRUE ORDER BY name";
        return $this->fetchAll($sql);
    }

    public function getFuelTypeWithPrice($fuelTypeId) {
        $sql = "SELECT ft.*, fi.price_per_liter, fi.current_liters
                FROM {$this->table} ft
                JOIN fuel_inventory fi ON ft.id = fi.fuel_type_id
                WHERE ft.id = ? AND ft.is_active = TRUE";
        return $this->fetch($sql, [$fuelTypeId]);
    }

    public function getAllFuelTypesWithInventory() {
        $sql = "SELECT ft.*, fi.price_per_liter, fi.current_liters, fi.min_threshold
                FROM {$this->table} ft
                JOIN fuel_inventory fi ON ft.id = fi.fuel_type_id
                WHERE ft.is_active = TRUE
                ORDER BY ft.name";
        return $this->fetchAll($sql);
    }
}
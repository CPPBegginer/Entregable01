<?php
namespace App\Models;

class Sale extends Model {
    protected $table = 'fuel_sales';

    public function createSale($data) {
        return $this->create($data);
    }

    public function getTodaySales() {
        $sql = "SELECT fs.*, ft.name as fuel_name, 
                       c.name as customer_name, c.vehicle_plate,
                       u.name as attendant_name
                FROM {$this->table} fs
                JOIN fuel_types ft ON fs.fuel_type_id = ft.id
                LEFT JOIN customers c ON fs.customer_id = c.id
                JOIN users u ON fs.attendant_id = u.id
                WHERE DATE(fs.sale_date) = CURDATE() 
                ORDER BY fs.sale_date DESC";
        return $this->fetchAll($sql);
    }

    public function getSalesByDate($date) {
        $sql = "SELECT fs.*, ft.name as fuel_name, 
                       c.name as customer_name,
                       u.name as attendant_name
                FROM {$this->table} fs
                JOIN fuel_types ft ON fs.fuel_type_id = ft.id
                LEFT JOIN customers c ON fs.customer_id = c.id
                JOIN users u ON fs.attendant_id = u.id
                WHERE DATE(fs.sale_date) = ?
                ORDER BY fs.sale_date DESC";
        return $this->fetchAll($sql, [$date]);
    }

    public function getTotalSalesToday() {
        $sql = "SELECT 
                COUNT(*) as total_sales,
                SUM(liters) as total_liters,
                SUM(total) as total_amount
                FROM {$this->table} 
                WHERE DATE(sale_date) = CURDATE()";
        return $this->fetch($sql);
    }

    public function getSalesStats($startDate, $endDate) {
        $sql = "SELECT 
                ft.name as fuel_name,
                COUNT(*) as sale_count,
                SUM(fs.liters) as total_liters,
                SUM(fs.total) as total_amount
                FROM {$this->table} fs
                JOIN fuel_types ft ON fs.fuel_type_id = ft.id
                WHERE fs.sale_date BETWEEN ? AND ?
                GROUP BY fs.fuel_type_id
                ORDER BY total_amount DESC";
        return $this->fetchAll($sql, [$startDate, $endDate]);
    }
}
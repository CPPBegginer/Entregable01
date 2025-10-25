<?php
namespace App\Models;

class Expense extends Model {
    protected $table = 'expenses';

    public function createExpense($data) {
        return $this->create($data);
    }

    public function getMonthlyExpenses($year, $month) {
        $sql = "SELECT type, SUM(amount) as total
                FROM {$this->table} 
                WHERE YEAR(expense_date) = ? AND MONTH(expense_date) = ?
                GROUP BY type";
        return $this->fetchAll($sql, [$year, $month]);
    }

    public function getRecentExpenses($limit = 10) {
        $sql = "SELECT e.*, u.name as created_by_name
                FROM {$this->table} e
                JOIN users u ON e.created_by = u.id
                ORDER BY e.expense_date DESC, e.created_at DESC
                LIMIT ?";
        return $this->fetchAll($sql, [$limit]);
    }
}
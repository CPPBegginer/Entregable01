<?php
namespace App\Controllers;

use Core\Middleware;
use App\Models\Customer;

class ApiController {
    protected $customerModel;
    
    public function __construct() {
        $this->customerModel = new Customer();
    }
    
    public function searchCustomers() {
        $middleware = new Middleware();
        $middleware->attendant(); // Solo usuarios autenticados
        
        $search = $_GET['q'] ?? '';
        if (strlen($search) < 2) {
            echo json_encode([]);
            return;
        }
        
        $customers = $this->customerModel->findByNameOrPlate($search);
        echo json_encode($customers);
    }
}
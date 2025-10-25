<?php
namespace App\Controllers;

use Core\Auth;
use Core\Session;
use Core\Middleware;
use App\Models\Sale;
use App\Models\Inventory;
use App\Models\Customer;
use App\Models\FuelType;
use Exception;

class AttendantController {
    protected $auth;
    protected $session;
    protected $saleModel;
    protected $inventoryModel;
    protected $customerModel;
    protected $fuelTypeModel;
    
    public function __construct() {
        $this->auth = new Auth();
        $this->session = new Session();
        $this->saleModel = new Sale();
        $this->inventoryModel = new Inventory();
        $this->customerModel = new Customer();
        $this->fuelTypeModel = new FuelType();
    }
    
    public function dashboard() {
        $middleware = new Middleware();
        $middleware->attendant();
        
        $user = $this->auth->user();
        $todaySales = $this->saleModel->getTodaySales();
        $salesStats = $this->saleModel->getTotalSalesToday();
        $inventory = $this->inventoryModel->getFullInventory();
        
        ob_start();
        include __DIR__ . '/../Views/layouts/header.php';
        ?>
        
        <div class="dashboard-header">
            <h1>⛽ Panel del Despachador</h1>
            <p>Bienvenido, <strong><?= htmlspecialchars($user['name']) ?></strong></p>
        </div>

        <?php if ($success = $this->session->getFlash('success')): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <!-- Estadísticas rápidas -->
        <div class="grid grid-3">
            <div class="stat-card">
                <h3>Ventas Hoy</h3>
                <div class="stat-number"><?= $salesStats['total_sales'] ?? 0 ?></div>
                <p>Transacciones</p>
            </div>
            <div class="stat-card">
                <h3>Litros Vendidos</h3>
                <div class="stat-number"><?= number_format($salesStats['total_liters'] ?? 0, 1) ?></div>
                <p>Litros hoy</p>
            </div>
            <div class="stat-card">
                <h3>Total Vendido</h3>
                <div class="stat-number">S/ <?= number_format($salesStats['total_amount'] ?? 0, 2) ?></div>
                <p>Importe total</p>
            </div>
        </div>

        <!-- Acciones rápidas -->
        <div class="quick-actions">
            <a href="/attendant/new-sale" class="action-card">
                <h3>➕ Nueva Venta</h3>
                <p>Registrar nueva venta de combustible</p>
            </a>
            <a href="/attendant/sales" class="action-card">
                <h3>📋 Ver Ventas</h3>
                <p>Historial de ventas del día</p>
            </a>
            <a href="/attendant/customers" class="action-card">
                <h3>👥 Clientes</h3>
                <p>Gestionar clientes</p>
            </a>
        </div>

        <!-- Inventario actual -->
        <div class="card">
            <div class="card-header">
                <h3>📊 Inventario Actual</h3>
            </div>
            <div class="grid grid-2">
                <?php foreach ($inventory as $fuel): 
                    $statusClass = $fuel['stock_status'] == 'low' ? 'inventory-low' : 
                                  ($fuel['stock_status'] == 'warning' ? 'inventory-warning' : '');
                ?>
                    <div class="sale-item <?= $statusClass ?>">
                        <h4><?= htmlspecialchars($fuel['fuel_name']) ?></h4>
                        <p><strong>Stock:</strong> <?= number_format($fuel['current_liters'], 1) ?> L</p>
                        <p><strong>Precio:</strong> S/ <?= number_format($fuel['price_per_liter'], 2) ?> x L</p>
                        <p><strong>Estado:</strong> 
                            <?= $fuel['stock_status'] == 'low' ? '⚠️ Stock Bajo' : 
                                ($fuel['stock_status'] == 'warning' ? '📉 Stock Medio' : '✅ Stock Normal') ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Últimas ventas -->
        <div class="card">
            <div class="card-header">
                <h3>🔄 Últimas Ventas</h3>
            </div>
            <?php if (empty($todaySales)): ?>
                <p>No hay ventas registradas hoy.</p>
            <?php else: ?>
                <div class="grid">
                    <?php foreach (array_slice($todaySales, 0, 5) as $sale): ?>
                        <div class="sale-item">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <strong><?= htmlspecialchars($sale['fuel_name']) ?></strong>
                                    <br>
                                    <small><?= $sale['liters'] ?> L × S/ <?= $sale['price_per_liter'] ?></small>
                                    <?php if ($sale['customer_name']): ?>
                                        <br><small>Cliente: <?= htmlspecialchars($sale['customer_name']) ?></small>
                                    <?php endif; ?>
                                </div>
                                <div style="text-align: right;">
                                    <strong>S/ <?= number_format($sale['total'], 2) ?></strong>
                                    <br>
                                    <small><?= date('H:i', strtotime($sale['sale_date'])) ?></small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($todaySales) > 5): ?>
                    <div style="text-align: center; margin-top: 15px;">
                        <a href="/attendant/sales" class="btn">Ver todas las ventas</a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <?php
        include __DIR__ . '/../Views/layouts/footer.php';
        return ob_get_clean();
    }
    
    public function newSale() {
        $middleware = new Middleware();
        $middleware->attendant();
        
        $fuelTypes = $this->fuelTypeModel->getAllFuelTypesWithInventory();
        $customers = $this->customerModel->getRegularCustomers();
        
        ob_start();
        include __DIR__ . '/../Views/layouts/header.php';
        ?>
        
        <div class="card">
            <div class="card-header">
                <h1>🛒 Nueva Venta</h1>
                <p>Registra una nueva venta de combustible</p>
            </div>

            <a href="/attendant/dashboard" class="btn" style="margin-bottom: 20px;">← Volver al Dashboard</a>

            <?php if ($error = $this->session->getFlash('error')): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="/attendant/process-sale">
                <div class="grid grid-2">
                    <!-- Columna 1: Información de la venta -->
                    <div>
                        <h3>Información del Combustible</h3>
                        
                        <div class="form-group">
                            <label class="form-label">Tipo de Combustible *</label>
                            <select name="fuel_type_id" id="fuel_type" class="form-control" required>
                                <option value="">Seleccionar combustible...</option>
                                <?php foreach ($fuelTypes as $fuel): ?>
                                    <option value="<?= $fuel['id'] ?>" 
                                            data-price="<?= $fuel['price_per_liter'] ?>"
                                            data-stock="<?= $fuel['current_liters'] ?>">
                                        <?= htmlspecialchars($fuel['name']) ?> - 
                                        S/ <?= number_format($fuel['price_per_liter'], 2) ?> x L
                                        (Stock: <?= number_format($fuel['current_liters'], 1) ?> L)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Litros *</label>
                            <input type="number" name="liters" id="liters" class="form-control" 
                                   step="0.1" min="0.1" required oninput="calculateTotal()">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Precio por litro</label>
                            <input type="number" name="price_per_liter" id="price_per_liter" 
                                   class="form-control" step="0.01" readonly>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Total a pagar</label>
                            <input type="number" name="total" id="total" class="form-control" step="0.01" readonly>
                            <div id="total_display" class="alert alert-success" style="display: none; margin-top: 10px;">
                                <strong>Total: S/ <span id="total_text">0.00</span></strong>
                            </div>
                        </div>
                    </div>

                    <!-- Columna 2: Información del cliente y pago -->
                    <div>
                        <h3>Información del Cliente y Pago</h3>
                        
                        <div class="form-group">
                            <label class="form-label">Cliente (opcional)</label>
                            <select name="customer_id" class="form-control">
                                <option value="">Cliente ocasional</option>
                                <?php foreach ($customers as $customer): ?>
                                    <option value="<?= $customer['id'] ?>">
                                        <?= htmlspecialchars($customer['name']) ?>
                                        <?php if ($customer['vehicle_plate']): ?>
                                            - <?= htmlspecialchars($customer['vehicle_plate']) ?>
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small>O <a href="/attendant/customers/create" target="_blank">registrar nuevo cliente</a></small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Método de Pago *</label>
                            <select name="payment_method" class="form-control" required>
                                <option value="cash">Efectivo</option>
                                <option value="card">Tarjeta</option>
                                <option value="transfer">Transferencia</option>
                                <option value="credit">Crédito</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Tipo de Venta</label>
                            <select name="sale_type" class="form-control">
                                <option value="retail">Al por menor</option>
                                <option value="wholesale">Al por mayor</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Notas (opcional)</label>
                            <textarea name="notes" class="form-control" rows="3" 
                                      placeholder="Observaciones adicionales..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-group" style="text-align: center; margin-top: 30px;">
                    <button type="submit" class="btn btn-success" style="padding: 15px 30px; font-size: 1.1rem;">
                        ✅ Procesar Venta
                    </button>
                    <a href="/attendant/dashboard" class="btn" style="margin-left: 10px;">Cancelar</a>
                </div>
            </form>
        </div>

        <script>
            function calculateTotal() {
                const fuelSelect = document.getElementById('fuel_type');
                const litersInput = document.getElementById('liters');
                const priceInput = document.getElementById('price_per_liter');
                const totalInput = document.getElementById('total');
                const totalDisplay = document.getElementById('total_display');
                const totalText = document.getElementById('total_text');
                
                if (fuelSelect.value && litersInput.value) {
                    const price = parseFloat(fuelSelect.selectedOptions[0].getAttribute('data-price'));
                    const liters = parseFloat(litersInput.value);
                    const stock = parseFloat(fuelSelect.selectedOptions[0].getAttribute('data-stock'));
                    const total = price * liters;
                    
                    // Validar stock
                    if (liters > stock) {
                        alert('⚠️ Stock insuficiente. Stock disponible: ' + stock + ' L');
                        litersInput.value = stock;
                        return;
                    }
                    
                    priceInput.value = price.toFixed(2);
                    totalInput.value = total.toFixed(2);
                    totalText.textContent = total.toFixed(2);
                    totalDisplay.style.display = 'block';
                } else {
                    totalDisplay.style.display = 'none';
                }
            }
            
            document.getElementById('fuel_type').addEventListener('change', calculateTotal);
            document.getElementById('liters').addEventListener('input', calculateTotal);
        </script>

        <?php
        include __DIR__ . '/../Views/layouts/footer.php';
        return ob_get_clean();
    }
    
    public function processSale() {
        $middleware = new Middleware();
        $middleware->attendant();
        
        $user = $this->auth->user();
        
        // Recoger datos del formulario
        $saleData = [
            'customer_id' => !empty($_POST['customer_id']) ? (int)$_POST['customer_id'] : null,
            'fuel_type_id' => (int)$_POST['fuel_type_id'],
            'liters' => (float)$_POST['liters'],
            'price_per_liter' => (float)$_POST['price_per_liter'],
            'total' => (float)$_POST['total'],
            'payment_method' => $_POST['payment_method'],
            'sale_type' => $_POST['sale_type'],
            'attendant_id' => $user['id'],
            'notes' => trim($_POST['notes'] ?? '')
        ];
        
        // Validaciones
        if ($saleData['liters'] <= 0 || $saleData['fuel_type_id'] <= 0) {
            $this->session->setFlash('error', 'Datos inválidos en el formulario');
            redirect('/attendant/new-sale');
        }
        
        try {
            // Verificar stock antes de procesar
            $inventory = $this->inventoryModel->find($saleData['fuel_type_id']);
            if (!$inventory || $inventory['current_liters'] < $saleData['liters']) {
                $this->session->setFlash('error', 'Stock insuficiente para completar la venta');
                redirect('/attendant/new-sale');
            }
            
            // Procesar la venta
            $saleId = $this->saleModel->createSale($saleData);
            
            // Actualizar inventario
            $this->inventoryModel->updateInventory($saleData['fuel_type_id'], $saleData['liters']);
            
            $this->session->setFlash('success', '✅ Venta registrada exitosamente! ID: #' . $saleId);
            redirect('/attendant/dashboard');
            
        } catch (Exception $e) {
            $this->session->setFlash('error', 'Error al registrar venta: ' . $e->getMessage());
            redirect('/attendant/new-sale');
        }
    }
    
    public function sales() {
        $middleware = new Middleware();
        $middleware->attendant();
        
        $date = $_GET['date'] ?? date('Y-m-d');
        $sales = $this->saleModel->getSalesByDate($date);
        $salesStats = $this->saleModel->getTotalSalesToday();
        
        ob_start();
        include __DIR__ . '/../Views/layouts/header.php';
        ?>
        
        <div class="card">
            <div class="card-header">
                <h1>📋 Historial de Ventas</h1>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <p>Ventas del <?= date('d/m/Y', strtotime($date)) ?></p>
                    <a href="/attendant/new-sale" class="btn btn-success">➕ Nueva Venta</a>
                </div>
            </div>

            <a href="/attendant/dashboard" class="btn" style="margin-bottom: 20px;">← Volver al Dashboard</a>

            <!-- Filtro de fecha -->
            <form method="GET" action="/attendant/sales" style="margin-bottom: 20px;">
                <div class="form-group">
                    <label class="form-label">Filtrar por fecha:</label>
                    <input type="date" name="date" value="<?= $date ?>" class="form-control" 
                           style="width: auto; display: inline-block;">
                    <button type="submit" class="btn">Filtrar</button>
                    <a href="/attendant/sales" class="btn">Hoy</a>
                </div>
            </form>

            <!-- Resumen del día -->
            <div class="grid grid-3" style="margin-bottom: 20px;">
                <div class="stat-card">
                    <h3>Total Ventas</h3>
                    <div class="stat-number"><?= count($sales) ?></div>
                    <p>Transacciones</p>
                </div>
                <div class="stat-card">
                    <h3>Total Litros</h3>
                    <div class="stat-number"><?= number_format($salesStats['total_liters'] ?? 0, 1) ?></div>
                    <p>Litros vendidos</p>
                </div>
                <div class="stat-card">
                    <h3>Total Importe</h3>
                    <div class="stat-number">S/ <?= number_format($salesStats['total_amount'] ?? 0, 2) ?></div>
                    <p>Ventas totales</p>
                </div>
            </div>

            <!-- Lista de ventas -->
            <?php if (empty($sales)): ?>
                <div class="alert alert-warning">
                    <h3>No hay ventas registradas para esta fecha</h3>
                    <p>Comienza registrando tu primera venta del día.</p>
                    <a href="/attendant/new-sale" class="btn btn-success">Registrar Primera Venta</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Hora</th>
                                <th>Combustible</th>
                                <th>Cliente</th>
                                <th>Litros</th>
                                <th>Precio Unit.</th>
                                <th>Total</th>
                                <th>Pago</th>
                                <th>Atendió</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sales as $sale): ?>
                                <tr>
                                    <td><?= date('H:i', strtotime($sale['sale_date'])) ?></td>
                                    <td><?= htmlspecialchars($sale['fuel_name']) ?></td>
                                    <td>
                                        <?= $sale['customer_name'] ? 
                                            htmlspecialchars($sale['customer_name']) : 
                                            '<em>Ocasional</em>' ?>
                                        <?php if ($sale['vehicle_plate']): ?>
                                            <br><small><?= $sale['vehicle_plate'] ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= number_format($sale['liters'], 1) ?> L</td>
                                    <td>S/ <?= number_format($sale['price_per_liter'], 2) ?></td>
                                    <td><strong>S/ <?= number_format($sale['total'], 2) ?></strong></td>
                                    <td>
                                        <?php 
                                        $paymentMethods = [
                                            'cash' => 'Efectivo',
                                            'card' => 'Tarjeta', 
                                            'transfer' => 'Transferencia',
                                            'credit' => 'Crédito'
                                        ];
                                        echo $paymentMethods[$sale['payment_method']] ?? $sale['payment_method'];
                                        ?>
                                    </td>
                                    <td><?= htmlspecialchars($sale['attendant_name']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <?php
        include __DIR__ . '/../Views/layouts/footer.php';
        return ob_get_clean();
    }

    // Métodos para clientes (simplificados por ahora)
    public function customers() {
        $middleware = new Middleware();
        $middleware->attendant();
        
        $customers = $this->customerModel->all();
        
        ob_start();
        include __DIR__ . '/../Views/layouts/header.php';
        ?>
        
        <div class="card">
            <div class="card-header">
                <h1>👥 Gestión de Clientes</h1>
                <a href="/attendant/dashboard" class="btn">← Volver</a>
                <a href="/attendant/customers/create" class="btn btn-success" style="float: right;">➕ Nuevo Cliente</a>
            </div>

            <?php if (empty($customers)): ?>
                <div class="alert alert-warning">
                    <h3>No hay clientes registrados</h3>
                    <p>Comienza registrando tu primer cliente.</p>
                    <a href="/attendant/customers/create" class="btn btn-success">Registrar Primer Cliente</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Contacto</th>
                                <th>Vehículo</th>
                                <th>Tipo</th>
                                <th>Fecha Registro</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers as $customer): ?>
                                <tr>
                                    <td><?= htmlspecialchars($customer['name']) ?></td>
                                    <td>
                                        <?php if ($customer['email']): ?>
                                            <?= htmlspecialchars($customer['email']) ?><br>
                                        <?php endif; ?>
                                        <?php if ($customer['phone']): ?>
                                            <small><?= htmlspecialchars($customer['phone']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($customer['vehicle_plate']): ?>
                                            <?= htmlspecialchars($customer['vehicle_plate']) ?>
                                            <?php if ($customer['vehicle_brand']): ?>
                                                <br><small><?= $customer['vehicle_brand'] ?> <?= $customer['vehicle_model'] ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <em>No especificado</em>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $types = [
                                            'regular' => 'Regular',
                                            'corporate' => 'Corporativo',
                                            'wholesale' => 'Mayorista'
                                        ];
                                        echo $types[$customer['customer_type']] ?? $customer['customer_type'];
                                        ?>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($customer['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <?php
        include __DIR__ . '/../Views/layouts/footer.php';
        return ob_get_clean();
    }

    public function createCustomer() {
        $middleware = new Middleware();
        $middleware->attendant();
        
        ob_start();
        include __DIR__ . '/../Views/layouts/header.php';
        ?>
        
        <div class="card">
            <div class="card-header">
                <h1>👤 Registrar Nuevo Cliente</h1>
                <a href="/attendant/customers" class="btn">← Volver a Clientes</a>
            </div>

            <form method="POST" action="/attendant/store-customer">
                <div class="grid grid-2">
                    <div>
                        <h3>Información Personal</h3>
                        
                        <div class="form-group">
                            <label class="form-label">Nombre completo *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="phone" class="form-control">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Tipo de Cliente</label>
                            <select name="customer_type" class="form-control">
                                <option value="regular">Regular</option>
                                <option value="corporate">Corporativo</option>
                                <option value="wholesale">Mayorista</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <h3>Información del Vehículo (opcional)</h3>
                        
                        <div class="form-group">
                            <label class="form-label">Placa</label>
                            <input type="text" name="vehicle_plate" class="form-control">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Marca</label>
                            <input type="text" name="vehicle_brand" class="form-control">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Modelo</label>
                            <input type="text" name="vehicle_model" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="form-group" style="text-align: center; margin-top: 30px;">
                    <button type="submit" class="btn btn-success">💾 Guardar Cliente</button>
                    <a href="/attendant/customers" class="btn">Cancelar</a>
                </div>
            </form>
        </div>

        <?php
        include __DIR__ . '/../Views/layouts/footer.php';
        return ob_get_clean();
    }

    public function storeCustomer() {
        $middleware = new Middleware();
        $middleware->attendant();
        
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'vehicle_plate' => trim($_POST['vehicle_plate'] ?? ''),
            'vehicle_brand' => trim($_POST['vehicle_brand'] ?? ''),
            'vehicle_model' => trim($_POST['vehicle_model'] ?? ''),
            'customer_type' => $_POST['customer_type'] ?? 'regular'
        ];

        if (empty($data['name'])) {
            $this->session->setFlash('error', 'El nombre del cliente es requerido');
            redirect('/attendant/customers/create');
        }

        try {
            $customerId = $this->customerModel->createCustomer($data);
            $this->session->setFlash('success', '✅ Cliente registrado exitosamente!');
            redirect('/attendant/customers');
        } catch (Exception $e) {
            $this->session->setFlash('error', 'Error al registrar cliente: ' . $e->getMessage());
            redirect('/attendant/customers/create');
        }
    }
}
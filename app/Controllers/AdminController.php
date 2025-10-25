<?php
namespace App\Controllers;

use Core\Auth;
use Core\Session;
use Core\Middleware;
use App\Models\User;
use App\Models\Sale;
use App\Models\Inventory;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Supply;
use App\Models\Expense;
use App\Models\FuelType;
use Exception;

class AdminController {
    protected $auth;
    protected $session;
    protected $userModel;
    protected $saleModel;
    protected $inventoryModel;
    protected $customerModel;
    protected $supplierModel;
    protected $supplyModel;
    protected $expenseModel;
    protected $fuelTypeModel;
    
    public function __construct() {
        $this->auth = new Auth();
        $this->session = new Session();
        $this->userModel = new User();
        $this->saleModel = new Sale();
        $this->inventoryModel = new Inventory();
        $this->customerModel = new Customer();
        $this->supplierModel = new Supplier();
        $this->supplyModel = new Supply();
        $this->expenseModel = new Expense();
        $this->fuelTypeModel = new FuelType();
    }
    
    public function dashboard() {
        $middleware = new Middleware();
        $middleware->admin();
        
        $user = $this->auth->user();
        
        // Estadísticas generales
        $totalUsers = $this->userModel->count();
        $totalCustomers = $this->customerModel->count();
        $totalSuppliers = $this->supplierModel->count();
        
        // Ventas de hoy
        $todaySales = $this->saleModel->getTotalSalesToday();
        $recentSales = $this->saleModel->getTodaySales();
        
        // Inventario
        $inventory = $this->inventoryModel->getFullInventory();
        $lowStock = $this->inventoryModel->getLowStockItems();
        
        // Suministros recientes
        $recentSupplies = $this->supplyModel->getRecentSupplies(5);
        
        include __DIR__ . '/../Views/layouts/header.php';
        ?>
        
        <div class="dashboard-header">
            <h1>⚙️ Panel de Administración</h1>
            <p>Bienvenido, <strong><?= htmlspecialchars($user['name']) ?></strong> 👑</p>
        </div>

        <?php if ($success = $this->session->getFlash('success')): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <!-- Alertas importantes -->
        <?php if (!empty($lowStock)): ?>
            <div class="alert alert-warning">
                <h3>⚠️ Alertas de Stock Bajo</h3>
                <ul>
                    <?php foreach ($lowStock as $item): ?>
                        <li>
                            <strong><?= htmlspecialchars($item['fuel_name']) ?>:</strong> 
                            <?= number_format($item['current_liters'], 1) ?> L restantes
                            (Mínimo: <?= number_format($item['min_threshold'], 1) ?> L)
                        </li>
                    <?php endforeach; ?>
                </ul>
                <a href="/admin/inventory" class="btn btn-warning">Gestionar Inventario</a>
            </div>
        <?php endif; ?>

        <!-- Estadísticas principales -->
        <div class="grid grid-4">
            <div class="stat-card">
                <h3>👥 Usuarios</h3>
                <div class="stat-number"><?= $totalUsers ?></div>
                <p>Registrados</p>
            </div>
            <div class="stat-card">
                <h3>🚗 Clientes</h3>
                <div class="stat-number"><?= $totalCustomers ?></div>
                <p>En sistema</p>
            </div>
            <div class="stat-card">
                <h3>📦 Proveedores</h3>
                <div class="stat-number"><?= $totalSuppliers ?></div>
                <p>Activos</p>
            </div>
            <div class="stat-card">
                <h3>💰 Ventas Hoy</h3>
                <div class="stat-number">S/ <?= number_format($todaySales['total_amount'] ?? 0, 2) ?></div>
                <p>Ingresos</p>
            </div>
        </div>

        <!-- Acciones rápidas -->
        <div class="quick-actions">
            <a href="/admin/users" class="action-card">
                <h3>👥 Usuarios</h3>
                <p>Gestionar usuarios del sistema</p>
            </a>
            <a href="/admin/inventory" class="action-card">
                <h3>📊 Inventario</h3>
                <p>Control de combustibles</p>
            </a>
            <a href="/admin/sales" class="action-card">
                <h3>📋 Ventas</h3>
                <p>Reportes de ventas</p>
            </a>
            <a href="/admin/supplies" class="action-card">
                <h3>🚚 Suministros</h3>
                <p>Gestionar suministros</p>
            </a>
            <a href="/admin/customers" class="action-card">
                <h3>🚗 Clientes</h3>
                <p>Base de clientes</p>
            </a>
            <a href="/admin/suppliers" class="action-card">
                <h3>🏢 Proveedores</h3>
                <p>Gestionar proveedores</p>
            </a>
        </div>

        <div class="grid grid-2">
            <!-- Inventario actual -->
            <div class="card">
                <div class="card-header">
                    <h3>⛽ Inventario Actual</h3>
                </div>
                <div class="grid">
                    <?php foreach ($inventory as $fuel): 
                        $statusClass = $fuel['stock_status'] == 'low' ? 'inventory-low' : 
                                      ($fuel['stock_status'] == 'warning' ? 'inventory-warning' : '');
                    ?>
                        <div class="sale-item <?= $statusClass ?>">
                            <h4><?= htmlspecialchars($fuel['fuel_name']) ?></h4>
                            <p><strong>Stock:</strong> <?= number_format($fuel['current_liters'], 1) ?> L</p>
                            <p><strong>Precio:</strong> S/ <?= number_format($fuel['price_per_liter'], 2) ?></p>
                            <p><strong>Capacidad:</strong> <?= number_format($fuel['capacity_percentage'], 1) ?>%</p>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div style="text-align: center; margin-top: 15px;">
                    <a href="/admin/inventory" class="btn">Gestionar Inventario</a>
                </div>
            </div>

            <!-- Últimas ventas -->
            <div class="card">
                <div class="card-header">
                    <h3>🔄 Últimas Ventas</h3>
                </div>
                <?php if (empty($recentSales)): ?>
                    <p>No hay ventas hoy.</p>
                <?php else: ?>
                    <div class="grid">
                        <?php foreach (array_slice($recentSales, 0, 5) as $sale): ?>
                            <div class="sale-item">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <strong><?= htmlspecialchars($sale['fuel_name']) ?></strong>
                                        <br>
                                        <small><?= $sale['liters'] ?> L</small>
                                        <?php if ($sale['customer_name']): ?>
                                            <br><small>Cliente: <?= htmlspecialchars($sale['customer_name']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div style="text-align: right;">
                                        <strong>S/ <?= number_format($sale['total'], 2) ?></strong>
                                        <br>
                                        <small><?= date('H:i', strtotime($sale['sale_date'])) ?></small>
                                        <br>
                                        <small><?= htmlspecialchars($sale['attendant_name']) ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div style="text-align: center; margin-top: 15px;">
                        <a href="/admin/sales" class="btn">Ver todas las ventas</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Suministros recientes -->
        <?php if (!empty($recentSupplies)): ?>
            <div class="card">
                <div class="card-header">
                    <h3>🚚 Suministros Recientes</h3>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Proveedor</th>
                                <th>Combustible</th>
                                <th>Litros</th>
                                <th>Costo Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentSupplies as $supply): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($supply['supply_date'])) ?></td>
                                    <td><?= htmlspecialchars($supply['supplier_name']) ?></td>
                                    <td><?= htmlspecialchars($supply['fuel_name']) ?></td>
                                    <td><?= number_format($supply['liters'], 1) ?> L</td>
                                    <td>S/ <?= number_format($supply['total_cost'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div style="text-align: center; margin-top: 15px;">
                    <a href="/admin/supplies" class="btn">Ver todos los suministros</a>
                </div>
            </div>
        <?php endif; ?>

        <?php
        include __DIR__ . '/../Views/layouts/footer.php';
        
    }

    // Gestión de usuarios
    public function users() {
        $middleware = new Middleware();
        $middleware->admin();
        
        $users = $this->userModel->all();
        
        include __DIR__ . '/../Views/layouts/header.php';
        ?>
        
        <div class="card">
            <div class="card-header">
                <h1>👥 Gestión de Usuarios</h1>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <p>Total usuarios: <?= count($users) ?></p>
                    <a href="/admin/dashboard" class="btn">← Volver al Panel</a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Fecha Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $user['id'] ?></td>
                                <td><?= htmlspecialchars($user['name']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <span style="padding: 4px 8px; border-radius: 12px; font-size: 0.8em; 
                                          background: <?= $user['role'] == 'admin' ? '#dc3545' : ($user['role'] == 'manager' ? '#ffc107' : '#007bff') ?>; 
                                          color: white;">
                                        <?= $user['role'] ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="color: <?= $user['is_active'] ? '#28a745' : '#dc3545' ?>; font-weight: bold;">
                                        <?= $user['is_active'] ? 'Activo' : 'Inactivo' ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <button class="btn" style="padding: 5px 10px; font-size: 0.8rem;">Editar</button>
                                    <?php if ($user['id'] != $this->auth->id()): ?>
                                        <button class="btn btn-danger" style="padding: 5px 10px; font-size: 0.8rem;">
                                            Desactivar
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php
        include __DIR__ . '/../Views/layouts/footer.php';
    }

    // Gestión de inventario
    public function inventory() {
        $middleware = new Middleware();
        $middleware->admin();
        
        $inventory = $this->inventoryModel->getFullInventory();
        
        include __DIR__ . '/../Views/layouts/header.php';
        ?>
        
        <div class="card">
            <div class="card-header">
                <h1>📊 Gestión de Inventario</h1>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <p>Control de combustibles y precios</p>
                    <a href="/admin/dashboard" class="btn">← Volver al Panel</a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Combustible</th>
                            <th>Stock Actual</th>
                            <th>Capacidad</th>
                            <th>Mínimo</th>
                            <th>Precio x Litro</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inventory as $fuel): 
                            $statusText = $fuel['stock_status'] == 'low' ? 'Stock Bajo ⚠️' : 
                                         ($fuel['stock_status'] == 'warning' ? 'Stock Medio 📉' : 'Stock Normal ✅');
                            $statusColor = $fuel['stock_status'] == 'low' ? '#dc3545' : 
                                          ($fuel['stock_status'] == 'warning' ? '#ffc107' : '#28a745');
                        ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($fuel['fuel_name']) ?></strong>
                                </td>
                                <td>
                                    <strong><?= number_format($fuel['current_liters'], 1) ?> L</strong>
                                </td>
                                <td><?= number_format($fuel['max_capacity'], 1) ?> L</td>
                                <td><?= number_format($fuel['min_threshold'], 1) ?> L</td>
                                <td>
                                    <strong>S/ <?= number_format($fuel['price_per_liter'], 2) ?></strong>
                                </td>
                                <td>
                                    <span style="color: <?= $statusColor ?>; font-weight: bold;">
                                        <?= $statusText ?>
                                    </span>
                                    <br>
                                    <small><?= number_format($fuel['capacity_percentage'], 1) ?>% de capacidad</small>
                                </td>
                                <td>
                                    <button onclick="editPrice(<?= $fuel['fuel_type_id'] ?>, '<?= htmlspecialchars($fuel['fuel_name']) ?>', <?= $fuel['price_per_liter'] ?>)" 
                                            class="btn" style="padding: 5px 10px; font-size: 0.8rem;">
                                        Editar Precio
                                    </button>
                                    <a href="/admin/supplies?fuel_type=<?= $fuel['fuel_type_id'] ?>" 
                                       class="btn btn-success" style="padding: 5px 10px; font-size: 0.8rem;">
                                        Agregar Stock
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal para editar precio -->
        <div id="priceModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
            <div style="background: white; padding: 30px; border-radius: 8px; width: 400px; max-width: 90%;">
                <h3 id="modalTitle">Editar Precio</h3>
                <form id="priceForm" method="POST" action="/admin/update-price">
                    <input type="hidden" name="fuel_type_id" id="modalFuelId">
                    
                    <div class="form-group">
                        <label class="form-label">Nuevo Precio por Litro (S/)</label>
                        <input type="number" name="new_price" id="modalNewPrice" class="form-control" step="0.01" min="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Razón del cambio (opcional)</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Ej: Ajuste por costo de importación..."></textarea>
                    </div>
                    
                    <div style="display: flex; gap: 10px; margin-top: 20px;">
                        <button type="submit" class="btn btn-success">💾 Actualizar Precio</button>
                        <button type="button" onclick="closeModal()" class="btn">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function editPrice(fuelId, fuelName, currentPrice) {
                document.getElementById('modalTitle').textContent = 'Editar Precio: ' + fuelName;
                document.getElementById('modalFuelId').value = fuelId;
                document.getElementById('modalNewPrice').value = currentPrice;
                document.getElementById('priceModal').style.display = 'flex';
            }

            function closeModal() {
                document.getElementById('priceModal').style.display = 'none';
            }

            // Cerrar modal al hacer clic fuera
            document.getElementById('priceModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeModal();
                }
            });
        </script>

        <?php
        include __DIR__ . '/../Views/layouts/footer.php';
    }

    public function updatePrice() {
        $middleware = new Middleware();
        $middleware->admin();
        
        $fuelTypeId = (int)$_POST['fuel_type_id'];
        $newPrice = (float)$_POST['new_price'];
        $reason = trim($_POST['reason'] ?? '');
        $user = $this->auth->user();

        if ($fuelTypeId <= 0 || $newPrice <= 0) {
            $this->session->setFlash('error', 'Datos inválidos');
            redirect('/admin/inventory');
        }

        try {
            $this->inventoryModel->updatePrice($fuelTypeId, $newPrice, $user['id'], $reason);
            $this->session->setFlash('success', '✅ Precio actualizado exitosamente!');
        } catch (Exception $e) {
            $this->session->setFlash('error', 'Error al actualizar precio: ' . $e->getMessage());
        }

        redirect('/admin/inventory');
    }

    // Gestión de ventas
    public function sales() {
        $middleware = new Middleware();
        $middleware->admin();
        
        $date = $_GET['date'] ?? date('Y-m-d');
        $sales = $this->saleModel->getSalesByDate($date);
        
        include __DIR__ . '/../Views/layouts/header.php';
        ?>
        
        <div class="card">
            <div class="card-header">
                <h1>📋 Reporte de Ventas</h1>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <p>Ventas del <?= date('d/m/Y', strtotime($date)) ?></p>
                    <a href="/admin/dashboard" class="btn">← Volver al Panel</a>
                </div>
            </div>

            <!-- Filtro de fecha -->
            <form method="GET" action="/admin/sales" style="margin-bottom: 20px;">
                <div class="form-group">
                    <label class="form-label">Filtrar por fecha:</label>
                    <input type="date" name="date" value="<?= $date ?>" class="form-control" 
                           style="width: auto; display: inline-block;">
                    <button type="submit" class="btn">Filtrar</button>
                    <a href="/admin/sales" class="btn">Hoy</a>
                </div>
            </form>

            <?php if (empty($sales)): ?>
                <div class="alert alert-warning">
                    <h3>No hay ventas registradas para esta fecha</h3>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Fecha/Hora</th>
                                <th>Combustible</th>
                                <th>Cliente</th>
                                <th>Litros</th>
                                <th>Precio Unit.</th>
                                <th>Total</th>
                                <th>Pago</th>
                                <th>Despachador</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalLiters = 0;
                            $totalAmount = 0;
                            foreach ($sales as $sale): 
                                $totalLiters += $sale['liters'];
                                $totalAmount += $sale['total'];
                            ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($sale['sale_date'])) ?></td>
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
                        <tfoot style="background: #f8f9fa; font-weight: bold;">
                            <tr>
                                <td colspan="3">TOTALES</td>
                                <td><?= number_format($totalLiters, 1) ?> L</td>
                                <td></td>
                                <td>S/ <?= number_format($totalAmount, 2) ?></td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <!-- Resumen -->
                <div class="grid grid-3" style="margin-top: 20px;">
                    <div class="stat-card">
                        <h3>Total Ventas</h3>
                        <div class="stat-number"><?= count($sales) ?></div>
                        <p>Transacciones</p>
                    </div>
                    <div class="stat-card">
                        <h3>Total Litros</h3>
                        <div class="stat-number"><?= number_format($totalLiters, 1) ?></div>
                        <p>Litros vendidos</p>
                    </div>
                    <div class="stat-card">
                        <h3>Total Importe</h3>
                        <div class="stat-number">S/ <?= number_format($totalAmount, 2) ?></div>
                        <p>Ventas totales</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php
        include __DIR__ . '/../Views/layouts/footer.php';
    }

    // Métodos simplificados para las otras secciones (por tiempo)
    public function customers() {
        $middleware = new Middleware();
        $middleware->admin();
        
        $customers = $this->customerModel->all();
        
        include __DIR__ . '/../Views/layouts/header.php';
        ?>
        
        <div class="card">
            <div class="card-header">
                <h1>🚗 Gestión de Clientes</h1>
                <a href="/admin/dashboard" class="btn">← Volver al Panel</a>
            </div>

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
        </div>

        <?php
        include __DIR__ . '/../Views/layouts/footer.php';
    }

    public function suppliers() {
        $middleware = new Middleware();
        $middleware->admin();
        
        $suppliers = $this->supplierModel->getActiveSuppliers();
        
        include __DIR__ . '/../Views/layouts/header.php';
        ?>
        
        <div class="card">
            <div class="card-header">
                <h1>🏢 Gestión de Proveedores</h1>
                <a href="/admin/dashboard" class="btn">← Volver al Panel</a>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Contacto</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>RUC</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($suppliers as $supplier): ?>
                            <tr>
                                <td><?= htmlspecialchars($supplier['name']) ?></td>
                                <td><?= htmlspecialchars($supplier['contact_person'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($supplier['phone'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($supplier['email'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($supplier['ruc'] ?? 'N/A') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php
        include __DIR__ . '/../Views/layouts/footer.php';
    }

    public function supplies() {
        $middleware = new Middleware();
        $middleware->admin();
        
        $supplies = $this->supplyModel->getRecentSupplies(20);
        
        include __DIR__ . '/../Views/layouts/header.php';
        ?>
        
        <div class="card">
            <div class="card-header">
                <h1>🚚 Historial de Suministros</h1>
                <a href="/admin/dashboard" class="btn">← Volver al Panel</a>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Proveedor</th>
                            <th>Combustible</th>
                            <th>Litros</th>
                            <th>Costo Unit.</th>
                            <th>Costo Total</th>
                            <th>Factura</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($supplies as $supply): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($supply['supply_date'])) ?></td>
                                <td><?= htmlspecialchars($supply['supplier_name']) ?></td>
                                <td><?= htmlspecialchars($supply['fuel_name']) ?></td>
                                <td><?= number_format($supply['liters'], 1) ?> L</td>
                                <td>S/ <?= number_format($supply['unit_cost'], 2) ?></td>
                                <td><strong>S/ <?= number_format($supply['total_cost'], 2) ?></strong></td>
                                <td><?= htmlspecialchars($supply['invoice_number'] ?? 'N/A') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php
        include __DIR__ . '/../Views/layouts/footer.php';
    }
}
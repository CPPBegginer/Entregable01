<div class="dashboard-header">
    <h1>⚙️ Panel de Administración</h1>
    <p>Bienvenido, <strong><?= htmlspecialchars($user['name'] ?? 'Administrador') ?></strong> 👑</p>
</div>

<div class="grid grid-4">
    <div class="stat-card">
        <h3>👥 Usuarios</h3>
        <div class="stat-number"><?= $totalUsers ?? 0 ?></div>
        <p>Registrados</p>
    </div>
    <div class="stat-card">
        <h3>🚗 Clientes</h3>
        <div class="stat-number"><?= $totalCustomers ?? 0 ?></div>
        <p>En sistema</p>
    </div>
    <div class="stat-card">
        <h3>💰 Ventas Hoy</h3>
        <div class="stat-number">S/ <?= number_format($todaySales['total_amount'] ?? 0, 2) ?></div>
        <p>Ingresos</p>
    </div>
    <div class="stat-card">
        <h3>📦 Proveedores</h3>
        <div class="stat-number"><?= $totalSuppliers ?? 0 ?></div>
        <p>Activos</p>
    </div>
</div>

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
</div>
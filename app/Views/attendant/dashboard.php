<div class="dashboard-header">
    <h1>⛽ Panel del Despachador</h1>
    <p>Bienvenido, <strong><?= htmlspecialchars($user['name'] ?? 'Usuario') ?></strong></p>
</div>

<div class="grid grid-3">
    <div class="stat-card">
        <h3>Ventas Hoy</h3>
        <div class="stat-number"><?= $todaySales['total_sales'] ?? 0 ?></div>
        <p>Transacciones</p>
    </div>
    <div class="stat-card">
        <h3>Litros Vendidos</h3>
        <div class="stat-number"><?= number_format($todaySales['total_liters'] ?? 0, 1) ?></div>
        <p>Litros hoy</p>
    </div>
    <div class="stat-card">
        <h3>Total Vendido</h3>
        <div class="stat-number">S/ <?= number_format($todaySales['total_amount'] ?? 0, 2) ?></div>
        <p>Importe total</p>
    </div>
</div>

<div class="quick-actions">
    <a href="/attendant/new-sale" class="action-card">
        <h3>➕ Nueva Venta</h3>
        <p>Registrar nueva venta de combustible</p>
    </a>
    <a href="/attendant/sales" class="action-card">
        <h3>📋 Ver Ventas</h3>
        <p>Historial de ventas del día</p>
    </a>
</div>
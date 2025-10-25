<div class="card">
    <div class="card-header">
        <h1>🛒 Nueva Venta</h1>
        <a href="/attendant/dashboard" class="btn">← Volver</a>
    </div>
    
    <form method="POST" action="/attendant/process-sale">
        <div class="form-group">
            <label class="form-label">Combustible:</label>
            <select name="fuel_type_id" class="form-control" required>
                <option value="">Seleccionar...</option>
                <?php foreach($fuelTypes as $fuel): ?>
                    <option value="<?= $fuel['id'] ?>">
                        <?= htmlspecialchars($fuel['name']) ?> - S/ <?= $fuel['price_per_liter'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label class="form-label">Litros:</label>
            <input type="number" name="liters" class="form-control" step="0.1" min="0.1" required>
        </div>
        
        <button type="submit" class="btn btn-success">Procesar Venta</button>
    </form>
</div>
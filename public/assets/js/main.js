// Funciones JavaScript globales
function calculateFuelTotal() {
    const fuelSelect = document.getElementById('fuel_type');
    const litersInput = document.getElementById('liters');
    const priceInput = document.getElementById('price_per_liter');
    const totalInput = document.getElementById('total');
    const totalDisplay = document.getElementById('total_display');
    const totalText = document.getElementById('total_text');
    
    if (fuelSelect && fuelSelect.value && litersInput && litersInput.value) {
        const price = parseFloat(fuelSelect.selectedOptions[0].getAttribute('data-price'));
        const liters = parseFloat(litersInput.value);
        const total = price * liters;
        
        if (priceInput) priceInput.value = price.toFixed(2);
        if (totalInput) totalInput.value = total.toFixed(2);
        if (totalText) totalText.textContent = total.toFixed(2);
        if (totalDisplay) totalDisplay.style.display = 'block';
    } else {
        if (totalDisplay) totalDisplay.style.display = 'none';
    }
}

// Confirmaciones antes de acciones
function confirmAction(message) {
    return confirm(message || '¿Estás seguro de realizar esta acción?');
}

// Inicializaciones cuando el DOM está listo
document.addEventListener('DOMContentLoaded', function() {
    // Auto-calcular totales en formularios de venta
    const fuelSelect = document.getElementById('fuel_type');
    const litersInput = document.getElementById('liters');
    
    if (fuelSelect) {
        fuelSelect.addEventListener('change', calculateFuelTotal);
    }
    if (litersInput) {
        litersInput.addEventListener('input', calculateFuelTotal);
    }
});
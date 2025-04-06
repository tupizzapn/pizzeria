/**
 * Configuración global para toppings
 */
const toppingConfig = {
    minNombreLength: 3,
    decimalesPrecio: 2,
    precioMinimo: 0.01
};

/**
 * Inicialización cuando el DOM esté listo
 */
document.addEventListener('DOMContentLoaded', function() {
    // Configurar eventos
    configurarEventosTopping();
    
    // Configurar inputs de precio
    configurarInputsPrecioTopping();
    
    // Mostrar lista por defecto
    cambiarVistaTopping('lista');
});

/**
 * Configura todos los eventos de topping
 */
function configurarEventosTopping() {
    // Botones de navegación
    document.getElementById('btnNuevo')?.addEventListener('click', function(e) {
        e.preventDefault();
        cambiarVistaTopping('formulario');
    });
    
    document.getElementById('btnEditar')?.addEventListener('click', function(e) {
        e.preventDefault();
        cambiarVistaTopping('lista');
    });

    // Validación de topping existente
    document.getElementById('nombre_topping')?.addEventListener('blur', validarToppingUnico);
}

/**
 * Cambia entre vista de formulario y lista
 */
function cambiarVistaTopping(vista) {
    const formulario = document.querySelector('.nuevo_ingreso');
    const lista = document.querySelector('.lista-usuarios');
    
    if (vista === 'formulario') {
        formulario.style.display = 'block';
        lista.style.display = 'none';
        limpiarFormularioTopping();
    } else {
        formulario.style.display = 'none';
        lista.style.display = 'block';
    }
}

/**
 * Limpia el formulario de topping
 */
function limpiarFormularioTopping() {
    const form = document.querySelector('.nuevo_ingreso form');
    if (!form) return;

    form.reset();
    document.getElementById('topping-error').style.display = 'none';
}

/**
 * Configura los inputs de precio para toppings
 */
function configurarInputsPrecioTopping() {
    const inputsPrecio = document.querySelectorAll('.precio-input');
    
    inputsPrecio.forEach(input => {
        input.setAttribute('inputmode', 'decimal');
        input.addEventListener('input', manejarInputPrecioTopping);
        input.addEventListener('blur', formatearPrecioTopping);
    });
}

/**
 * Maneja el input para campos de precio
 */
function manejarInputPrecioTopping(e) {
    const input = e.target;
    const cursorPos = input.selectionStart;
    let value = input.value.replace(/[^0-9.,]/g, '');
    
    // Reemplazar coma por punto
    value = value.replace(',', '.');
    
    // Manejar múltiples puntos
    const parts = value.split('.');
    if (parts.length > 2) {
        value = parts[0] + '.' + parts.slice(1).join('');
    }
    
    // Limitar decimales
    if (parts.length > 1) {
        value = parts[0] + '.' + parts[1].slice(0, toppingConfig.decimalesPrecio);
    }
    
    input.value = value;
    input.setSelectionRange(cursorPos, cursorPos);
}

/**
 * Formatea el precio al perder foco
 */
function formatearPrecioTopping(e) {
    const input = e.target;
    if (!input.value) return;
    
    let value = parseFloat(input.value) || 0;
    if (value < toppingConfig.precioMinimo) value = toppingConfig.precioMinimo;
    
    input.value = value.toFixed(toppingConfig.decimalesPrecio);
}

/**
 * Valida que el topping sea único
 */
async function validarToppingUnico() {
    const nombre = document.getElementById('nombre_topping')?.value.trim();
    const errorElement = document.getElementById('topping-error');
    const submitBtn = document.querySelector('button[name="agregar_topping"]');
    
    if (!nombre || !errorElement || !submitBtn) return;
    
    if (nombre.length < toppingConfig.minNombreLength) {
        errorElement.textContent = `Mínimo ${toppingConfig.minNombreLength} caracteres`;
        errorElement.style.display = 'block';
        submitBtn.disabled = true;
        return;
    }
    
    try {
        const response = await fetch(
            `${API_BASE_URL}/api.php?nombre_topping=${encodeURIComponent(nombre)}`
        );
        const data = await response.json();
        
        if (data.existe) {
            errorElement.textContent = 'Ya existe un topping con este nombre';
            errorElement.style.display = 'block';
            submitBtn.disabled = true;
        } else {
            errorElement.style.display = 'none';
            submitBtn.disabled = false;
        }
    } catch (error) {
        console.error('Error:', error);
        errorElement.textContent = 'Error al validar';
        errorElement.style.display = 'block';
    }
}
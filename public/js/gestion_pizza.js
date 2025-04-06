/**
 * Configuración global para pizzas
 */
const pizzaConfig = {
    minNombreLength: 3,
    decimalesPrecio: 2,
    preciosMinimos: {
        familiar: 0.01,
        pequena: 0.01
    }
};

/**
 * Inicialización cuando el DOM esté listo
 */
document.addEventListener('DOMContentLoaded', function() {
    // Configurar eventos
    configurarEventosPizza();
    
    // Configurar inputs de precio
    configurarInputsPrecio();
    
    // Mostrar lista por defecto
    cambiarVistaPizza('lista');
});

/**
 * Configura todos los eventos de pizza
 */
function configurarEventosPizza() {
    // Botones de navegación
    document.getElementById('btnNuevo')?.addEventListener('click', function(e) {
        e.preventDefault();
        cambiarVistaPizza('formulario');
    });
    
    document.getElementById('btnEditar')?.addEventListener('click', function(e) {
        e.preventDefault();
        cambiarVistaPizza('lista');
    });

    // Validación de pizza existente
    document.getElementById('nombre_pizza')?.addEventListener('blur', validarPizzaUnica);
    document.getElementById('tamaño_pizza')?.addEventListener('change', validarPizzaUnica);
}

/**
 * Cambia entre vista de formulario y lista
 */
function cambiarVistaPizza(vista) {
    const formulario = document.querySelector('.nuevo_ingreso');
    const lista = document.querySelector('.lista-usuarios');
    
    if (vista === 'formulario') {
        formulario.style.display = 'block';
        lista.style.display = 'none';
        limpiarFormularioPizza();
    } else {
        formulario.style.display = 'none';
        lista.style.display = 'block';
    }
}

/**
 * Limpia el formulario de pizza
 */
function limpiarFormularioPizza() {
    const form = document.querySelector('.nuevo_ingreso form');
    if (!form) return;

    form.reset();
    document.getElementById('pizza-error').style.display = 'none';
}

/**
 * Configura los inputs de precio
 */
function configurarInputsPrecio() {
    const inputsPrecio = document.querySelectorAll('.precio-input');
    
    inputsPrecio.forEach(input => {
        input.setAttribute('inputmode', 'decimal');
        input.addEventListener('input', manejarInputPrecio);
        input.addEventListener('blur', formatearPrecio);
    });
}

/**
 * Maneja el input para campos de precio
 */
function manejarInputPrecio(e) {
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
        value = parts[0] + '.' + parts[1].slice(0, pizzaConfig.decimalesPrecio);
    }
    
    input.value = value;
    input.setSelectionRange(cursorPos, cursorPos);
}

/**
 * Formatea el precio al perder foco
 */
function formatearPrecio(e) {
    const input = e.target;
    if (!input.value) return;
    
    let value = parseFloat(input.value) || 0;
    const minValue = input.id.includes('familiar') ? 
        pizzaConfig.preciosMinimos.familiar : 
        pizzaConfig.preciosMinimos.pequena;
    
    if (value < minValue) value = minValue;
    
    input.value = value.toFixed(pizzaConfig.decimalesPrecio);
}

/**
 * Valida que la pizza sea única
 */
async function validarPizzaUnica() {
    const nombre = document.getElementById('nombre_pizza')?.value.trim();
    const tamaño = document.getElementById('tamaño_pizza')?.value;
    const errorElement = document.getElementById('pizza-error');
    const submitBtn = document.querySelector('button[name="agregar_pizza"]');
    
    if (!nombre || !tamaño || !errorElement || !submitBtn) return;
    
    if (nombre.length < pizzaConfig.minNombreLength) {
        errorElement.textContent = `Mínimo ${pizzaConfig.minNombreLength} caracteres`;
        errorElement.style.display = 'block';
        submitBtn.disabled = true;
        return;
    }
    
    try {
        const response = await fetch(
            `${API_BASE_URL}/api.php?nombre_pizza=${encodeURIComponent(nombre)}&tamaño_pizza=${encodeURIComponent(tamaño)}`
        );
        const data = await response.json();
        
        if (data.existe) {
            errorElement.textContent = 'Ya existe una pizza con este nombre y tamaño';
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
document.addEventListener('DOMContentLoaded', function() {
    // Elementos del DOM con validación de existencia
    const deliveryCheckbox = document.getElementById('requiere_delivery');
    const direccionContainer = document.getElementById('direccion_container');
    const direccionInput = document.getElementById('direccion');
    const deliverySelect = document.getElementById('delivery_id');
    const actualizarDirCheckbox = document.getElementById('actualizar_direccion_cliente');
    
    // Verificar que los elementos esenciales existen
    if (!deliveryCheckbox || !direccionContainer || !direccionInput || !deliverySelect) {
        console.error('Error: Elementos esenciales del formulario no encontrados');
        return;
    }

    // Configuración inicial segura
    function initDeliveryFields() {
        try {
            const requiereDelivery = deliveryCheckbox.checked;
            
            // Mostrar/ocultar campos de dirección
            direccionContainer.style.display = requiereDelivery ? 'block' : 'none';
            direccionInput.required = requiereDelivery;
            
            // Habilitar/deshabilitar select de repartidor
            deliverySelect.disabled = !requiereDelivery;
            deliverySelect.required = requiereDelivery;
            
            // Seleccionar primer repartidor por defecto si hay delivery
            if (requiereDelivery && deliverySelect.value === '' && deliverySelect.options.length > 0) {
                deliverySelect.selectedIndex = 0;
            }

            // Manejo seguro del checkbox de actualización (si existe)
            if (actualizarDirCheckbox) {
                const tieneDireccion = direccionInput.value.trim() !== '';
                actualizarDirCheckbox.disabled = !tieneDireccion;
                
                // Contenedor opcional (si existe)
                const actualizarDirContainer = document.getElementById('actualizar_direccion_container');
                if (actualizarDirContainer) {
                    actualizarDirContainer.style.display = tieneDireccion ? 'block' : 'none';
                }
            }
        } catch (error) {
            console.error('Error en initDeliveryFields:', error);
        }
    }

    // Inicialización segura
    try {
        initDeliveryFields();
        deliveryCheckbox.addEventListener('change', initDeliveryFields);
        
        const pedidoForm = document.getElementById('pedidoForm');
        if (pedidoForm) {
            pedidoForm.addEventListener('submit', function(e) {
                if (deliveryCheckbox.checked && !direccionInput.value.trim()) {
                    e.preventDefault();
                    alert('Por favor, ingrese la dirección de entrega.');
                    direccionInput.focus();
                }
            });
        }
    } catch (error) {
        console.error('Error en la inicialización:', error);
    }
});
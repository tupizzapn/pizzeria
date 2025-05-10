document.addEventListener('DOMContentLoaded', function() {
    const deliveryCheckbox = document.getElementById('requiere_delivery');
    const direccionContainer = document.getElementById('direccion_container');
    const direccionInput = document.getElementById('direccion');
    const deliverySelect = document.getElementById('delivery_id');
    
    // Configuración inicial
    function initDeliveryFields() {
        if (deliveryCheckbox.checked) {
            direccionContainer.style.display = 'block';
            deliverySelect.required = true;
            direccionInput.required = true;
        } else {
            direccionContainer.style.display = 'none';
            deliverySelect.required = false;
            direccionInput.required = false;
        }
    }
    
    // Inicializar
    deliveryCheckbox.checked = true;
    initDeliveryFields();
    
    // Manejar cambios
    deliveryCheckbox.addEventListener('change', initDeliveryFields);
    
    // Validación antes de enviar
    document.querySelector('form').addEventListener('submit', function(e) {
        if (deliveryCheckbox.checked && !direccionInput.value) {
            e.preventDefault();
            alert('Por favor ingrese la dirección para delivery');
            direccionInput.focus();
        }
    });
});
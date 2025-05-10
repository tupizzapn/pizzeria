document.addEventListener('DOMContentLoaded', function() {
    // Configuración existente del ScrollManager
    const tablaScrollManager = new ScrollManager({
        containerSelector: '#tablaPedidosContainer',
        itemSelector: '#cuerpoTablaPedidos tr',
        maxHeight: 40,
        scrollbarColor: '#e74c3c'
    });

    // Función mejorada para enviar por WhatsApp
    function enviarResumen(event) {
        event.preventDefault();
        const boton = event.target;
        
        try {
            boton.disabled = true;
            boton.innerHTML = '<span class="spinner"></span> Enviando...';
            const telefonoCliente = document.getElementById('telefono-cliente').textContent.trim();
            const nombreCliente = document.getElementById('nombre-cliente').textContent.trim();
            const totalPedido = document.getElementById('total-pedido').textContent.trim();

            if (!telefonoCliente) throw new Error('Número de teléfono no disponible');

            // Formatear teléfono para Venezuela (+58)
            const telefonoLimpio = telefonoCliente.replace(/\D/g, '');
            const codigoPais = '58';
            let numeroWhatsApp = telefonoLimpio.startsWith(codigoPais) 
                ? telefonoLimpio 
                : codigoPais + telefonoLimpio;
            numeroWhatsApp = numeroWhatsApp.replace(/^580?/, codigoPais);

            // Crear mensaje
            const mensaje = `*🍕 Resumen de Pedido - Pizzería* 🍕\n\n` +
                           `*Cliente:* ${nombreCliente || 'No especificado'}\n` +
                           `*Teléfono:* ${telefonoCliente}\n` +
                           `*Total:* Bs. ${totalPedido}\n\n` +
                           `*Métodos de pago:*\n` +
                           `- Efectivo (Bs./$)\n` +
                           `- Transferencia bancaria\n` +
                           `- Pago móvil\n\n` +
                           `*Confirmar pedido respondiendo:*\n` +
                           `✅ Sí - ❌ No - ✏ Modificar`;

            // Abrir WhatsApp
            window.open(`https://wa.me/${numeroWhatsApp}?text=${encodeURIComponent(mensaje)}`, '_blank');
            
            // Redirección ABSOLUTA confiable
            setTimeout(() => {
                // Versión 1: Ruta absoluta directa (recomendada)
                window.location.href = '/pizzeria/views/pedidos.php';
                
                /* 
                 * Versión 2: Alternativa dinámica (por si cambia la estructura)
                 * const basePath = window.location.pathname.includes('/pizzeria') 
                 *     ? '/pizzeria' 
                 *     : '';
                 * window.location.href = `${basePath}/views/pedidos.php`;
                 */
            }, 1500);

        } catch (error) {
            console.error('Error al enviar:', error);
            mostrarNotificacion('Error al preparar el enlace de WhatsApp', 'error');
            boton.disabled = false;
            boton.textContent = 'Enviar';
        }
    }

    // Función de notificación
    function mostrarNotificacion(mensaje, tipo = 'success') {
        const notificacion = document.createElement('div');
        notificacion.className = `notificacion ${tipo}`;
        notificacion.innerHTML = `
            <span class="icono">${tipo === 'success' ? '🍕' : '⚠'}</span>
            <span>${mensaje}</span>
        `;
        document.body.appendChild(notificacion);
        setTimeout(() => notificacion.remove(), 3000);
    }

    // Asignar evento al botón
    const botonEnviar = document.getElementById('boton-enviar');
    if (botonEnviar) {
        botonEnviar.addEventListener('click', enviarResumen);
    }
});
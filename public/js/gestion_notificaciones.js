// public/js/gestion_notificaciones.js
function enviarNotificaciones(notificaciones, baseUrl) {
    // Enlaces WhatsApp
    const enlacePizzero = `https://wa.me/${notificaciones.pizzero.telefono}?text=${encodeURIComponent(notificaciones.pizzero.mensaje)}`;
    
    if (notificaciones.delivery) {
        const enlaceDelivery = `https://wa.me/${notificaciones.delivery.telefono}?text=${encodeURIComponent(notificaciones.delivery.mensaje)}`;
        window.open(enlaceDelivery, '_blank'); // Delivery primero
    }

    setTimeout(() => {
        window.open(enlacePizzero, '_blank'); // Pizzero después
        window.location.href = `${baseUrl}/views/pedidos_procesados.php`;
    }, 15000);
}

// Inicialización
if (typeof notificacionesData !== 'undefined') {
    document.addEventListener('DOMContentLoaded', () => {
        enviarNotificaciones(notificacionesData.notificaciones, notificacionesData.baseUrl);
    });
}

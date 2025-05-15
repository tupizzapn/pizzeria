<?php
session_start();
include __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['notificaciones'])) {
    header('Location: ' . BASE_URL . '/views/pedidos.php');
    exit();
}

$notificaciones = $_SESSION['notificaciones'];
unset($_SESSION['notificaciones']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificaciones Enviadas</title>
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Indie+Flower&family=Lugrasimo&display=swap" rel="stylesheet">
    <style>
    .contenedor_resumen {
        background-image: url('<?php echo IMG_URL; ?>/backgrounds/background_pedido.jpg');
            }
    .pantalla_resumen { 
        background-image: url('<?php echo IMG_URL; ?>/backgrounds/background_telefono.png');
          
    </style>
</head>
<body>
    <div class="contenedor_resumen">
        <div class="pantalla_resumen">
            <div class="area_resumen">
                <div class="titulo_resumen">
                    <h1>Notificaciones Enviadas</h1>
                </div>
                
                <!-- Mensaje al Pizzero -->
                <div class="mensaje-container">
                    <div class="mensaje-header">🍕 Mensaje al Pizzero:</div>
                    <div class="mensaje-content"><?= htmlspecialchars($notificaciones['pizzero']['mensaje']) ?></div>
                </div>
                
                <!-- Mensaje al Delivery (si existe) -->
                <?php if (!empty($notificaciones['delivery'])): ?>
                <div class="mensaje-container">
                    <div class="mensaje-header">🚴 Mensaje al Repartidor:</div>
                    <div class="mensaje-content"><?= htmlspecialchars($notificaciones['delivery']['mensaje']) ?></div>
                </div>
                <?php endif; ?>
                
                <div class="spinner"></div>
                <div class="redireccionando">Redirigiendo automáticamente...</div>
                
                <button class="btn-continuar" onclick="window.location.href='<?= BASE_URL ?>/views/pedidos_procesados.php'">
                    Continuar ahora
                </button>
            </div>
        </div>
    </div>

    <script>
    // Pasar datos PHP a JS de forma segura
    const notificacionesData = {
        notificaciones: <?= json_encode($notificaciones) ?>,
        baseUrl: '<?= BASE_URL ?>'
    };
    </script>
    <script src="<?php echo JS_URL; ?>/gestion_notificaciones.js"></script>
</body>
</html>

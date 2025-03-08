<?php
session_start();
include __DIR__ . '/../includes/config.php'; // Incluir config.php

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/controllers/login.php'); // Ruta corregida
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Pedido</title>
    <link rel="stylesheet" href="<?php echo CSS_DIR; ?>/styles.css"> <!-- Ruta corregida -->
</head>
<body>
    <h1>¡Pedido recibido!</h1>
    <p>Gracias por tu pedido. Estamos procesándolo y te contactaremos pronto.</p>
    <a href="<?php echo BASE_URL; ?>/views/index.php" class="btn">Volver al Menú Principal</a> <!-- Ruta corregida -->
</body>
</html>
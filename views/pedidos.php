<?php
session_start();
include __DIR__ . '/../includes/config.php'; // Incluir config.php

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/controllers/login.php'); // Ruta corregida
    exit();
}

if ($_SESSION['rol'] !== 'vendedor') {
    echo "Acceso denegado. Solo los vendedores pueden ver pedidos.";
    exit();
}

include __DIR__ . '/../includes/db.php'; // Ruta corregida

// Obtener la lista de pedidos con los datos del cliente
$pedidos = $conn->query("
    SELECT p.id, p.fecha_pedido, p.total, c.nombre AS nombre_cliente, c.telefono 
    FROM pedidos p
    JOIN clientes c ON p.cliente_id = c.id
    ORDER BY p.fecha_pedido DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos</title>
    <link rel="stylesheet" href="<?php echo CSS_DIR; ?>/styles.css"> <!-- Ruta corregida -->
</head>
<body>
    <h1>Pedidos</h1>
    <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?>! 
       <a href="<?php echo BASE_URL; ?>/controllers/logout.php">Cerrar sesión</a> <!-- Ruta corregida -->
    </p>

    <!-- Botón para volver al menú principal -->
    <a href="<?php echo BASE_URL; ?>/views/index.php" class="btn">Volver al Menú Principal</a> <!-- Ruta corregida -->

    <h2>Lista de Pedidos</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Teléfono</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pedidos as $pedido): ?>
                <tr>
                    <td><?php echo htmlspecialchars($pedido['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($pedido['fecha_pedido'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($pedido['nombre_cliente'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($pedido['telefono'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>$<?php echo number_format($pedido['total'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
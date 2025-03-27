<?php
session_start();
include __DIR__ . '/../includes/config.php'; // Incluir config.php
include __DIR__ . '/../includes/db.php'; // Incluir conexión a la base de datos

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/controllers/login.php'); // Redirigir al login
    exit();
}

// Verificar si el usuario tiene el rol de vendedor o admin
if ($_SESSION['rol'] !== 'vendedor' && $_SESSION['rol'] !== 'admin') {
    echo "Acceso denegado. Solo vendedores y administradores pueden ver los pedidos procesados.";
    exit();
}

// Obtener la fecha actual
$fecha_actual = date('Y-m-d');

// Obtener los pedidos procesados del día con nombres de repartidores, pizzeros y teléfono del cliente
$query = "
    SELECT 
        p.id, 
        p.fecha_pedido, 
        p.total, 
        v.delivery_id, 
        v.pizzero_id, 
        v.fecha_venta, 
        d.nombre AS nombre_delivery, 
        pz.nombre AS nombre_pizzero,
        c.telefono AS telefono_cliente
    FROM pedidos p
    INNER JOIN ventas v ON p.id = v.pedido_id
    LEFT JOIN delivery d ON v.delivery_id = d.usuario_id
    LEFT JOIN pizzero pz ON v.pizzero_id = pz.usuario_id
    LEFT JOIN clientes c ON p.cliente_id = c.id
    WHERE DATE(v.fecha_venta) = :fecha_actual
    ORDER BY v.fecha_venta DESC
";
$stmt = $conn->prepare($query);
$stmt->execute(['fecha_actual' => $fecha_actual]);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pedidos Procesados del Día</title>
</head>
<body>
    <h1>Pedidos Procesados del Día</h1>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Teléfono</th>
                <th>Fecha Pedido</th>
                <th>Total</th>
                <th>Repartidor</th>
                <th>Pizzero</th>
                <th>Fecha Venta</th>
            </tr>
        </thead>
        <tbody>
        <a href="pedidos.php">Ver Pedidos Pendientes</a>
            <?php foreach ($pedidos as $pedido): ?>
                <tr>
                    <td><?php echo htmlspecialchars($pedido['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($pedido['telefono_cliente'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($pedido['fecha_pedido'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>$<?php echo htmlspecialchars($pedido['total'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($pedido['nombre_delivery'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($pedido['nombre_pizzero'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($pedido['fecha_venta'], ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
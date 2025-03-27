<?php
session_start();
include __DIR__ . '/../includes/config.php'; // Incluir config.php
include __DIR__ . '/../includes/db.php'; // Incluir conexión a la base de datos

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/controllers/login.php'); // Redirigir al login
    exit();
}

// Verificar si el usuario tiene el rol de vendedor
if ($_SESSION['rol'] !== 'vendedor') {
    echo "Acceso denegado. Solo los vendedores pueden ver los pedidos.";
    exit();
}

// Obtener la fecha actual
$fecha_actual = date('Y-m-d');

// Obtener los pedidos no procesados del día (que no están en la tabla ventas)
$query = "
    SELECT p.* 
    FROM pedidos p
    LEFT JOIN ventas v ON p.id = v.pedido_id
    WHERE v.pedido_id IS NULL AND DATE(p.fecha_pedido) = :fecha_actual
    ORDER BY p.fecha_pedido DESC
";
$stmt = $conn->prepare($query);
$stmt->execute(['fecha_actual' => $fecha_actual]);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pedidos Pendientes</title>
</head>
<body>
    <h1>Pedidos Pendientes del Día</h1>
    <a href="pedidos_procesados.php">Ver Pedidos Procesados</a>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Fecha</th>
                <th>Total</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pedidos as $pedido): ?>
                <tr>
                    <td><?php echo htmlspecialchars($pedido['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($pedido['cliente_id'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($pedido['fecha_pedido'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>$<?php echo htmlspecialchars($pedido['total'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($pedido['estado'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <a href="asignar_delivery.php?id=<?php echo $pedido['id']; ?>">Asignar Delivery</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
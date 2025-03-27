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
    echo "Acceso denegado. Solo los vendedores pueden asignar delivery.";
    exit();
}

// Obtener el ID del pedido desde la URL
$pedido_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($pedido_id <= 0) {
    echo "Error: ID de pedido no válido.";
    exit();
}

// Obtener información del pedido
$stmt = $conn->prepare("SELECT * FROM pedidos WHERE id = ?");
$stmt->execute([$pedido_id]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    echo "Error: Pedido no encontrado.";
    exit();
}

// Obtener información del cliente
$stmt = $conn->prepare("SELECT nombre FROM clientes WHERE telefono = ?");
$stmt->execute([$pedido['cliente_id']]); // Asumiendo que cliente_id es el teléfono
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

$nombre_cliente = $cliente ? $cliente['nombre'] : '';

// Obtener lista de delivery y pizzeros disponibles
$delivery_stmt = $conn->query("SELECT * FROM delivery");
$pizzero_stmt = $conn->query("SELECT * FROM pizzero");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignar Delivery</title>
</head>
<body>
    <h1>Asignar Delivery y Preparación</h1>
    <p>Pedido ID: <?php echo htmlspecialchars($pedido['id'], ENT_QUOTES, 'UTF-8'); ?></p>
    <p>Teléfono del Cliente: <?php echo htmlspecialchars($pedido['cliente_id'], ENT_QUOTES, 'UTF-8'); ?></p>
    <p>Total: $<?php echo htmlspecialchars($pedido['total'], ENT_QUOTES, 'UTF-8'); ?></p>

    <form action="../controllers/procesar_asignacion.php" method="POST">
        <input type="hidden" name="pedido_id" value="<?php echo $pedido_id; ?>">
        <input type="hidden" name="telefono" value="<?php echo htmlspecialchars($pedido['cliente_id'], ENT_QUOTES, 'UTF-8'); ?>">

        <!-- Campo para nombre del cliente (obligatorio si no está registrado) -->
        <label for="nombre_cliente">Nombre del Cliente:</label>
        <input type="text" name="nombre_cliente" id="nombre_cliente" value="<?php echo htmlspecialchars($nombre_cliente, ENT_QUOTES, 'UTF-8'); ?>" required>
        <br>

        <!-- Campo para dirección (solo si requiere delivery) -->
        <label for="requiere_delivery">¿Requiere delivery?</label>
        <input type="checkbox" name="requiere_delivery" id="requiere_delivery" onchange="toggleDireccion()">
        <br>

        <div id="direccion_container" style="display: none;">
            <label for="direccion">Dirección o enlace de ubicación:</label>
            <input type="text" name="direccion" id="direccion">
            <br>
        </div>

        <!-- Selección de personal de delivery -->
        <label for="delivery_id">Seleccionar Repartidor:</label>
        <select name="delivery_id" id="delivery_id" required>
            <?php while ($delivery = $delivery_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                <option value="<?php echo $delivery['usuario_id']; ?>">
                    <?php echo htmlspecialchars($delivery['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                </option>
            <?php endwhile; ?>
        </select>
        <br>

        <!-- Selección de pizzero -->
        <label for="pizzero_id">Seleccionar Pizzero:</label>
        <select name="pizzero_id" id="pizzero_id" required>
            <?php while ($pizzero = $pizzero_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                <option value="<?php echo $pizzero['usuario_id']; ?>">
                    <?php echo htmlspecialchars($pizzero['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                </option>
            <?php endwhile; ?>
        </select>
        <br>

        <button type="submit">Asignar y Confirmar</button>
    </form>

    <script>
        function toggleDireccion() {
            const requiereDelivery = document.getElementById('requiere_delivery');
            const direccionContainer = document.getElementById('direccion_container');
            if (requiereDelivery.checked) {
                direccionContainer.style.display = 'block';
            } else {
                direccionContainer.style.display = 'none';
            }
        }
    </script>
</body>
</html>
<?php
session_start();
include __DIR__ . '/../includes/config.php';
include __DIR__ . '/../includes/db.php';

// Verificar sesión y rol
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/controllers/login.php');
    exit();
}
if ($_SESSION['rol'] !== 'vendedor') {
    echo "Acceso denegado. Solo los vendedores pueden ver los pedidos.";
    exit();
}

// Validar y sanitizar el ID del pedido
$pedido_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$pedido_id) {
    echo "ID de pedido no válido.";
    exit();
}

// Obtener información del pedido y del cliente
$stmt = $conn->prepare("
    SELECT p.*, c.nombre AS cliente_nombre, c.telefono AS cliente_telefono, c.direccion AS cliente_direccion, p.cliente_id
    FROM pedidos p
    LEFT JOIN clientes c ON p.cliente_id = c.id
    WHERE p.id = ?
");
$stmt->execute([$pedido_id]);
$pedido_info = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pedido_info) {
    echo "Error: Pedido no encontrado.";
    exit();
}

$nombre_cliente = $pedido_info['cliente_nombre'] ?? '';
$telefono_cliente = $pedido_info['cliente_telefono'] ?? '';
$direccion_cliente = $pedido_info['cliente_direccion'] ?? '';
$cliente_id = $pedido_info['cliente_id'];
$direccion_inicial = $pedido_info['cliente_direccion'] ?? '';

// Obtener lista de delivery y pizzeros disponibles
$delivery_stmt = $conn->query("SELECT * FROM delivery");
$pizzero_stmt = $conn->query("SELECT * FROM pizzero");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventas</title>
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Indie+Flower&family=Lugrasimo&display=swap" rel="stylesheet">
    <style>
        .contenedor_ventas {
            background-image: url('<?php echo IMG_URL; ?>/backgrounds/background_ventas.jpg');
        }
    </style>
</head>
<body>
<div class="contenedor_ventas">
    <div class="factura">
        <div class="titulo_resumen">
            <h1>Procesar Venta</h1>

            <div class="info_item">
                <strong>Pedido ID:</strong><span><?php echo htmlspecialchars($pedido_info['id'], ENT_QUOTES, 'UTF-8'); ?></span>
            </div>

            <div class="info_item">
                <strong>Teléfono: </strong><span><?php echo htmlspecialchars($telefono_cliente, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <div class="info_item">
                <strong>Total ($) :     </strong><span><?php echo htmlspecialchars($pedido_info['total'], ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
        </div>

        <div>
            <form id="pedidoForm" action="<?php echo BASE_URL; ?>/controllers/procesar_asignacion.php" method="POST">
                <input type="hidden" name="pedido_id" value="<?php echo $pedido_id; ?>">
                <input type="hidden" name="cliente_id" value="<?php echo htmlspecialchars($cliente_id, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="telefono" value="<?php echo htmlspecialchars($telefono_cliente, ENT_QUOTES, 'UTF-8'); ?>">

                <label for="nombre_cliente">Nombre del Cliente:</label>
                <input type="text" name="nombre_cliente" id="nombre_cliente" value="<?php echo htmlspecialchars($nombre_cliente, ENT_QUOTES, 'UTF-8'); ?>" required>
                <br>

                <label for="requiere_delivery">¿Requiere delivery?</label>
                <input type="checkbox" name="requiere_delivery" id="requiere_delivery" onchange="toggleDireccion()" checked>
                <br>

                <div id="direccion_container" style="display: block;">
                    <label for="direccion">Dirección de Entrega:</label>
                    <input type="text" name="direccion" id="direccion" value="<?php echo htmlspecialchars($direccion_inicial, ENT_QUOTES, 'UTF-8'); ?>" required>
                    
                    <?php if(!empty($direccion_inicial)): ?>
                        <label for="actualizar_direccion_cliente">¿Actualizar dirección del cliente?</label>
                        <input type="checkbox" name="actualizar_direccion_cliente" id="actualizar_direccion_cliente">
                    <?php endif; ?>
                </div>

             <label for="delivery_id">Repartidor:</label>
<select name="delivery_id" id="delivery_id" required>
    <?php
    // Reiniciar el puntero del resultado para volver a leer los datos
    $delivery_stmt->execute(); 
    while ($delivery = $delivery_stmt->fetch(PDO::FETCH_ASSOC)): ?>
        <option value="<?php echo $delivery['usuario_id']; ?>" <?php echo ($delivery_stmt->rowCount() == 1) ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($delivery['nombre'], ENT_QUOTES, 'UTF-8'); ?>
        </option>
    <?php endwhile; ?>
</select>
                <br>

                <label for="pizzero_id">Seleccionar Pizzero:</label>
                <select name="pizzero_id" id="pizzero_id" required>
                    <?php
                    $pizzero_stmt->execute();
                    while ($pizzero = $pizzero_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <option value="<?php echo $pizzero['usuario_id']; ?>"><?php echo htmlspecialchars($pizzero['nombre'], ENT_QUOTES, 'UTF-8'); ?></option>
                    <?php endwhile; ?>
                </select>
                <br>

                <button class="enviar-pedido" type="submit">Confirmar</button>
            </form>
        </div>
    </div>
</div>
<script src="<?php echo JS_URL; ?>/gestion_ventas.js"></script>
</body>
</html>
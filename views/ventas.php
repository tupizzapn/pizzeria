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

// Validar y sanitizar el ID del pedido
$pedido_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$pedido_id) {
    echo "ID de pedido no válido.";
    exit();
}

// Obtener información del pedido
$stmt = $conn->prepare("
    SELECT p.*, c.nombre, c.telefono 
    FROM pedidos p
    JOIN clientes c ON p.cliente_id = c.id
    WHERE p.id = ?
");
$stmt->execute([$pedido_id]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    echo "Error: Pedido no encontrado.";
    exit();
}

$nombre_cliente = $pedido['nombre'] ?? '';  // Usamos el nombre desde el JOIN
$telefono_cliente = $pedido['telefono'] ?? '';

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
                            <strong>Pedido ID:</strong><span><?php echo htmlspecialchars($pedido['id'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div> 

                        <div class="info_item"> 
                            <strong>Teléfono: </strong><span><?php echo htmlspecialchars($telefono_cliente, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div> 
                        <div class="info_item">
                            <strong>Total ($) :     </strong><span><?php echo htmlspecialchars($pedido['total'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div> 
                    </div>

                    <div>
                        <form id="pedidoForm" action="<?php echo BASE_URL; ?>/controllers/procesar_asignacion.php" method="POST">
                            <input type="hidden" name="pedido_id" value="<?php echo $pedido_id; ?>">
                            

                            <!-- Campo para nombre del cliente (obligatorio si no está registrado) -->
                            <label for="nombre_cliente">Nombre del Cliente:</label>
                            <input type="text" name="nombre_cliente" id="nombre_cliente" value="<?php echo htmlspecialchars($nombre_cliente, ENT_QUOTES, 'UTF-8'); ?>" required>
                            <br>
                            <input type="hidden" name="cliente_id" value="<?php echo htmlspecialchars($pedido['cliente_id'], ENT_QUOTES, 'UTF-8'); ?>">
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
                            <select name="delivery_id" id="delivery_id">
                                <?php while ($delivery = $delivery_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                    <option value="<?php echo $delivery['id']; ?>"> <!-- Cambiar usuario_id por id -->
                                        <?php echo htmlspecialchars($delivery['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <br>

                            <!-- Selección de pizzero -->
                            <label for="pizzero_id">Seleccionar Pizzero:</label>
                            <select name="pizzero_id" id="pizzero_id">
                                <?php while ($pizzero = $pizzero_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                    <option value="<?php echo $pizzero['id']; ?>"> <!-- Cambiar usuario_id por id -->
                                        <?php echo htmlspecialchars($pizzero['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <br>
                    </div>
                            <button class="enviar-pedido" type="submit">Confirmar</button>
                        </form>
    </div>
</div>  
<script src="<?php echo JS_URL; ?>/gestion_ventas.js"></script>

                     
</body>
</html>
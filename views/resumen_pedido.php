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

// Obtener los detalles del pedido
$pedido = $conn->query("SELECT * FROM pedidos WHERE id = $pedido_id")->fetch(PDO::FETCH_ASSOC);
$cliente = $conn->query("SELECT * FROM clientes WHERE id = {$pedido['cliente_id']}")->fetch(PDO::FETCH_ASSOC);
$detalles_pedido = $conn->query("SELECT * FROM detalles_pedido WHERE pedido_id = $pedido_id")->fetchAll(PDO::FETCH_ASSOC);

$redirect_url = BASE_URL . '/views/pedidos.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen del Pedido</title>
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Indie+Flower&family=Lugrasimo&display=swap" rel="stylesheet">
    <style>
    .contenedor_resumen {
        background-image: url('<?php echo IMG_URL; ?>/backgrounds/background_pedido.jpg');
    }
    .pantalla_resumen { 
        background-image: url('<?php echo IMG_URL; ?>/backgrounds/background_telefono.png');
    }
     </style>
</head>
<body>
<div class="contenedor_resumen">
    <div class="pantalla_resumen"> 
        <div class="area_resumen">
            <div class="titulo_resumen">
                <h1>Resumen del Pedido</h1>
                
                <h2 class="font_resumen">Datos del Cliente</h2>

                <div class="info-line">
                    <strong class="font_forma">Nombre:</strong>
                    <span id="nombre-cliente" class="value"><?php echo htmlspecialchars($cliente['nombre'], ENT_QUOTES, 'UTF-8'); ?></span>
                </div>

                <div class="info-line">
                    <strong class="font_forma">Teléfono:</strong>
                    <span id="telefono-cliente" class="value"><?php echo htmlspecialchars($cliente['telefono'], ENT_QUOTES, 'UTF-8'); ?></span>
                </div> <!-- Cierre faltante para este div info-line -->
                
                <h2 class="font_resumen">Detalles del Pedido</h2>
            </div> <!-- Cierre de titulo_resumen -->
    
            <div class="contenedor-tabla" id="tablaPedidosContainer">
                <table class="tabla-pedidos">
                    <thead class="sticky-header">
                        <tr class="font_tabla">
                            <th>Pizza</th>
                            <th>Cant</th>
                            <th>Toppins</th>
                            <th>Precio</th>
                        </tr>
                    </thead>
                    <tbody id="cuerpoTablaPedidos">
                        <?php foreach ($detalles_pedido as $detalle): ?>
                            <?php
                            $pizza = $conn->query("SELECT * FROM pizzas WHERE id = {$detalle['pizza_id']}")->fetch(PDO::FETCH_ASSOC);
                            $toppings = $conn->query("SELECT t.nombre, t.precio_familiar, t.precio_pequeña FROM toppings_pedido tp JOIN toppings t ON tp.topping_id = t.id WHERE tp.detalle_pedido_id = {$detalle['id']}")->fetchAll(PDO::FETCH_ASSOC);
                            $precio_toppings = 0;

                            foreach ($toppings as $topping) {
                                $precio_toppings += ($pizza['tamaño'] === 'Familiar') ? $topping['precio_familiar'] : $topping['precio_pequeña'];
                            }

                            $precio_total_pizza = ($pizza['precio'] + $precio_toppings) * $detalle['cantidad'];
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($pizza['nombre'], ENT_QUOTES, 'UTF-8'); ?> (<?php echo htmlspecialchars($pizza['tamaño'], ENT_QUOTES, 'UTF-8'); ?>)</td>
                                <td><?php echo htmlspecialchars($detalle['cantidad'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <div class="lista-toppings">
                                        <?php foreach ($toppings as $topping): ?>
                                            <span class="topping-pedido">
                                                • <?php echo htmlspecialchars($topping['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                                ($<?php echo ($pizza['tamaño'] === 'Familiar') ? 
                                                    number_format($topping['precio_familiar'], 2) : 
                                                    number_format($topping['precio_pequeña'], 2); ?>)
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td>$<?php echo number_format($precio_total_pizza, 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div> <!-- cierre de contenedor-tabla -->

            <h2 class="font_resumen">Total del Pedido</h2>
            <p><strong class="font_forma">Total ($): </strong>  <span id="total-pedido" class="font_total"><?php echo number_format($pedido['total'], 2); ?></span></p>
            <!-- Botón para enviar resumen al cliente -->
            <button class="enviar-pedido" id="boton-enviar">Enviar</button>
        </div> <!-- cierre de area_resumen -->
    </div> <!-- cierre de pantalla_resumen -->        
</div> <!-- cierre de contenedor_resumen -->                                       

</script>
<script src="<?php echo JS_URL; ?>/scrollManager.js"></script>
<script src="<?php echo JS_URL; ?>/resumen_pedido.js" defer></script>
</body>
</html>
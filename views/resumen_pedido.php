<?php
session_start();
include __DIR__ . '/../includes/config.php'; // Incluir config.php

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/controllers/login.php'); // Ruta corregida
    exit();
}

if ($_SESSION['rol'] !== 'vendedor') {
    echo "Acceso denegado. Solo los vendedores pueden ver resúmenes de pedidos.";
    exit();
}

include __DIR__ . '/../includes/db.php'; // Ruta corregida

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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen del Pedido</title>
    <link rel="stylesheet" href="<?php echo CSS_DIR; ?>/styles.css"> <!-- Ruta corregida -->
</head>
<body>
    <h1>Resumen del Pedido</h1>
    <h2>Datos del Cliente</h2>
    <p><strong>Nombre:</strong> <?php echo htmlspecialchars($cliente['nombre'], ENT_QUOTES, 'UTF-8'); ?></p>
    <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($cliente['telefono'], ENT_QUOTES, 'UTF-8'); ?></p>

    <h2>Detalles del Pedido</h2>
    <table>
        <thead>
            <tr>
                <th>Pizza</th>
                <th>Cantidad</th>
                <th>Toppings</th>
                <th>Precio</th>
            </tr>
        </thead>
        <tbody>
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
                        <?php foreach ($toppings as $topping): ?>
                            <?php echo htmlspecialchars($topping['nombre'], ENT_QUOTES, 'UTF-8'); ?> 
                            ($<?php echo ($pizza['tamaño'] === 'Familiar') ? $topping['precio_familiar'] : $topping['precio_pequeña']; ?>),
                        <?php endforeach; ?>
                    </td>
                    <td>$<?php echo number_format($precio_total_pizza, 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Total del Pedido</h2>
    <p><strong>Total:</strong> $<?php echo number_format($pedido['total'], 2); ?></p>

    <!-- Botón para enviar resumen al cliente -->
    <button onclick="enviarResumen()">Enviar Resumen al Cliente</button>

    <script>
        function formatearNumeroWhatsApp(numero) {
            // Eliminar cualquier carácter que no sea un número
            numero = numero.replace(/\D/g, '');

            // Si el número comienza con "0", reemplazarlo con "+58"
            if (numero.startsWith('0')) {
                numero = '58' + numero.substring(1);
            }

            return numero;
        }

        function enviarResumen() {
            const resumen = `
                Resumen del Pedido:
                Cliente: <?php echo htmlspecialchars($cliente['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                Teléfono: <?php echo htmlspecialchars($cliente['telefono'], ENT_QUOTES, 'UTF-8'); ?>

                Detalles del Pedido:
                <?php foreach ($detalles_pedido as $detalle): ?>
                    - <?php echo htmlspecialchars($pizza['nombre'], ENT_QUOTES, 'UTF-8'); ?> (<?php echo htmlspecialchars($pizza['tamaño'], ENT_QUOTES, 'UTF-8'); ?>): <?php echo htmlspecialchars($detalle['cantidad'], ENT_QUOTES, 'UTF-8'); ?> x $<?php echo number_format($precio_total_pizza, 2); ?>
                <?php endforeach; ?>

                Total: $<?php echo number_format($pedido['total'], 2); ?>
            `;

            // Formatear el número de teléfono
            const telefono = formatearNumeroWhatsApp("<?php echo htmlspecialchars($cliente['telefono'], ENT_QUOTES, 'UTF-8'); ?>");

            // Generar el enlace de WhatsApp
            const mensaje = encodeURIComponent(resumen);
            window.open(`https://wa.me/${telefono}?text=${mensaje}`, '_blank');
        }
    </script>
</body>
</html>
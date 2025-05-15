<?php
session_start();
include __DIR__ . '/../includes/config.php';
include __DIR__ . '/../includes/db.php';
include __DIR__ . '/../includes/functions.php';

// --- Validación de sesión y rol ---
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'vendedor') {
    $_SESSION['error'] = "Acceso denegado. Inicie sesión como vendedor.";
    header('Location: ' . BASE_URL . '/controllers/login.php');
    exit();
}

// --- Sanitización de inputs ---
$pedido_id = filter_input(INPUT_POST, 'pedido_id', FILTER_VALIDATE_INT);
$cliente_id = filter_input(INPUT_POST, 'cliente_id', FILTER_VALIDATE_INT);
$nombre_cliente = filter_input(INPUT_POST, 'nombre_cliente', FILTER_SANITIZE_STRING);
$requiere_delivery = isset($_POST['requiere_delivery']) ? 1 : 0;
$direccion_entrega = $requiere_delivery ? filter_input(INPUT_POST, 'direccion', FILTER_SANITIZE_STRING) : null;
$delivery_id = $requiere_delivery ? filter_input(INPUT_POST, 'delivery_id', FILTER_VALIDATE_INT) : null;
$pizzero_id = filter_input(INPUT_POST, 'pizzero_id', FILTER_VALIDATE_INT);
$actualizar_direccion = isset($_POST['actualizar_direccion_cliente']);

// --- Validación de campos obligatorios ---
if (empty($pedido_id) || empty($nombre_cliente) || empty($pizzero_id)) {
    $_SESSION['error'] = "Error: Faltan campos obligatorios.";
    header('Location: ' . BASE_URL . '/views/error.php');
    exit();
}

if ($requiere_delivery && (empty($direccion_entrega) || empty($delivery_id))) {
    $_SESSION['error'] = "Error: Se requiere dirección y repartidor para delivery.";
    header('Location: ' . BASE_URL . '/views/error.php');
    exit();
}

try {
    // --- Inicio de transacción ---
    $conn->beginTransaction();

    // --- Actualización de pedido ---
    $stmt_pedido = $conn->prepare("
        UPDATE pedidos 
        SET estado = 'procesado',
            requiere_delivery = ?,
            direccion_entrega = ?
        WHERE id = ?
    ");
    $stmt_pedido->execute([$requiere_delivery, $direccion_entrega, $pedido_id]);

    // --- Actualización de dirección del cliente (si aplica) ---
    if ($requiere_delivery && ($actualizar_direccion || empty($direccion_actual))) {
        $stmt_cliente = $conn->prepare("UPDATE clientes SET direccion = ? WHERE id = ?");
        $stmt_cliente->execute([$direccion_entrega, $cliente_id]);
    }

    // --- Registro en ventas ---
    $stmt_venta = $conn->prepare("
        INSERT INTO ventas (pedido_id, delivery_id, pizzero_id, fecha_venta)
        VALUES (?, ?, ?, NOW())
    ");
    $stmt_venta->execute([$pedido_id, $delivery_id, $pizzero_id]);

    // --- Obtener datos para notificaciones ---
    $pedido = obtenerDatosNotificacion($conn, $pedido_id);
    $items = obtenerDetallesPedido($conn, $pedido_id);

    // --- Validar teléfonos ---
    $telefonoPizzero = formatearTelefonoWhatsApp($pedido['pizzero_telefono']);
    $telefonoDelivery = $pedido['requiere_delivery'] 
        ? formatearTelefonoWhatsApp($pedido['delivery_telefono'])
        : null;

    if (!preg_match('/^[0-9]{10,12}$/', $telefonoPizzero)) {
        throw new Exception("Teléfono del pizzero no válido");
    }

    // --- Preparar notificaciones ---
    $_SESSION['notificaciones'] = [
        'pizzero' => [
            'telefono' => $telefonoPizzero,
            'mensaje' => generarMensajePizzero($pedido, $items)
        ],
        'delivery' => $pedido['requiere_delivery'] ? [
            'telefono' => $telefonoDelivery,
            'mensaje' => generarMensajeDelivery($pedido)
        ] : null,
        'pedido_id' => $pedido_id
    ];

    // --- Confirmar TODOS los cambios en BD antes de redirigir ---
    $conn->commit();

    // --- Redirección única ---
    header('Location: ' . BASE_URL . '/views/notificaciones.php');
    exit();

} catch (PDOException $e) {
    // --- Rollback en caso de error ---
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Error en pedido #$pedido_id: " . $e->getMessage());
    $_SESSION['error'] = "Error al procesar. Por favor reintente.";
    header('Location: ' . BASE_URL . '/views/error.php');
    exit();
}

// --- Funciones auxiliares ---
function obtenerDatosNotificacion($conn, $pedido_id) {
    $stmt = $conn->prepare("
        SELECT p.*, c.nombre AS cliente_nombre, c.telefono AS cliente_telefono,
               d.telefono AS delivery_telefono, pz.telefono AS pizzero_telefono
        FROM ventas v
        JOIN pedidos p ON v.pedido_id = p.id
        JOIN clientes c ON p.cliente_id = c.id
        LEFT JOIN delivery d ON v.delivery_id = d.usuario_id
        JOIN pizzero pz ON v.pizzero_id = pz.usuario_id
        WHERE v.pedido_id = ?
    ");
    $stmt->execute([$pedido_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function obtenerDetallesPedido($conn, $pedido_id) {
    $stmt = $conn->prepare("
        SELECT dp.cantidad, pz.nombre AS pizza_nombre, pz.tamaño,
               GROUP_CONCAT(t.nombre SEPARATOR ', ') AS toppings
        FROM detalles_pedido dp
        JOIN pizzas pz ON dp.pizza_id = pz.id
        LEFT JOIN toppings_pedido tp ON dp.id = tp.detalle_pedido_id
        LEFT JOIN toppings t ON tp.topping_id = t.id
        WHERE dp.pedido_id = ?
        GROUP BY dp.id
    ");
    $stmt->execute([$pedido_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function generarMensajePizzero($pedido, $items) {
    $mensaje = "🍕 *PEDIDO #{$pedido['id']}*\nCliente: {$pedido['cliente_nombre']}\n\n";
    foreach ($items as $item) {
        $mensaje .= "➡️ {$item['cantidad']}x {$item['pizza_nombre']} ({$item['tamaño']})";
        if (!empty($item['toppings'])) $mensaje .= "\n  🧀 {$item['toppings']}";
        $mensaje .= "\n";
    }
    return $mensaje . "\n🕒 Preparar inmediatamente";
}

function generarMensajeDelivery($pedido) {
    return "🚴 *ENTREGA #{$pedido['id']}*\n"
         . "👤 {$pedido['cliente_nombre']}\n"
         . "📞 {$pedido['cliente_telefono']}\n"
         . "📍 {$pedido['direccion_entrega']}\n\n"
         . "⚠️ Confirmar entrega";
}
?>
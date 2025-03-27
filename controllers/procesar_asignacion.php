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

// Validar y sanitizar los datos del formulario
$pedido_id = filter_input(INPUT_POST, 'pedido_id', FILTER_SANITIZE_NUMBER_INT);
$telefono = filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_STRING);
$nombre_cliente = filter_input(INPUT_POST, 'nombre_cliente', FILTER_SANITIZE_STRING);
$requiere_delivery = isset($_POST['requiere_delivery']) ? 1 : 0;
$direccion = $requiere_delivery ? filter_input(INPUT_POST, 'direccion', FILTER_SANITIZE_STRING) : null;
$delivery_id = filter_input(INPUT_POST, 'delivery_id', FILTER_SANITIZE_NUMBER_INT);
$pizzero_id = filter_input(INPUT_POST, 'pizzero_id', FILTER_SANITIZE_NUMBER_INT);

// Verificar que todos los campos obligatorios estén presentes
if (empty($pedido_id) || empty($telefono) || empty($nombre_cliente) || empty($delivery_id) || empty($pizzero_id)) {
    echo "Error: Todos los campos son obligatorios.";
    exit();
}

try {
    // Verificar si el cliente ya existe
    $stmt = $conn->prepare("SELECT id FROM clientes WHERE telefono = ?");
    $stmt->execute([$telefono]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cliente) {
        // Crear un nuevo cliente
        $stmt = $conn->prepare("INSERT INTO clientes (nombre, telefono) VALUES (?, ?)");
        $stmt->execute([$nombre_cliente, $telefono]);
        $cliente_id = $conn->lastInsertId();
    } else {
        $cliente_id = $cliente['id'];
    }

    // Actualizar el pedido con la dirección y el estado de delivery
    $stmt = $conn->prepare("UPDATE pedidos SET direccion_entrega = ?, requiere_delivery = ? WHERE id = ?");
    $stmt->execute([$direccion, $requiere_delivery, $pedido_id]);

    // Insertar en la tabla ventas
    $stmt = $conn->prepare("INSERT INTO ventas (pedido_id, delivery_id, pizzero_id) VALUES (?, ?, ?)");
    $stmt->execute([$pedido_id, $delivery_id, $pizzero_id]);

    // Redirigir a la lista de pedidos
    header('Location: ' . BASE_URL . '/views/pedidos.php');
    exit();
} catch (PDOException $e) {
    echo "Error al procesar la asignación: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}
?>
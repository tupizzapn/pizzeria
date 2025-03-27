<?php
session_start();
include __DIR__ . '/../includes/config.php'; // Incluir config.php

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/controllers/login.php'); // Redirigir al login
    exit();
}

// Verificar si el usuario tiene el rol de vendedor
if ($_SESSION['rol'] !== 'vendedor') {
    echo "Acceso denegado. Solo los vendedores pueden agregar direcciones.";
    exit();
}

include __DIR__ . '/../includes/db.php'; // Incluir conexión a la base de datos

// Validar y sanitizar los datos del formulario
$pedido_id = filter_input(INPUT_POST, 'pedido_id', FILTER_SANITIZE_NUMBER_INT);
$direccion = filter_input(INPUT_POST, 'direccion', FILTER_SANITIZE_STRING);

// Verificar que todos los campos estén presentes
if (empty($pedido_id) || empty($direccion)) {
    echo "Error: Todos los campos son obligatorios.";
    exit();
}

try {
    // Actualizar la dirección de entrega en la base de datos
    $stmt = $conn->prepare("UPDATE pedidos SET direccion_entrega = ? WHERE id = ?");
    $stmt->execute([$direccion, $pedido_id]);

    // Redirigir a la lista de pedidos
    header('Location: ' . BASE_URL . '/views/pedidos.php');
    exit();
} catch (PDOException $e) {
    // Mostrar un mensaje de error detallado
    echo "Error al guardar la dirección: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}
?>
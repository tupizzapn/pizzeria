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
    echo "Acceso denegado. Solo los vendedores pueden cambiar el estado de los pedidos.";
    exit();
}

// Validar y sanitizar los datos de la URL (GET)
$pedido_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$estado = filter_input(INPUT_GET, 'estado', FILTER_SANITIZE_STRING);

// Verificar que todos los campos estén presentes
if (empty($pedido_id) || empty($estado)) {
    echo "Error: Todos los campos son obligatorios.";
    exit();
}

// Redirigir a la vista de asignación
header('Location: ' . BASE_URL . '/views/asignar_delivery.php?id=' . $pedido_id);
exit();
?>
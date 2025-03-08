<?php
session_start();
include __DIR__ . '/../includes/config.php'; // Incluir config.php

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/controllers/login.php');
    exit();
}

// Verificar si el usuario tiene el rol de administrador
if ($_SESSION['rol'] !== 'admin') {
    echo "Acceso denegado. Solo los administradores pueden eliminar usuarios.";
    exit();
}

include __DIR__ . '/../includes/db.php'; // Incluir conexión a la base de datos

// Validar y sanitizar el ID del usuario
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$id) {
    echo "<h1>Error: ID de usuario no válido.</h1>";
    exit();
}

try {
    // Eliminar el usuario de la base de datos
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);

    // Redirigir a la página de gestión de usuarios
    header('Location: ' . BASE_URL . '/controllers/gestionar_usuarios.php');
    exit();
} catch (PDOException $e) {
    // Mostrar un mensaje de error detallado
    echo "<h1>Error al eliminar usuario</h1>";
    echo "<p>Por favor, intenta nuevamente. Si el problema persiste, contacta al soporte.</p>";
    echo "<p>Detalles del error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>";
}
?>

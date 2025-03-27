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
    echo "Acceso denegado. Solo los administradores pueden gestionar usuarios.";
    exit();
}

include __DIR__ . '/../includes/db.php'; // Incluir conexión a la base de datos


if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['username'])) {
    $username = filter_input(INPUT_GET, 'username', FILTER_SANITIZE_STRING);
    try {
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE username = ?");
        $stmt->execute([$username]);
        $existe = $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
        echo json_encode(['existe' => $existe]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al verificar el usuario.']);
    }
    exit();
}
?>


<?php
session_start();
header('Content-Type: application/json');

include __DIR__ . '/../includes/config.php';
include __DIR__ . '/../includes/db.php';

// Verificación de sesión y rol
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    http_response_code(403);
    die(json_encode(['error' => 'Acceso no autorizado']));
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['username'])) {
    try {
        $username = filter_input(INPUT_GET, 'username', FILTER_SANITIZE_STRING);
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE username = ?");
        $stmt->execute([$username]);
        
        echo json_encode([
            'existe' => $stmt->fetch(PDO::FETCH_ASSOC) ? true : false
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error en la base de datos']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Solicitud inválida']);
}
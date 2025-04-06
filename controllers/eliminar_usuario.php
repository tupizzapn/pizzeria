<?php
session_start();
include __DIR__ . '/../includes/config.php';
include __DIR__ . '/../includes/db.php';

// Verificación de sesión y rol
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ' . BASE_URL . '/controllers/login.php');
    exit();
}

// Validar y sanitizar el ID del usuario
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$id) {
    $_SESSION['error'] = "ID de usuario no válido";
    header('Location: ' . BASE_URL . '/controllers/gestionar_usuarios.php');
    exit();
}

try {
    // Verificar si el usuario está siendo usado en otras tablas
    $usadoEnOtrasTablas = false;
    $tablasReferenciadas = [];
    
    // Verificar en tabla pizzero
    $stmt = $conn->prepare("SELECT COUNT(*) FROM pizzero WHERE usuario_id = ?");
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() > 0) {
        $usadoEnOtrasTablas = true;
        $tablasReferenciadas[] = 'pizzero';
    }
    
    // Verificar en tabla delivery
    $stmt = $conn->prepare("SELECT COUNT(*) FROM delivery WHERE usuario_id = ?");
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() > 0) {
        $usadoEnOtrasTablas = true;
        $tablasReferenciadas[] = 'delivery';
    }
    
    // Verificar en tabla ventas
    $stmt = $conn->prepare("SELECT COUNT(*) FROM ventas WHERE pizzero_id = ? OR delivery_id = ?");
    $stmt->execute([$id, $id]);
    if ($stmt->fetchColumn() > 0) {
        $usadoEnOtrasTablas = true;
        $tablasReferenciadas[] = 'ventas';
    }
    
    // Realizar borrado lógico
    $stmt = $conn->prepare("UPDATE usuarios SET activo = FALSE, fecha_eliminacion = NOW() WHERE id = ?");
    $stmt->execute([$id]);
    
    if ($usadoEnOtrasTablas) {
        $_SESSION['advertencia'] = "Usuario marcado como inactivo. Referencias encontradas en: " . 
                                 implode(', ', $tablasReferenciadas) . 
                                 ". Los registros históricos se mantienen.";
    } else {
        $_SESSION['exito'] = "Usuario marcado como inactivo correctamente";
    }
    
    header('Location: ' . BASE_URL . '/controllers/gestionar_usuarios.php');
    exit();

} catch (PDOException $e) {
    $_SESSION['error'] = "Error al desactivar usuario: " . $e->getMessage();
    header('Location: ' . BASE_URL . '/controllers/gestionar_usuarios.php');
    exit();
}
?>
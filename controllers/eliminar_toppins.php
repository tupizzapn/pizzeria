<?php
// Inicio seguro de sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/db.php';

// Verificación de sesión y rol
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ' . BASE_URL . '/controllers/login.php');
    exit();
}

// Validar ID del topping
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['error'] = "ID de topping no válido";
    header('Location: ' . BASE_URL . '/controllers/gestionar_toppins.php');
    exit();
}

try {
    // Verificar si el topping está siendo usado (solo para mensaje informativo)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM toppings_pedido WHERE topping_id = ?");
    $stmt->execute([$id]);
    $enUso = $stmt->fetchColumn() > 0;

    // Siempre permitir eliminación lógica (igual que con pizzas)
    $stmt = $conn->prepare(
        "UPDATE toppings SET 
         activo = FALSE, 
         fecha_eliminacion = NOW() 
         WHERE id = ?"
    );
    $stmt->execute([$id]);

    // Mensaje apropiado según uso
    if ($enUso) {
        $_SESSION['advertencia'] = "Topping marcado como inactivo. Se mantendrá en pedidos históricos.";
    } else {
        $_SESSION['exito'] = "Topping desactivado correctamente";
    }

} catch (PDOException $e) {
    $_SESSION['error'] = "Error al desactivar topping: " . $e->getMessage();
}

header('Location: ' . BASE_URL . '/controllers/gestionar_toppins.php');
exit();
?>
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

// Validar ID de la pizza
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['error'] = "ID de pizza no válido";
    header('Location: ' . BASE_URL . '/controllers/gestionar_pizzas.php');
    exit();
}

try {
    // Verificar referencias en otras tablas
    $tablasReferenciadas = [];
    
    // Verificar en detalles_pedido
    $stmt = $conn->prepare("SELECT COUNT(*) FROM detalles_pedido WHERE pizza_id = ?");
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() > 0) {
        $tablasReferenciadas[] = 'pedidos';
    }
    
    // Verificar en toppings_pedido (si hay relación directa)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM toppings_pedido tp 
                           JOIN detalles_pedido dp ON tp.detalle_pedido_id = dp.id 
                           WHERE dp.pizza_id = ?");
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() > 0) {
        $tablasReferenciadas[] = 'toppings de pedidos';
    }
    
    // Verificar en ventas (si aplica)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM ventas WHERE pizzero_id = ?");
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() > 0) {
        $tablasReferenciadas[] = 'ventas';
    }

    // Eliminación lógica
    $stmt = $conn->prepare(
        "UPDATE pizzas SET 
         activo = FALSE, 
         fecha_eliminacion = NOW() 
         WHERE id = ?"
    );
    $stmt->execute([$id]);

    // Mensaje apropiado según referencias
    if (!empty($tablasReferenciadas)) {
        $_SESSION['advertencia'] = sprintf(
            "Pizza marcada como inactiva. Existen referencias en: %s. Los registros históricos se mantendrán.",
            implode(', ', array_unique($tablasReferenciadas))
        );
    } else {
        $_SESSION['exito'] = "Pizza desactivada correctamente";
    }

} catch (PDOException $e) {
    $_SESSION['error'] = "Error al desactivar pizza: " . $e->getMessage();
}

header('Location: ' . BASE_URL . '/controllers/gestionar_pizzas.php');
exit();
?>
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
    echo "Acceso denegado. Solo los administradores pueden editar toppings.";
    exit();
}

include __DIR__ . '/../includes/db.php'; // Incluir conexión a la base de datos

// Procesar el formulario de edición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar y sanitizar los datos del formulario
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
    $precio_familiar = filter_input(INPUT_POST, 'precio_familiar', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $precio_pequeña = filter_input(INPUT_POST, 'precio_pequeña', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

    // Verificar que todos los campos estén presentes y no estén vacíos
    if (empty($id) || empty($nombre) || empty($precio_familiar) || empty($precio_pequeña)) {
        echo "<h1>Error: Todos los campos son obligatorios.</h1>";
        exit();
    }

    try {
        // Actualizar el topping en la base de datos
        $stmt = $conn->prepare("UPDATE toppings SET nombre = ?, precio_familiar = ?, precio_pequeña = ? WHERE id = ?");
        $stmt->execute([$nombre, $precio_familiar, $precio_pequeña, $id]);

        // Redirigir a la página de gestión de toppings
        header('Location: ' . BASE_URL . '/controllers/gestionar.php');
        exit();
    } catch (PDOException $e) {
        // Mostrar un mensaje de error detallado
        echo "<h1>Error al editar topping</h1>";
        echo "<p>Por favor, intenta nuevamente. Si el problema persiste, contacta al soporte.</p>";
        echo "<p>Detalles del error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>";
    }
}

// Obtener el ID del topping desde la URL
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$id) {
    echo "<h1>Error: ID de topping no válido.</h1>";
    exit();
}

// Obtener los datos del topping
try {
    $stmt = $conn->prepare("SELECT * FROM toppings WHERE id = ?");
    $stmt->execute([$id]);
    $topping = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$topping) {
        echo "<h1>Error: No se encontró el topping.</h1>";
        exit();
    }
} catch (PDOException $e) {
    echo "<h1>Error al obtener los datos del topping</h1>";
    echo "<p>Por favor, intenta nuevamente. Si el problema persiste, contacta al soporte.</p>";
    echo "<p>Detalles del error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Topping</title>
    <link rel="stylesheet" href="<?php echo CSS_DIR; ?>/styles.css"> <!-- Ruta corregida -->
</head>
<body>
    <h1>Editar Topping</h1>
    <form action="<?php echo BASE_URL; ?>/controllers/editar_topping.php" method="POST">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($topping['id'], ENT_QUOTES, 'UTF-8'); ?>">
        
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($topping['nombre'], ENT_QUOTES, 'UTF-8'); ?>" required>

        <label for="precio_familiar">Precio Familiar:</label>
        <input type="number" id="precio_familiar" name="precio_familiar" step="0.01" value="<?php echo htmlspecialchars($topping['precio_familiar'], ENT_QUOTES, 'UTF-8'); ?>" required>

        <label for="precio_pequeña">Precio Pequeña:</label>
        <input type="number" id="precio_pequeña" name="precio_pequeña" step="0.01" value="<?php echo htmlspecialchars($topping['precio_pequeña'], ENT_QUOTES, 'UTF-8'); ?>" required>

        <button type="submit">Guardar Cambios</button>
    </form>
</body>
</html>

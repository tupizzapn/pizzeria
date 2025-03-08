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
    echo "Acceso denegado. Solo los administradores pueden editar pizzas.";
    exit();
}

include __DIR__ . '/../includes/db.php'; // Incluir conexión a la base de datos

// Procesar el formulario de edición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar y sanitizar los datos del formulario
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
    $tamaño = filter_input(INPUT_POST, 'tamaño', FILTER_SANITIZE_STRING);
    $precio = filter_input(INPUT_POST, 'precio', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

    // Verificar que todos los campos estén presentes y no estén vacíos
    if (empty($id) || empty($nombre) || empty($tamaño) || empty($precio)) {
        echo "<h1>Error: Todos los campos son obligatorios.</h1>";
        exit();
    }

    try {
        // Actualizar la pizza en la base de datos
        $stmt = $conn->prepare("UPDATE pizzas SET nombre = ?, tamaño = ?, precio = ? WHERE id = ?");
        $stmt->execute([$nombre, $tamaño, $precio, $id]);

        // Redirigir a la página de gestión de pizzas
        header('Location: ' . BASE_URL . '/controllers/gestionar.php');
        exit();
    } catch (PDOException $e) {
        // Mostrar un mensaje de error detallado
        echo "<h1>Error al editar pizza</h1>";
        echo "<p>Por favor, intenta nuevamente. Si el problema persiste, contacta al soporte.</p>";
        echo "<p>Detalles del error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>";
    }
}

// Obtener el ID de la pizza desde la URL
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$id) {
    echo "<h1>Error: ID de pizza no válido.</h1>";
    exit();
}

// Obtener los datos de la pizza
try {
    $stmt = $conn->prepare("SELECT * FROM pizzas WHERE id = ?");
    $stmt->execute([$id]);
    $pizza = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pizza) {
        echo "<h1>Error: No se encontró la pizza.</h1>";
        exit();
    }
}
catch (PDOException $e) {
    echo "<h1>Error al obtener los datos de la pizza</h1>";
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
    <title>Editar Pizza</title>
    <link rel="stylesheet" href="<?php echo CSS_DIR; ?>/styles.css"> <!-- Ruta corregida -->
</head>
<body>
    <h1>Editar Pizza</h1>
    <form action="<?php echo BASE_URL; ?>/controllers/editar_pizza.php" method="POST">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($pizza['id'], ENT_QUOTES, 'UTF-8'); ?>">
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($pizza['nombre'], ENT_QUOTES, 'UTF-8'); ?>" required>

        <label for="tamaño">Tamaño:</label>
        <select id="tamaño" name="tamaño" required>
            <option value="Familiar" <?php echo ($pizza['tamaño'] === 'Familiar') ? 'selected' : ''; ?>>Familiar</option>
            <option value="Pequeña" <?php echo ($pizza['tamaño'] === 'Pequeña') ? 'selected' : ''; ?>>Pequeña</option>
        </select>

        <label for="precio">Precio:</label>
        <input type="number" id="precio" name="precio" step="0.01" value="<?php echo htmlspecialchars($pizza['precio'], ENT_QUOTES, 'UTF-8'); ?>" required>

        <button type="submit">Guardar Cambios</button>
    </form>
</body>
</html>
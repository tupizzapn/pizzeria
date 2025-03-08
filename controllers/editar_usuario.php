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
    echo "Acceso denegado. Solo los administradores pueden editar usuarios.";
    exit();
}

include __DIR__ . '/../includes/db.php'; // Incluir conexión a la base de datos

// Procesar el formulario de edición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar y sanitizar los datos del formulario
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $rol = filter_input(INPUT_POST, 'rol', FILTER_SANITIZE_STRING);

    // Verificar que todos los campos estén presentes y no estén vacíos
    if (empty($id) || empty($username) || empty($rol)) {
        $error = "Error: Todos los campos son obligatorios.";
    } else {
        try {
            // Actualizar el usuario en la base de datos
            $stmt = $conn->prepare("UPDATE usuarios SET username = ?, rol = ? WHERE id = ?");
            $stmt->execute([$username, $rol, $id]);

            // Redirigir a la página de gestión de usuarios
            header('Location: ' . BASE_URL . '/controllers/gestionar_usuarios.php');
            exit();
        } catch (PDOException $e) {
            $error = "Error al editar usuario: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
    }
}

// Obtener el ID del usuario desde la URL
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$id) {
    echo "<h1>Error: ID de usuario no válido.</h1>";
    exit();
}

// Obtener los datos del usuario
try {
    $stmt = $conn->prepare("SELECT id, username, rol FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        echo "<h1>Error: No se encontró el usuario.</h1>";
        exit();
    }
} catch (PDOException $e) {
    echo "<h1>Error al obtener los datos del usuario</h1>";
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
    <title>Editar Usuario</title>
    <link rel="stylesheet" href="<?php echo CSS_DIR; ?>/styles.css"> <!-- Ruta corregida -->
</head>
<body>
    <h1>Editar Usuario</h1>
    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    <form action="<?php echo BASE_URL; ?>/controllers/editar_usuario.php" method="POST">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($usuario['id'], ENT_QUOTES, 'UTF-8'); ?>">
        
        <label for="username">Usuario:</label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($usuario['username'], ENT_QUOTES, 'UTF-8'); ?>" required>

        <label for="rol">Rol:</label>
        <select id="rol" name="rol" required>
            <option value="admin" <?php echo ($usuario['rol'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
            <option value="vendedor" <?php echo ($usuario['rol'] === 'vendedor') ? 'selected' : ''; ?>>Vendedor</option>
        </select>

        <button type="submit">Guardar Cambios</button>
    </form>
</body>
</html>
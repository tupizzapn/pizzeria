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

// Procesar formulario de agregar usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_usuario'])) {
    // Validar y sanitizar los datos del formulario
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
    $rol = filter_input(INPUT_POST, 'rol', FILTER_SANITIZE_STRING);

    // Verificar que todos los campos estén presentes y no estén vacíos
    if (empty($username) || empty($password) || empty($rol)) {
        $error = "Error: Todos los campos son obligatorios.";
    } else {
        // Hash de la contraseña
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        try {
            // Insertar el nuevo usuario en la base de datos
            $stmt = $conn->prepare("INSERT INTO usuarios (username, password, rol) VALUES (?, ?, ?)");
            $stmt->execute([$username, $passwordHash, $rol]);

            // Redirigir para evitar reenvío del formulario
            header('Location: ' . BASE_URL . '/controllers/gestionar_usuarios.php');
            exit();
        } catch (PDOException $e) {
            $error = "Error al agregar usuario: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
    }
}

// Obtener la lista de usuarios
try {
    $usuarios = $conn->query("SELECT id, username, rol FROM usuarios")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al obtener la lista de usuarios: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Usuarios</title>
    <link rel="stylesheet" href="<?php echo CSS_DIR; ?>/styles.css"> <!-- Ruta corregida -->
</head>
<body>
    <h1>Gestionar Usuarios</h1>
    <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?>! <a href="<?php echo BASE_URL; ?>/controllers/logout.php">Cerrar sesión</a></p>

    <!-- Mostrar errores -->
    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Formulario para agregar usuarios -->
    <h2>Agregar Usuario</h2>
    <form action="<?php echo BASE_URL; ?>/controllers/gestionar_usuarios.php" method="POST">
        <label for="username">Usuario:</label>
        <input type="text" id="username" name="username" required>

        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" required>

        <label for="rol">Rol:</label>
        <select id="rol" name="rol" required>
            <option value="admin">Admin</option>
            <option value="vendedor">Vendedor</option>
        </select>

        <button type="submit" name="agregar_usuario">Agregar Usuario</button>
    </form>

    <!-- Lista de usuarios -->
    <h2>Lista de Usuarios</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Rol</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($usuarios)): ?>
                <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($usuario['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($usuario['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($usuario['rol'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>/controllers/editar_usuario.php?id=<?php echo $usuario['id']; ?>">Editar</a>
                            <a href="<?php echo BASE_URL; ?>/controllers/eliminar_usuario.php?id=<?php echo $usuario['id']; ?>" onclick="return confirm('¿Estás seguro de eliminar este usuario?');">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">No hay usuarios registrados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
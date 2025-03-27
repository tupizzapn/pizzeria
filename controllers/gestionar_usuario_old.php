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
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);

    try {
        // Verificar si el usuario ya existe
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE username = ?");
        $stmt->execute([$username]);
        $usuario_existente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario_existente) {
            $error = "Error: El nombre de usuario ya está registrado.";
        } else {
            // Insertar el nuevo usuario
            $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
            $rol = filter_input(INPUT_POST, 'rol', FILTER_SANITIZE_STRING);

            if (empty($username) || empty($password) || empty($rol)) {
                $error = "Error: Todos los campos son obligatorios.";
            } else {
                $passwordHash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("INSERT INTO usuarios (username, password, rol) VALUES (?, ?, ?)");
                $stmt->execute([$username, $passwordHash, $rol]);
                $usuario_id = $conn->lastInsertId();

                // Insertar información adicional si es necesario
                if ($rol === 'pizzero' || $rol === 'delivery') {
                    $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
                    $telefono = filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_STRING);

                    if (empty($nombre) || empty($telefono)) {
                        $error = "Error: Nombre y teléfono son obligatorios para pizeros y repartidores.";
                    } else {
                        if ($rol === 'pizzero') {
                            $stmt = $conn->prepare("INSERT INTO pizzero (usuario_id, nombre, telefono) VALUES (?, ?, ?)");
                        } else {
                            $stmt = $conn->prepare("INSERT INTO delivery (usuario_id, nombre, telefono) VALUES (?, ?, ?)");
                        }
                        $stmt->execute([$usuario_id, $nombre, $telefono]);
                    }
                }

                // Redirigir para evitar reenvío del formulario
                header('Location: ' . BASE_URL . '/controllers/gestionar_usuarios.php');
                exit();
            }
        }
    } catch (PDOException $e) {
        $error = "Error al verificar o agregar usuario: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
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
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/styles.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Indie+Flower&family=Lugrasimo&display=swap" rel="stylesheet">
    <style>
    .contenedor_tabla {
        background-image: url('<?php echo IMG_URL; ?>/backgrounds/background_adm.jpg');
    }
    </style>
    <script>
    // Define la ruta base para la API
    const API_BASE_URL = '<?php echo BASE_URL; ?>/controllers';
</script>
</head>
<body>
    <div class="contenedor_tabla">
        <div class="tabla">
            <div class="titulo_font">
            <h1>Gestionar Usuarios</h1>
            <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?>! <a href="<?php echo BASE_URL; ?>/controllers/logout.php">Cerrar sesión</a></p>
            <a href="<?php echo BASE_URL; ?>/views/index.php">Inicio</a>
            <!-- Mostrar errores -->
            <?php if (isset($error)): ?>
                <div class="alert alert-error">gestionar_<?php echo $error; ?></div>
                <?php endif; ?>
            </div>

    <!-- Formulario para agregar usuarios -->
    <div class="nuevo_ingreso">
    <div><h2 class="titulo_font">Agregar Usuario</h2></div>
        <div>
            <form action="<?php echo BASE_URL; ?>/controllers/gestionar_usuarios.php" method="POST">
            <label for="username">Usuario:</label>
            <input type="text" id="username" name="username" required>
            <div id="username-error" style="display: none; color: red; margin-top: 5px;"></div>
            <label for="rol">Rol:</label>
        <select id="rol" name="rol" required>
            <option value="admin">Admin</option>
            <option value="vendedor">Vendedor</option>
            <option value="pizzero">Pizzero</option>
            <option value="delivery">Delivery</option>
        </select>
        </div>    
        <div>
        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" required>
        </div>       

       

        <!-- Campos adicionales para pizzero y delivery -->
        <div id="campos-adicionales" style="display: none;">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre">

            <label for="telefono">Teléfono:</label>
            <input type="text" id="telefono" name="telefono">
        </div>
        <div>
        <button type="submit" name="agregar_usuario">Agregar Usuario</button>
        </div>
    </form>
    <script src="<?php echo JS_URL; ?>/gestion_usuario.js"></script>
    </div> 

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

    <!-- Script para mostrar/ocultar campos adicionales -->
    <script>
        document.getElementById('rol').addEventListener('change', function() {
            const camposAdicionales = document.getElementById('campos-adicionales');
            if (this.value === 'pizzero' || this.value === 'delivery') {
                camposAdicionales.style.display = 'block';
            } else {
                camposAdicionales.style.display = 'none';
            }
        });
    </script>
    </div>
    </div>
</body>
</html>
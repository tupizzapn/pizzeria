<?php
// Inicio seguro de sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir archivos de configuración
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/db.php';

// Verificación de sesión y rol
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/controllers/login.php');
    exit();
}

if ($_SESSION['rol'] !== 'admin') {
    die("Acceso denegado. Solo los administradores pueden editar usuarios.");
}

// Procesar el formulario de edición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING));
    $rol = filter_input(INPUT_POST, 'rol', FILTER_SANITIZE_STRING);
    $nombre = trim(filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING));
    $telefono = trim(filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_STRING));

    // Validaciones
    $errores = [];
    
    if (empty($id)) $errores[] = "ID de usuario inválido";
    if (empty($username) || strlen($username) < 3) $errores[] = "Usuario debe tener al menos 3 caracteres";
    if (empty($nombre)) $errores[] = "Nombre completo es requerido";
    if (empty($telefono) || !preg_match('/^[0-9]{9,15}$/', $telefono)) {
        $errores[] = "Teléfono debe tener 9-15 dígitos";
    }

    if (empty($errores)) {
        try {
            // Verificar si el usuario ya existe (excluyendo el actual)
            $stmt = $conn->prepare("SELECT id FROM usuarios WHERE username = ? AND id != ?");
            $stmt->execute([$username, $id]);
            
            if ($stmt->fetch()) {
                $errores[] = "El nombre de usuario ya está en uso";
            } else {
                // Actualizar en la base de datos
                $stmt = $conn->prepare("UPDATE usuarios SET username=?, rol=?, nombre=?, telefono=? WHERE id=?");
                $stmt->execute([$username, $rol, $nombre, $telefono, $id]);
                
                header('Location: ' . BASE_URL . '/controllers/gestionar_usuarios.php?mostrar=lista');
                exit();
            }
        } catch (PDOException $e) {
            $errores[] = "Error de base de datos: " . $e->getMessage();
        }
    }
}

// Obtener datos del usuario a editar
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) die("ID de usuario no válido");

try {
    $stmt = $conn->prepare("SELECT id, username, rol, nombre, telefono FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) die("Usuario no encontrado");
} catch (PDOException $e) {
    die("Error al obtener usuario: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario - Sistema de Pizzería</title>
    <link rel="stylesheet" href="<?= CSS_URL ?>/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Indie+Flower&family=Lugrasimo&display=swap" rel="stylesheet">
    
</head>
<body>
    <div class="contenedor_tabla">
        <div class="tabla">
            <div class="titulo_font">
                <h1>Editar Usuario</h1>
                <a href="<?= BASE_URL ?>/controllers/gestionar_usuarios.php" class="btn-regresar">← Regresar</a>
                
                <?php if (!empty($errores)): ?>
                    <div class="alert alert-error">
                        <?php foreach ($errores as $error): ?>
                            <p><?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="contenedor-edicion">
                <form id="formEditarUsuario" method="POST">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($usuario['id']) ?>">
                    
                    <div class="form-group">
                        <label for="username">Usuario:</label>
                        <input type="text" id="username" name="username" 
                               value="<?= htmlspecialchars($usuario['username']) ?>" 
                               required minlength="3">
                        <div class="error-msg" id="username-error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="nombre">Nombre Completo:</label>
                        <input type="text" id="nombre" name="nombre" 
                               value="<?= htmlspecialchars($usuario['nombre']) ?>" 
                               required minlength="3">
                    </div>
                    
                    <div class="form-group">
                        <label for="telefono">Teléfono:</label>
                        <input type="text" id="telefono" name="telefono" 
                               value="<?= htmlspecialchars($usuario['telefono']) ?>" 
                               required pattern="[0-9]{9,15}" 
                               title="9-15 dígitos numéricos">
                    </div>
                    
                    <div class="form-group">
                        <label for="rol">Rol:</label>
                        <select id="rol" name="rol" required>
                            <option value="admin" <?= $usuario['rol'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                            <option value="vendedor" <?= $usuario['rol'] === 'vendedor' ? 'selected' : '' ?>>Vendedor</option>
                            <option value="pizzero" <?= $usuario['rol'] === 'pizzero' ? 'selected' : '' ?>>Pizzero</option>
                            <option value="delivery" <?= $usuario['rol'] === 'delivery' ? 'selected' : '' ?>>Delivery</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn-guardar">Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Validación en tiempo real
        document.getElementById('telefono')?.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
        });

        // Validación de usuario único
        document.getElementById('username')?.addEventListener('blur', validarUsuario);
        
        async function validarUsuario() {
            const username = this.value.trim();
            const userId = document.querySelector('input[name="id"]').value;
            const errorElement = document.getElementById('username-error');
            const submitBtn = document.querySelector('button[type="submit"]');
            
            if (username.length < 3) {
                errorElement.textContent = 'Mínimo 3 caracteres';
                errorElement.style.display = 'block';
                return;
            }
            
            try {
                const response = await fetch(`<?= BASE_URL ?>/controllers/api.php?username=${encodeURIComponent(username)}&exclude_id=${userId}`);
                const data = await response.json();
                
                if (data.error) {
                    errorElement.textContent = data.error;
                    errorElement.style.display = 'block';
                    submitBtn.disabled = true;
                } else if (data.existe) {
                    errorElement.textContent = 'Usuario ya existe';
                    errorElement.style.display = 'block';
                    submitBtn.disabled = true;
                } else {
                    errorElement.style.display = 'none';
                    submitBtn.disabled = false;
                }
            } catch (error) {
                console.error('Error:', error);
                errorElement.textContent = 'Error al validar';
                errorElement.style.display = 'block';
            }
        }
    </script>
</body>
</html>
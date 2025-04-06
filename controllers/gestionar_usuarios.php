<?php
session_start();
include __DIR__ . '/../includes/config.php';
include __DIR__ . '/../includes/db.php';

// Verificación de sesión y rol
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ' . BASE_URL . '/controllers/login.php');
    exit();
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_usuario'])) {
    // Filtrado de inputs
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
    $rol = filter_input(INPUT_POST, 'rol', FILTER_SANITIZE_STRING);
    $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
    $telefono = filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_STRING);

    try {
        // Validación de campos obligatorios
        if (empty($username) || empty($password) || empty($rol) || empty($nombre) || empty($telefono)) {
            $error = "Todos los campos son obligatorios.";
        } 
        // Validación formato teléfono
        elseif (!preg_match('/^[0-9]{9,15}$/', $telefono)) {
            $error = "El teléfono debe contener solo números (9-15 dígitos).";
        } else {
            // Verificar usuario existente
            $stmt = $conn->prepare("SELECT id FROM usuarios WHERE username = ?");
            $stmt->execute([$username]);
            
            if ($stmt->fetch(PDO::FETCH_ASSOC)) {
                $error = "El nombre de usuario ya está registrado.";
            } else {
                // Insertar nuevo usuario
                $passwordHash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare(
                    "INSERT INTO usuarios (username, password, rol, nombre, telefono) 
                     VALUES (?, ?, ?, ?, ?)"
                );
                $stmt->execute([$username, $passwordHash, $rol, $nombre, $telefono]);
                
                // Redirigir para evitar reenvío
                header('Location: ' . BASE_URL . '/controllers/gestionar_usuarios.php');
                exit();
            }
        }
    } catch (PDOException $e) {
        $error = "Error en la base de datos: " . $e->getMessage();
    }
}

// Obtener lista de usuarios
try {
    // Cambia la consulta de usuarios a:
$usuarios = $conn->query(
    "SELECT id, username, rol, nombre, telefono 
     FROM usuarios 
     WHERE activo = TRUE
     ORDER BY created_at DESC"
)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al obtener usuarios: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Usuarios</title>
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Indie+Flower&family=Lugrasimo&display=swap" rel="stylesheet">
    <style>
    .contenedor_tabla {
        background-image: url('<?php echo IMG_URL; ?>/backgrounds/background_sm.jpg');
    }
    .tabla { 
        background-image: url('<?php echo IMG_URL; ?>/backgrounds/nota_sf.png');
    }

    </style>
    <script>
        const API_BASE_URL = '<?php echo BASE_URL; ?>/controllers';
    </script>
</head>
<body>
<div class="contenedor_tabla">
    <div class="tabla">
        <!-- Encabezado unificado -->
        <div class="titulo_font">
            <h1>Gestionar Usuarios</h1>
            <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?>!</p>
            
            <!-- Menú de navegación optimizado -->
            <div class="menu-navegacion">
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>/views/index.php">Regresar</a></li> 
                    <li><a href="#" id="btnNuevo">Nuevo ingreso</a></li> 
                    <li><a href="#" id="btnEditar">Editar Usuario</a></li> 
                </ul>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
        </div>

                  <!-- Formulario de Registro -->
                  <div class="nuevo_ingreso" style="display: none;">
            <h2>Agregar Usuario</h2>
            <form action="<?php echo htmlspecialchars(BASE_URL.'/controllers/gestionar_usuarios.php'); ?>" method="POST">
                                    <div>
                                                     <label for="username">Usuario:</label>
                                                     <input type="text" id="username" name="username" required 
                                                     autocomplete="off" 
                                                    readonly 
                                                    onfocus="this.removeAttribute('readonly')"
                                                    value="<?php echo htmlspecialchars('', ENT_QUOTES); ?>">
                                                 <div class="error-msg" id="username-error"></div>
                                    </div>
            
                                    <div>
                                            <label for="rol">Rol:</label>
                                            <select id="rol" name="rol" required 
                                                    autocomplete="off" 
                                                    readonly 
                                                   onfocus="this.removeAttribute('readonly')">
                                                    <option value="">Seleccione un rol</option>
                                                    <option value="admin">Admin</option>
                                                    <option value="vendedor">Vendedor</option>
                                                    <option value="pizzero">Pizzero</option>
                                                    <option value="delivery">Delivery</option>
                                            </select>
                                    </div>
            
                                    <div>
                                           <label for="password">Contraseña:</label>
                                            <input type="password" id="password" name="password" required 
                                             autocomplete="new-password"
                                             readonly 
                                             onfocus="this.removeAttribute('readonly')"
                                             value="<?php echo htmlspecialchars('', ENT_QUOTES); ?>">
                                    </div>
            
                                    <div>
                                        <label for="nombre">Nombre Completo:</label>
                                        <input type="text" id="nombre" name="nombre" required 
                                        autocomplete="off" 
                                        readonly 
                                        onfocus="this.removeAttribute('readonly')"
                                        value="<?php echo htmlspecialchars('', ENT_QUOTES); ?>">
                                    </div>
            
                                    <div>
                                         <label for="telefono">Teléfono:</label>
                                        <input type="text" id="telefono" name="telefono" required 
                                        pattern="[0-9]{9,15}" 
                                        title="9-15 dígitos numéricos" 
                                        autocomplete="off" 
                                        readonly 
                                        onfocus="this.removeAttribute('readonly')"
                                        value="<?php echo htmlspecialchars('', ENT_QUOTES); ?>">
                                    </div>
            
                                    <button type="submit" name="agregar_usuario">Agregar Usuario</button>
                             </form>
                </div>

            <!-- Lista de Usuarios -->
            <div class="lista-usuarios">
            <h2>Lista de Usuarios</h2>
            <table>
                      <thead>
                         <tr>
                            <th>Usuario</th>
                            <th>Rol</th>
                            <th>Nombre</th>
                            <th>Teléfono</th>
                            <th>Acciones</th>
                          </tr>
                     </thead>
                    <tbody>
                          <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                    <td><?php echo htmlspecialchars($usuario['username']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['rol']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['telefono']); ?></td>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>/controllers/editar_usuario.php?id=<?php echo $usuario['id']; ?>">Editar</a>
                                        <a href="<?php echo BASE_URL; ?>/controllers/eliminar_usuario.php?id=<?php echo $usuario['id']; ?>" 
                                         onclick="return confirm('¿Eliminar este usuario?')">Eliminar</a>
                                    </td>
                            </tr>
                     <?php endforeach; ?>
                    </tbody>
                </table>
        
            </div>
           
        </div>
     
    </div>
    
    <script src="<?php echo JS_URL; ?>/gestion_usuario.js"></script>
</body>
</html>
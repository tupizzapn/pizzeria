<?php
session_start();
include __DIR__ . '/../includes/config.php';
include __DIR__ . '/../includes/db.php';

// Generar token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Error de seguridad. Inténtalo de nuevo.";
    } else {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        try {
            $stmt = $conn->prepare("SELECT id, username, password, rol FROM usuarios WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['rol'] = $user['rol'];
                header('Location: ' . BASE_URL . '/views/index.php');
                exit();
            } else {
                $error = "Usuario o contraseña incorrectos.";
            }
        } catch (PDOException $e) {
            $error = "Error al iniciar sesión. Inténtalo de nuevo más tarde.";
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Bienvenido</title>
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/styles.css" />
    <style>
    .contenedor-principal {
        background-image: url('<?php echo IMG_URL; ?>/backgrounds/background.png');
    }
</style>
</head>
<body>
    <div class="contenedor-principal">
        <div class="grid_logo">
            <img src="<?php echo IMG_URL; ?>/logos/logo.png" alt="logo tu pizza">
        </div>

        <div id="loginform" class="formulariologin <?php echo isset($_POST['username']) ? 'active' : ''; ?>">
            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            <form id="loginForm" action="<?php echo BASE_URL; ?>/controllers/login.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="form-group">
                    <label for="username">Usuario:</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required>
                </div>

               <!-- <button type="submit" class="btn-login">Iniciar sesión</button> -->
            </form>
        </div>

        <div class="grid_item">
            <button id="botonBienvenida" class="boton-bienvenida">
                <span>Bienvenido</span>
            </button>
        </div>
    </div>

    <script src="<?php echo BASE_URL; ?>/public/js/bienvenida.js"></script>
</body>
</html>
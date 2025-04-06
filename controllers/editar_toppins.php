<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ' . BASE_URL . '/controllers/login.php');
    exit();
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['error'] = "ID de topping no válido";
    header('Location: ' . BASE_URL . '/controllers/gestionar_toppins.php');
    exit();
}

try {
    $stmt = $conn->prepare("SELECT * FROM toppings WHERE id = ? AND activo = TRUE");
    $stmt->execute([$id]);
    $topping = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$topping) {
        $_SESSION['error'] = "Topping no encontrado o inactivo";
        header('Location: ' . BASE_URL . '/controllers/gestionar_toppins.php');
        exit();
    }
} catch (PDOException $e) {
    die("Error al obtener topping: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim(filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING));
    $precio_familiar = filter_input(INPUT_POST, 'precio_familiar', FILTER_SANITIZE_STRING);
    $precio_pequeña = filter_input(INPUT_POST, 'precio_pequeña', FILTER_SANITIZE_STRING);
    
    // Procesamiento de precios
    $precio_familiar = str_replace(',', '.', $precio_familiar);
    $precio_pequeña = str_replace(',', '.', $precio_pequeña);
    
    $precio_familiar = filter_var($precio_familiar, FILTER_VALIDATE_FLOAT, [
        'options' => ['decimal' => '.', 'min_range' => 0.01]
    ]);
    
    $precio_pequeña = filter_var($precio_pequeña, FILTER_VALIDATE_FLOAT, [
        'options' => ['decimal' => '.', 'min_range' => 0.01]
    ]);

    $errores = [];

    if (empty($nombre) || strlen($nombre) < 3) {
        $errores[] = "Nombre debe tener al menos 3 caracteres";
    }
    
    if ($precio_familiar === false) {
        $errores[] = "Precio familiar debe ser un número válido mayor a 0.01";
    }
    
    if ($precio_pequeña === false) {
        $errores[] = "Precio pequeña debe ser un número válido mayor a 0.01";
    }

    if (empty($errores)) {
        try {
            $stmt = $conn->prepare("SELECT id FROM toppings WHERE nombre = ? AND id != ?");
            $stmt->execute([$nombre, $id]);
            
            if ($stmt->fetch()) {
                $errores[] = "Ya existe un topping con este nombre";
            } else {
                $stmt = $conn->prepare(
                    "UPDATE toppings SET 
                     nombre = ?, 
                     precio_familiar = ?, 
                     precio_pequeña = ? 
                     WHERE id = ?"
                );
                $stmt->execute([$nombre, $precio_familiar, $precio_pequeña, $id]);
                
                $_SESSION['exito'] = "Topping actualizado correctamente";
                header('Location: ' . BASE_URL . '/controllers/gestionar_toppins.php');
                exit();
            }
        } catch (PDOException $e) {
            $errores[] = "Error de base de datos: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Topping</title>
    <link rel="stylesheet" href="<?= CSS_URL ?>/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Indie+Flower&family=Lugrasimo&display=swap" rel="stylesheet">
    <style>
    .contenedor_tabla {
        background-image: url('<?= IMG_URL ?>/backgrounds/background_sm.jpg');
    }
    </style>
</head>
<body>
    <div class="contenedor_tabla">
        <div class="tabla">
            <div class="titulo_font">
                <h1>Editar Topping</h1>
                <a href="<?= BASE_URL ?>/controllers/gestionar_toppins.php" class="btn-regresar">← Regresar</a>
                
                <?php if (!empty($errores)): ?>
                    <div class="alert alert-error">
                        <?php foreach ($errores as $error): ?>
                            <p><?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="contenedor-edicion">
                <form method="POST" id="formTopping">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($topping['id']) ?>">
                    
                    <div class="form-group">
                        <label for="nombre">Nombre:</label>
                        <input type="text" id="nombre" name="nombre" 
                               value="<?= htmlspecialchars($topping['nombre']) ?>" 
                               required minlength="3">
                        <div class="error-msg" id="nombre-error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="precio_familiar">Precio (Familiar):</label>
                        <input type="text" id="precio_familiar" name="precio_familiar"
                               class="precio-input" data-decimales="2"
                               value="<?= number_format($topping['precio_familiar'], 2, '.', '') ?>"
                               placeholder="0.00" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="precio_pequeña">Precio (Pequeña):</label>
                        <input type="text" id="precio_pequeña" name="precio_pequeña"
                               class="precio-input" data-decimales="2"
                               value="<?= number_format($topping['precio_pequeña'], 2, '.', '') ?>"
                               placeholder="0.00" required>
                    </div>
                    
                    <button type="submit" class="btn-guardar">Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>
    <script>
        const API_BASE_URL = '<?= BASE_URL ?>/controllers';
        const TOPPING_ID = '<?= $topping['id'] ?>';
    </script>
    <script src="<?= JS_URL ?>/gestion_topping.js"></script>
</body>
</html>
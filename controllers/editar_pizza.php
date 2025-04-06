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
    $_SESSION['error'] = "ID de pizza no válido";
    header('Location: ' . BASE_URL . '/controllers/gestionar_pizzas.php');
    exit();
}

try {
    $stmt = $conn->prepare("SELECT * FROM pizzas WHERE id = ? AND activo = TRUE");
    $stmt->execute([$id]);
    $pizza = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pizza) {
        $_SESSION['error'] = "Pizza no encontrada o inactiva";
        header('Location: ' . BASE_URL . '/controllers/gestionar_pizzas.php');
        exit();
    }
} catch (PDOException $e) {
    die("Error al obtener pizza: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim(filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING));
    $tamaño = filter_input(INPUT_POST, 'tamaño', FILTER_SANITIZE_STRING);
    $precio = filter_input(INPUT_POST, 'precio', FILTER_SANITIZE_STRING);
    $precio = str_replace(',', '.', $precio);
    $precio = filter_var($precio, FILTER_VALIDATE_FLOAT, [
        'options' => [
            'decimal' => '.',
            'min_range' => 0.01
        ]
    ]);

    $errores = [];

    if (empty($nombre) || strlen($nombre) < 3) {
        $errores[] = "Nombre debe tener al menos 3 caracteres";
    }
    
    if (!in_array($tamaño, ['Familiar', 'Pequeña'])) {
        $errores[] = "Tamaño no válido";
    }
    
    if ($precio === false) {
        $errores[] = "Precio debe ser un número válido mayor a 0.01";
    }

    if (empty($errores)) {
        try {
            $stmt = $conn->prepare("SELECT id FROM pizzas WHERE nombre = ? AND tamaño = ? AND id != ?");
            $stmt->execute([$nombre, $tamaño, $id]);
            
            if ($stmt->fetch()) {
                $errores[] = "Ya existe una pizza con este nombre y tamaño";
            } else {
                $stmt = $conn->prepare(
                    "UPDATE pizzas SET 
                     nombre = ?, 
                     tamaño = ?, 
                     precio = ? 
                     WHERE id = ?"
                );
                $stmt->execute([$nombre, $tamaño, $precio, $id]);
                
                $_SESSION['exito'] = "Pizza actualizada correctamente";
                header('Location: ' . BASE_URL . '/controllers/gestionar_pizzas.php?mostrar=lista');
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
    <title>Editar Pizza</title>
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
                <h1>Editar Pizza</h1>
                <a href="<?= BASE_URL ?>/controllers/gestionar_pizzas.php" class="btn-regresar">← Regresar</a>
                
                <?php if (!empty($errores)): ?>
                    <div class="alert alert-error">
                        <?php foreach ($errores as $error): ?>
                            <p><?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="contenedor-edicion">
                <form method="POST" id="formPizza">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($pizza['id']) ?>">
                    
                    <div class="form-group">
                        <label for="nombre">Nombre:</label>
                        <input type="text" id="nombre" name="nombre" 
                               value="<?= htmlspecialchars($pizza['nombre']) ?>" 
                               required minlength="3">
                        <div class="error-msg" id="nombre-error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="tamaño">Tamaño:</label>
                        <select id="tamaño" name="tamaño" required>
                            <option value="Familiar" <?= $pizza['tamaño'] === 'Familiar' ? 'selected' : '' ?>>Familiar</option>
                            <option value="Pequeña" <?= $pizza['tamaño'] === 'Pequeña' ? 'selected' : '' ?>>Pequeña</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="precio">Precio:</label>
                        <input type="text" id="precio" name="precio"
                               class="precio-input"
                               value="<?= number_format($pizza['precio'], 2, '.', '') ?>"
                               placeholder="0.00"
                               required
                               data-decimales="2">
                        <div class="error-msg" id="precio-error"></div>
                    </div>
                    
                    <button type="submit" class="btn-guardar">Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>
    <script>
        const API_BASE_URL = '<?= BASE_URL ?>/controllers';
        const PIZZA_ID = '<?= $pizza['id'] ?>';
    </script>
    <script src="<?= JS_URL ?>/gestion_pizza.js"></script>
</body>
</html>
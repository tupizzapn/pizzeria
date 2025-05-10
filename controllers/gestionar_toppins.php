<?php
session_start();
include __DIR__ . '/../includes/config.php';
include __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ' . BASE_URL . '/controllers/login.php');
    exit();
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_topping'])) {
    $nombre = filter_input(INPUT_POST, 'nombre_topping', FILTER_SANITIZE_STRING);
    $precio_familiar = filter_input(INPUT_POST, 'precio_familiar', FILTER_SANITIZE_STRING);
    $precio_pequeña = filter_input(INPUT_POST, 'precio_pequeña', FILTER_SANITIZE_STRING);
    $cantidad_familiar = filter_input(INPUT_POST, 'cantidad_familiar', FILTER_SANITIZE_STRING);
    $cantidad_pequeña = filter_input(INPUT_POST, 'cantidad_pequeña', FILTER_SANITIZE_STRING);
    
    // Procesamiento de valores numéricos
    $precio_familiar = str_replace(',', '.', $precio_familiar);
    $precio_pequeña = str_replace(',', '.', $precio_pequeña);
    $cantidad_familiar = str_replace(',', '.', $cantidad_familiar);
    $cantidad_pequeña = str_replace(',', '.', $cantidad_pequeña);
    
    $precio_familiar = filter_var($precio_familiar, FILTER_VALIDATE_FLOAT, [
        'options' => ['decimal' => '.', 'min_range' => 0.01]
    ]);
    
    $precio_pequeña = filter_var($precio_pequeña, FILTER_VALIDATE_FLOAT, [
        'options' => ['decimal' => '.', 'min_range' => 0.01]
    ]);
    
    $cantidad_familiar = filter_var($cantidad_familiar, FILTER_VALIDATE_FLOAT, [
        'options' => ['decimal' => '.', 'min_range' => 0.01]
    ]);
    
    $cantidad_pequeña = filter_var($cantidad_pequeña, FILTER_VALIDATE_FLOAT, [
        'options' => ['decimal' => '.', 'min_range' => 0.01]
    ]);

    try {
        if (empty($nombre) || $precio_familiar === false || $precio_pequeña === false || 
            $cantidad_familiar === false || $cantidad_pequeña === false) {
            $error = "Todos los campos son obligatorios y los valores deben ser válidos.";
        } elseif ($precio_familiar <= 0 || $precio_pequeña <= 0 || 
                 $cantidad_familiar <= 0 || $cantidad_pequeña <= 0) {
            $error = "Todos los valores deben ser mayores a cero.";
        } else {
            $stmt = $conn->prepare("SELECT id FROM toppings WHERE nombre = ? AND activo = TRUE");
            $stmt->execute([$nombre]);
            
            if ($stmt->fetch()) {
                $error = "Ya existe un topping con este nombre.";
            } else {
                $stmt = $conn->prepare(
                    "INSERT INTO toppings 
                    (nombre, precio_familiar, precio_pequeña, cantidad_familiar, cantidad_pequeña) 
                    VALUES (?, ?, ?, ?, ?)"
                );
                $stmt->execute([
                    $nombre, 
                    $precio_familiar, 
                    $precio_pequeña,
                    $cantidad_familiar,
                    $cantidad_pequeña
                ]);
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit();
            }
        }
    } catch (PDOException $e) {
        $error = "Error en la base de datos: " . $e->getMessage();
    }
}

// Obtener toppings activos (actualizado para incluir las nuevas columnas)
$toppings = $conn->query(
    "SELECT id, nombre, precio_familiar, precio_pequeña, cantidad_familiar, cantidad_pequeña
     FROM toppings 
     WHERE activo = TRUE
     ORDER BY nombre"
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Toppings</title>
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
</head>
<body>
<div class="contenedor_tabla">
    <div class="tabla">
        <div class="titulo_font">
            <h1>Gestionar Toppings</h1>
            <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?>!</p>
            
            <div class="menu-navegacion">
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>/views/index.php">Regresar</a></li> 
                    <li><a href="#" id="btnNuevo">Nuevo ingreso</a></li> 
                    <li><a href="#" id="btnEditar">Ver Lista</a></li> 
                </ul>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
        </div>

        <div class="nuevo_ingreso">
            <h2>Agregar Topping</h2>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" id="formTopping">
                <div class="form-group">
                    <label for="nombre_topping">Nombre:</label>
                    <input type="text" id="nombre_topping" name="nombre_topping" required 
                           autocomplete="off" readonly onfocus="this.removeAttribute('readonly')">
                    <div class="error-msg" id="topping-error"></div>
                </div>

                <div class="form-group">
                    <label for="precio_familiar">Precio (Familiar):</label>
                    <input type="text" id="precio_familiar" name="precio_familiar"
                           class="precio-input" data-decimales="2"
                           placeholder="0.00" required>
                </div>

                <div class="form-group">
                    <label for="precio_pequeña">Precio (Pequeña):</label>
                    <input type="text" id="precio_pequeña" name="precio_pequeña"
                           class="precio-input" data-decimales="2"
                           placeholder="0.00" required>
                </div>

                <div class="form-group">
                    <label for="cantidad_familiar">Cantidad (Familiar):</label>
                    <input type="text" id="cantidad_familiar" name="cantidad_familiar"
                           class="precio-input" data-decimales="2"
                           placeholder="1.00" required>
                </div>

                <div class="form-group">
                    <label for="cantidad_pequeña">Cantidad (Pequeña):</label>
                    <input type="text" id="cantidad_pequeña" name="cantidad_pequeña"
                           class="precio-input" data-decimales="2"
                           placeholder="0.50" required>
                </div>


                <button type="submit" name="agregar_topping">Agregar Topping</button>
            </form>
        </div>

        <div class="lista-usuarios">
            <h2>Toppings Disponibles</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Precio Familiar</th>
                        <th>Precio Pequeña</th>
                        <th>Cant. Familiar</th>
                        <th>Cant. Pequeña</th>
                        <th>Acciones</th>
                </thead>
                <tbody>
                    <?php foreach ($toppings as $topping): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($topping['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo number_format($topping['precio_familiar'], 2); ?></td>
                            <td><?php echo number_format($topping['precio_pequeña'], 2); ?></td>
                            <td><?php echo number_format($topping['cantidad_familiar'], 2); ?></td>
                            <td><?php echo number_format($topping['cantidad_pequeña'], 2); ?></td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>/controllers/editar_toppins.php?id=<?php echo $topping['id']; ?>">Editar</a>
                                <a href="<?php echo BASE_URL; ?>/controllers/eliminar_toppins.php?id=<?php echo $topping['id']; ?>" 
                                   onclick="return confirm('¿Eliminar este topping?')">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    const API_BASE_URL = '<?php echo BASE_URL; ?>/controllers';
</script>
<script src="<?php echo JS_URL; ?>/gestion_topping.js"></script>
</body>
</html>
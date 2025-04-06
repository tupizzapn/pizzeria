<?php
session_start();
include __DIR__ . '/../includes/config.php';
include __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ' . BASE_URL . '/controllers/login.php');
    exit();
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_pizza'])) {
    $nombre = filter_input(INPUT_POST, 'nombre_pizza', FILTER_SANITIZE_STRING);
    $tamaño = filter_input(INPUT_POST, 'tamaño_pizza', FILTER_SANITIZE_STRING);
    $precio = filter_input(INPUT_POST, 'precio_pizza', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

    try {
        if (empty($nombre) || empty($tamaño) || empty($precio)) {
            $error = "Todos los campos son obligatorios.";
        } elseif ($precio <= 0) {
            $error = "El precio debe ser mayor a cero.";
        } else {
            $stmt = $conn->prepare("SELECT id FROM pizzas WHERE nombre = ? AND tamaño = ? AND activo = TRUE");
            $stmt->execute([$nombre, $tamaño]);
            
            if ($stmt->fetch()) {
                $error = "Ya existe una pizza con este nombre y tamaño.";
            } else {
                $stmt = $conn->prepare(
                    "INSERT INTO pizzas (nombre, tamaño, precio) 
                     VALUES (?, ?, ?)"
                );
                $stmt->execute([$nombre, $tamaño, $precio]);
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit();
            }
        }
    } catch (PDOException $e) {
        $error = "Error en la base de datos: " . $e->getMessage();
    }
}

// Obtener pizzas activas
$pizzas = $conn->query(
    "SELECT id, nombre, tamaño, precio 
     FROM pizzas 
     WHERE activo = TRUE
     ORDER BY nombre, tamaño"
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Pizzas</title>
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
            <h1>Gestionar Pizzas</h1>
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
            <h2>Agregar Pizza</h2>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" id="formPizza">
                <div class="form-group">
                    <label for="nombre_pizza">Nombre:</label>
                    <input type="text" id="nombre_pizza" name="nombre_pizza" required 
                           autocomplete="off" readonly onfocus="this.removeAttribute('readonly')">
                    <div class="error-msg" id="pizza-error"></div>
                </div>

                <div class="form-group">
                    <label for="tamaño_pizza">Tamaño:</label>
                    <select id="tamaño_pizza" name="tamaño_pizza" required
                            autocomplete="off" readonly onfocus="this.removeAttribute('readonly')">
                        <option value="">Seleccione tamaño</option>
                        <option value="Familiar">Familiar</option>
                        <option value="Pequeña">Pequeña</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="precio_pizza">Precio:</label>
                    <input type="text" id="precio_pizza" name="precio_pizza" 
                           class="precio-input" 
                           placeholder="0.00"
                           required
                           data-decimales="2">
                </div>

                <button type="submit" name="agregar_pizza">Agregar Pizza</button>
            </form>
        </div>

        <div class="lista-usuarios">
            <h2>Pizzas Disponibles</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Tamaño</th>
                        <th>Precio</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pizzas as $pizza): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($pizza['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($pizza['tamaño'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo number_format($pizza['precio'], 2); ?></td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>/controllers/editar_pizza.php?id=<?php echo $pizza['id']; ?>">Editar</a>
                                <a href="<?php echo BASE_URL; ?>/controllers/eliminar_pizza.php?id=<?php echo $pizza['id']; ?>" 
                                   onclick="return confirm('¿Eliminar esta pizza?')">Eliminar</a>
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
<script src="<?php echo JS_URL; ?>/gestion_pizza.js"></script>
</body>
</html>
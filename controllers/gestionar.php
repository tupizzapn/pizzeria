<?php
session_start();
include __DIR__ . '/../includes/config.php'; // Incluir config.php

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/controllers/login.php'); // Ruta corregida
    exit();
}

if ($_SESSION['rol'] !== 'admin') {
    echo "Acceso denegado. Solo los administradores pueden gestionar pizzas y toppings.";
    exit();
}

include __DIR__ . '/../includes/db.php'; // Ruta corregida

// Procesar formulario de pizzas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_pizza'])) {
    $nombre = filter_input(INPUT_POST, 'nombre_pizza', FILTER_SANITIZE_STRING);
    $tamaño = filter_input(INPUT_POST, 'tamaño_pizza', FILTER_SANITIZE_STRING);
    $precio = filter_input(INPUT_POST, 'precio_pizza', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

    // Verificar si la pizza ya existe
    $stmt = $conn->prepare("SELECT id FROM pizzas WHERE nombre = ? AND tamaño = ?");
    $stmt->execute([$nombre, $tamaño]);
    if ($stmt->fetch()) {
        $error_pizza = "Ya existe una pizza con ese nombre y tamaño.";
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO pizzas (nombre, tamaño, precio) VALUES (?, ?, ?)");
            $stmt->execute([$nombre, $tamaño, $precio]);
            header('Location: ' . BASE_URL . '/controllers/gestionar.php');
            exit();
        } catch (PDOException $e) {
            $error_pizza = "Error al agregar pizza: " . $e->getMessage();
        }
    }
}

// Procesar formulario de toppings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_topping'])) {
    $nombre = filter_input(INPUT_POST, 'nombre_topping', FILTER_SANITIZE_STRING);
    $precio_familiar = filter_input(INPUT_POST, 'precio_familiar', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $precio_pequeña = filter_input(INPUT_POST, 'precio_pequeña', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

    // Verificar si el topping ya existe
    $stmt = $conn->prepare("SELECT id FROM toppings WHERE nombre = ?");
    $stmt->execute([$nombre]);
    if ($stmt->fetch()) {
        $error_topping = "Ya existe un topping con ese nombre.";
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO toppings (nombre, precio_familiar, precio_pequeña) VALUES (?, ?, ?)");
            $stmt->execute([$nombre, $precio_familiar, $precio_pequeña]);
            header('Location: ' . BASE_URL . '/controllers/gestionar.php');
            exit();
        } catch (PDOException $e) {
            $error_topping = "Error al agregar topping: " . $e->getMessage();
        }
    }
}

// Obtener listas de pizzas y toppings
$pizzas = $conn->query("SELECT * FROM pizzas")->fetchAll(PDO::FETCH_ASSOC);
$toppings = $conn->query("SELECT * FROM toppings")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Pizzas y Toppings</title>
    <link rel="stylesheet" href="<?php echo CSS_DIR; ?>/styles.css"> <!-- Ruta corregida -->
</head>
<body>
<nav>
    <a href="<?php echo BASE_URL; ?>/views/index.php">Inicio</a> <!-- Ruta corregida -->
    <?php if ($_SESSION['rol'] === 'admin'): ?>
        <a href="<?php echo BASE_URL; ?>/controllers/gestionar.php">Gestionar Pizzas y Toppings</a> <!-- Ruta corregida -->
        <a href="<?php echo BASE_URL; ?>/controllers/gestionar_usuarios.php">Gestionar Usuarios</a> <!-- Ruta corregida -->
    <?php endif; ?>
    <?php if ($_SESSION['rol'] === 'vendedor'): ?>
        <a href="<?php echo BASE_URL; ?>/views/realizar_pedido.php">Realizar Pedido</a> <!-- Ruta corregida -->
        <a href="<?php echo BASE_URL; ?>/views/pedidos.php">Ver Pedidos</a> <!-- Ruta corregida -->
    <?php endif; ?>
    <a href="<?php echo BASE_URL; ?>/controllers/logout.php">Cerrar sesión</a> <!-- Ruta corregida -->
</nav>
    <h1>Gestionar Pizzas y Toppings</h1>
    <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?>! <a href="<?php echo BASE_URL; ?>/controllers/logout.php">Cerrar sesión</a></p>

    <!-- Formulario para agregar pizzas -->
    <h2>Agregar Pizza</h2>
    <?php if (isset($error_pizza)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error_pizza, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <form action="<?php echo BASE_URL; ?>/controllers/gestionar.php" method="POST"> <!-- Ruta corregida -->
        <label for="nombre_pizza">Nombre:</label>
        <input type="text" id="nombre_pizza" name="nombre_pizza" required>

        <label for="tamaño_pizza">Tamaño:</label>
        <select id="tamaño_pizza" name="tamaño_pizza" required>
            <option value="Familiar">Familiar</option>
            <option value="Pequeña">Pequeña</option>
        </select>

        <label for="precio_pizza">Precio:</label>
        <input type="number" id="precio_pizza" name="precio_pizza" step="0.01" required>

        <button type="submit" name="agregar_pizza">Agregar Pizza</button>
    </form>

    <!-- Lista de pizzas -->
    <h2>Pizzas</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Tamaño</th>
                <th>Precio</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pizzas as $pizza): ?>
                <tr>
                    <td><?php echo htmlspecialchars($pizza['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($pizza['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($pizza['tamaño'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo number_format($pizza['precio'], 2); ?></td>
                    <td>
                        <a href="<?php echo BASE_URL; ?>/controllers/editar_pizza.php?id=<?php echo $pizza['id']; ?>">Editar</a> <!-- Ruta corregida -->
                        <a href="<?php echo BASE_URL; ?>/controllers/eliminar_pizza.php?id=<?php echo $pizza['id']; ?>" onclick="return confirm('¿Estás seguro de eliminar esta pizza?');">Eliminar</a> <!-- Ruta corregida -->
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Formulario para agregar toppings -->
    <h2>Agregar Topping</h2>
    <?php if (isset($error_topping)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error_topping, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <form action="<?php echo BASE_URL; ?>/controllers/gestionar.php" method="POST"> <!-- Ruta corregida -->
        <label for="nombre_topping">Nombre:</label>
        <input type="text" id="nombre_topping" name="nombre_topping" required>

        <label for="precio_familiar">Precio (Familiar):</label>
        <input type="number" id="precio_familiar" name="precio_familiar" step="0.01" required>

        <label for="precio_pequeña">Precio (Pequeña):</label>
        <input type="number" id="precio_pequeña" name="precio_pequeña" step="0.01" required>

        <button type="submit" name="agregar_topping">Agregar Topping</button>
    </form>

    <!-- Lista de toppings -->
    <h2>Toppings</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Precio (Familiar)</th>
                <th>Precio (Pequeña)</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($toppings as $topping): ?>
                <tr>
                    <td><?php echo htmlspecialchars($topping['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($topping['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo number_format($topping['precio_familiar'], 2); ?></td>
                    <td><?php echo number_format($topping['precio_pequeña'], 2); ?></td>
                    <td>
                        <a href="<?php echo BASE_URL; ?>/controllers/editar_topping.php?id=<?php echo $topping['id']; ?>">Editar</a> <!-- Ruta corregida -->
                        <a href="<?php echo BASE_URL; ?>/controllers/eliminar_topping.php?id=<?php echo $topping['id']; ?>" onclick="return confirm('¿Estás seguro de eliminar este topping?');">Eliminar</a> <!-- Ruta corregida -->
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
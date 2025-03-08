<?php
include __DIR__ . '/../includes/config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/controllers/login.php'); // Ruta corregida
    exit();
}

//if ($_SESSION['rol'] !== 'vendedor') {
  //  echo "Acceso denegado. Solo los vendedores pueden realizar pedidos.";
  //  exit();
//}
include __DIR__ . '/../includes/db.php'; // Ruta corregida

// Obtener la lista de pizzas y toppings
$pizzas = $conn->query("SELECT * FROM pizzas")->fetchAll(PDO::FETCH_ASSOC);
$toppings = $conn->query("SELECT * FROM toppings")->fetchAll(PDO::FETCH_ASSOC);

// Procesar el formulario de pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Datos del cliente
    $nombre_cliente = $_POST['nombre_cliente'];
    $telefono_cliente = $_POST['telefono_cliente'];

    // Verificar si el cliente ya existe
    $stmt = $conn->prepare("SELECT id FROM clientes WHERE telefono = ?");
    $stmt->execute([$telefono_cliente]);
    $cliente = $stmt->fetch();

    if (!$cliente) {
        // Crear nuevo cliente
        $stmt = $conn->prepare("INSERT INTO clientes (nombre, telefono) VALUES (?, ?)");
        $stmt->execute([$nombre_cliente, $telefono_cliente]);
        $cliente_id = $conn->lastInsertId();
    } else {
        $cliente_id = $cliente['id'];
    }

    // Crear el pedido
    $total_pedido = 0;
    $stmt = $conn->prepare("INSERT INTO pedidos (cliente_id, total) VALUES (?, ?)");
    $stmt->execute([$cliente_id, $total_pedido]);
    $pedido_id = $conn->lastInsertId();

    // Procesar cada pizza en el pedido
    foreach ($_POST['pizzas'] as $pizza) {
        $pizza_id = $pizza['id'];
        $cantidad = $pizza['cantidad'];
        $toppings_seleccionados = $pizza['toppings'];

        // Calcular el precio de la pizza con toppings
        $precio_pizza = $conn->query("SELECT precio FROM pizzas WHERE id = $pizza_id")->fetchColumn();
        $precio_toppings = 0;

        foreach ($toppings_seleccionados as $topping_id) {
            $precio_topping = $conn->query("SELECT precio_familiar FROM toppings WHERE id = $topping_id")->fetchColumn();
            $precio_toppings += $precio_topping;
        }

        $precio_total_pizza = ($precio_pizza + $precio_toppings) * $cantidad;
        $total_pedido += $precio_total_pizza;

        // Guardar los detalles de la pizza en el pedido
        $stmt = $conn->prepare("INSERT INTO detalles_pedido (pedido_id, pizza_id, cantidad) VALUES (?, ?, ?)");
        $stmt->execute([$pedido_id, $pizza_id, $cantidad]);
        $detalle_pedido_id = $conn->lastInsertId();

        // Guardar los toppings de la pizza
        foreach ($toppings_seleccionados as $topping_id) {
            $stmt = $conn->prepare("INSERT INTO toppings_pedido (detalle_pedido_id, topping_id) VALUES (?, ?)");
            $stmt->execute([$detalle_pedido_id, $topping_id]);
        }
    }

    // Actualizar el total del pedido
    $stmt = $conn->prepare("UPDATE pedidos SET total = ? WHERE id = ?");
    $stmt->execute([$total_pedido, $pedido_id]);

    // Redirigir al resumen del pedido
    header("Location: " . BASE_URL . "/views/resumen_pedido.php?id=$pedido_id"); // Ruta corregida
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Realizar Pedido</title>
    <link rel="stylesheet" href="<?php echo CSS_DIR; ?>/styles.css"> <!-- Ruta corregida -->
</head>
<body>
    <h1>Realizar Pedido</h1>
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
    <form action="<?php echo BASE_URL; ?>/views/index.php" method="POST"> <!-- Ruta corregida -->
        <!-- Datos del cliente -->
        <h2>Datos del Cliente</h2>
        <label for="nombre_cliente">Nombre:</label>
        <input type="text" id="nombre_cliente" name="nombre_cliente" required>

        <label for="telefono_cliente">Teléfono:</label>
        <input type="text" id="telefono_cliente" name="telefono_cliente" required>

        <!-- Lista de pizzas -->
        <h2>Pizzas</h2>
        <div id="pizzas">
            <div class="pizza">
                <label for="pizza_1">Pizza:</label>
                <select name="pizzas[0][id]" required>
                    <?php foreach ($pizzas as $pizza): ?>
                        <option value="<?php echo $pizza['id']; ?>"><?php echo $pizza['nombre']; ?> (<?php echo $pizza['tamaño']; ?>) - $<?php echo $pizza['precio']; ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="cantidad_1">Cantidad:</label>
                <input type="number" name="pizzas[0][cantidad]" value="1" min="1" required>

                <label>Toppings:</label>
                <?php foreach ($toppings as $topping): ?>
                    <label>
                        <input type="checkbox" name="pizzas[0][toppings][]" value="<?php echo $topping['id']; ?>">
                        <?php echo $topping['nombre']; ?> ($<?php echo $topping['precio_familiar']; ?>)
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Botón para agregar más pizzas -->
        <button type="button" onclick="agregarPizza()">Agregar otra pizza</button>

        <!-- Confirmar pedido -->
        <button type="submit">Confirmar Pedido</button>
    </form>

    <script>
        let contadorPizzas = 1;

        function agregarPizza() {
            const divPizzas = document.getElementById('pizzas');
            const nuevaPizza = document.createElement('div');
            nuevaPizza.className = 'pizza';
            nuevaPizza.innerHTML = `
                <label for="pizza_${contadorPizzas}">Pizza:</label>
                <select name="pizzas[${contadorPizzas}][id]" required>
                    <?php foreach ($pizzas as $pizza): ?>
                        <option value="<?php echo $pizza['id']; ?>"><?php echo $pizza['nombre']; ?> (<?php echo $pizza['tamaño']; ?>) - $<?php echo $pizza['precio']; ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="cantidad_${contadorPizzas}">Cantidad:</label>
                <input type="number" name="pizzas[${contadorPizzas}][cantidad]" value="1" min="1" required>

                <label>Toppings:</label>
                <?php foreach ($toppings as $topping): ?>
                    <label>
                        <input type="checkbox" name="pizzas[${contadorPizzas}][toppings][]" value="<?php echo $topping['id']; ?>">
                        <?php echo $topping['nombre']; ?> ($<?php echo $topping['precio_familiar']; ?>)
                    </label>
                <?php endforeach; ?>
            `;
            divPizzas.appendChild(nuevaPizza);
            contadorPizzas++;
        }
    </script>
</body>
</html>
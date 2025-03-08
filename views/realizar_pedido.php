<?php
session_start();
include __DIR__ . '/../includes/config.php'; // Incluir config.php

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/controllers/login.php'); // Ruta corregida
    exit();
}

if ($_SESSION['rol'] !== 'vendedor') {
    echo "Acceso denegado. Solo los vendedores pueden realizar pedidos.";
    exit();
}

include __DIR__ . '/../includes/db.php'; // Ruta corregida

// Obtener la lista de pizzas y toppings
$pizzas = $conn->query("SELECT * FROM pizzas")->fetchAll(PDO::FETCH_ASSOC);
$toppings = $conn->query("SELECT * FROM toppings")->fetchAll(PDO::FETCH_ASSOC);
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
    <form action="<?php echo BASE_URL; ?>/controllers/procesar_pedido.php" method="POST"> <!-- Ruta corregida -->
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
                <select name="pizzas[0][id]" required onchange="actualizarToppings(this)">
                    <?php foreach ($pizzas as $pizza): ?>
                        <option value="<?php echo $pizza['id']; ?>"><?php echo $pizza['nombre']; ?> (<?php echo $pizza['tamaño']; ?>) - $<?php echo $pizza['precio']; ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="cantidad_1">Cantidad:</label>
                <input type="number" name="pizzas[0][cantidad]" value="1" min="1" required>

                <label>Toppings:</label>
                <div class="toppings">
                    <?php foreach ($toppings as $topping): ?>
                        <label>
                            <input type="checkbox" name="pizzas[0][toppings][]" value="<?php echo $topping['id']; ?>" data-precio-familiar="<?php echo $topping['precio_familiar']; ?>" data-precio-pequeña="<?php echo $topping['precio_pequeña']; ?>">
                            <?php echo $topping['nombre']; ?> 
                            (<span class="precio-topping">$<?php echo $topping['precio_familiar']; ?></span>)
                        </label>
                    <?php endforeach; ?>
                </div>
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
                <select name="pizzas[${contadorPizzas}][id]" required onchange="actualizarToppings(this)">
                    <?php foreach ($pizzas as $pizza): ?>
                        <option value="<?php echo $pizza['id']; ?>"><?php echo $pizza['nombre']; ?> (<?php echo $pizza['tamaño']; ?>) - $<?php echo $pizza['precio']; ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="cantidad_${contadorPizzas}">Cantidad:</label>
                <input type="number" name="pizzas[${contadorPizzas}][cantidad]" value="1" min="1" required>

                <label>Toppings:</label>
                <div class="toppings">
                    <?php foreach ($toppings as $topping): ?>
                        <label>
                            <input type="checkbox" name="pizzas[${contadorPizzas}][toppings][]" value="<?php echo $topping['id']; ?>" data-precio-familiar="<?php echo $topping['precio_familiar']; ?>" data-precio-pequeña="<?php echo $topping['precio_pequeña']; ?>">
                            <?php echo $topping['nombre']; ?> 
                            (<span class="precio-topping">$<?php echo $topping['precio_familiar']; ?></span>)
                        </label>
                    <?php endforeach; ?>
                </div>
            `;
            divPizzas.appendChild(nuevaPizza);
            contadorPizzas++;
        }

        function actualizarToppings(select) {
            // Obtener el tamaño de la pizza seleccionada
            const tamaño = select.options[select.selectedIndex].text.includes('Familiar') ? 'familiar' : 'pequeña';

            // Obtener todos los toppings de la pizza actual
            const toppings = select.closest('.pizza').querySelectorAll('.toppings input[type="checkbox"]');

            // Actualizar el precio de cada topping
            toppings.forEach(topping => {
                const precio = topping.getAttribute(`data-precio-${tamaño}`);
                const precioElement = topping.closest('label').querySelector('.precio-topping');
                if (precioElement) {
                    precioElement.textContent = `$${precio}`;
                }
            });
        }

        // Inicializar los precios al cargar la página
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.pizza select').forEach(select => {
                actualizarToppings(select);
            });
        });
    </script>
</body>
</html>

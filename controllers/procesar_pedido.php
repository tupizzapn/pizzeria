<?php
session_start();
include __DIR__ . '/../includes/config.php'; // Incluir config.php

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/controllers/login.php'); // Ruta corregida
    exit();
}

include __DIR__ . '/../includes/db.php'; // Ruta corregida

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar y sanitizar los datos del formulario
    $nombre_cliente = filter_input(INPUT_POST, 'nombre_cliente', FILTER_SANITIZE_STRING);
    $telefono_cliente = filter_input(INPUT_POST, 'telefono_cliente', FILTER_SANITIZE_STRING);
    $pizzas = $_POST['pizzas']; // Array de pizzas seleccionadas

    // Verificar que todos los campos estén presentes y no estén vacíos
    if (empty($telefono_cliente) || empty($pizzas)) {
        echo "<h1>Error: Todos los campos son obligatorios.</h1>";
        exit();
    }

    try {
        // Verificar si el cliente ya existe
        $stmt = $conn->prepare("SELECT id FROM clientes WHERE telefono = ?");
        $stmt->execute([$telefono_cliente]);
        $cliente = $stmt->fetch();

        if (!$cliente) {
            // Crear nuevo cliente
            $stmt = $conn->prepare("INSERT INTO clientes (nombre, telefono, direccion) VALUES (?, ?, ?)");
            $stmt->execute([$nombre_cliente, $telefono_cliente, '']); // Insertar cadena vacía para la dirección
            $cliente_id = $conn->lastInsertId();
        } else {
            $cliente_id = $cliente['id'];
        }

        // Crear el pedido
        $total_pedido = 0;
        $stmt = $conn->prepare("INSERT INTO pedidos (cliente_id, total) VALUES (?, ?)");
        $stmt->execute([$cliente_id, $total_pedido]);
        $pedido_id = $conn->lastInsertId();

        // Procesar cada pizza en el pedido (sin cambios)
        foreach ($pizzas as $pizza) {
            $pizza_id = $pizza['id'];
            $cantidad = $pizza['cantidad'];
            $toppings_seleccionados = $pizza['toppings'];

            // Obtener el tamaño y precio de la pizza
            $pizza_info = $conn->query("SELECT tamaño, precio FROM pizzas WHERE id = $pizza_id")->fetch(PDO::FETCH_ASSOC);
            $tamaño_pizza = $pizza_info['tamaño'];
            $precio_pizza = $pizza_info['precio'];

            // Calcular el precio de los toppings según el tamaño de la pizza
            $precio_toppings = 0;
            foreach ($toppings_seleccionados as $topping_id) {
                $precio_topping = $conn->query("SELECT precio_$tamaño_pizza FROM toppings WHERE id = $topping_id")->fetchColumn();
                $precio_toppings += $precio_topping;
            }

            // Calcular el precio total de la pizza (precio de la pizza + toppings) * cantidad
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

        // Actualizar el total del pedido (sin cambios)
        $stmt = $conn->prepare("UPDATE pedidos SET total = ? WHERE id = ?");
        $stmt->execute([$total_pedido, $pedido_id]);

        // Redirigir al resumen del pedido (sin cambios)
        header('Location: ' . BASE_URL . '/views/resumen_pedido.php?id=' . $pedido_id);
        exit();
    } catch (PDOException $e) {
        // Mostrar un mensaje de error detallado (sin cambios)
        echo "<h1>Error al procesar el pedido</h1>";
        echo "<p>Por favor, intenta nuevamente. Si el problema persiste, contacta al soporte.</p>";
        echo "<p>Detalles del error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>";
    }
}
?>
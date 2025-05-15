<?php
session_start();
include __DIR__ . '/../includes/config.php'; // Incluir config.php
include __DIR__ . '/../includes/db.php'; // Incluir conexión a la base de datos

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/controllers/login.php'); // Redirigir al login
    exit();
}

// Verificar si el usuario tiene el rol de vendedor
if ($_SESSION['rol'] !== 'vendedor') {
    echo "Acceso denegado. Solo los vendedores pueden ver los pedidos.";
    exit();
}

// Obtener la fecha actual
$fecha_actual = date('Y-m-d');

// Obtener los pedidos no procesados del día (que no están en la tabla ventas)
$query = "
    SELECT p.id, p.total, p.estado, c.telefono 
    FROM pedidos p
    LEFT JOIN clientes c ON p.cliente_id = c.id
    LEFT JOIN ventas v ON p.id = v.pedido_id
    WHERE v.pedido_id IS NULL AND DATE(p.fecha_pedido) = :fecha_actual
    ORDER BY p.fecha_pedido DESC
";
$stmt = $conn->prepare($query);
$stmt->execute(['fecha_actual' => $fecha_actual]);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener la lista de pizzas y toppings
$pizzas = $conn->query("SELECT * FROM pizzas")->fetchAll(PDO::FETCH_ASSOC);
$toppings = $conn->query("SELECT * FROM toppings")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de Pedidos</title>
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
                    <h1>Gestionar Pedidos</h1>
                    <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?>!</p>
                    
                            <div class="menu-navegacion">
                                <ul>
                                    <li><a href="<?php echo BASE_URL; ?>/views/index.php">Home</a></li> 
                                    <li><a href="#" id="btnNuevo">Nuevo Pedido</a></li> 
                                    <li><a href="#" id="btnLista">Ver Lista</a></li> 
                                </ul>
                            </div>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-error"><?php echo $error; ?></div>
                    <?php endif; ?>
                </div>

            <!-- Formulario nuevos Pedidos -->
            <div id="formularioPedido" class="formulario-pedido">
                <p>Ingresa un Nuevo Pedido</p>
                <form id="pedidoForm" action="<?php echo BASE_URL; ?>/controllers/procesar_pedido.php" method="POST">
                                            <!-- Datos del cliente - Nueva estructura -->
                                            <h2>Datos del Cliente</h2>
                                        <div class="datos-cliente-grid">

                                            <div class="campo-cliente">
                                                <label for="nombre_cliente">Nombre:</label>
                                                <input type="text" id="nombre_cliente" name="nombre_cliente">
                                            </div>

                                            <div class="campo-cliente">
                                                <label for="telefono_cliente">Teléfono:</label>
                                                <input type="text" id="telefono_cliente" name="telefono_cliente" required>
                                            </div>

                                            <div class="campo-cliente">
                                                <div class="resumen-label">Resumen del Pedido:</div>
                                                    <div class="resumen-pedido">
                                                        <p>Total Pizzas: <span id="resumen-cantidad">0</span></p>
                                                        <p>Total a Pagar: $ <span id="resumen-total">0.00</span></p>
                                                    </div>
                                            </div>
                                        </div>

                                <!-- Lista del formulario de pizzas -->
                                <h2>Pizzas</h2>
                                        <div id="pizzas" class="pizzas-scrollable">

                                            <div class="pizza">
                                                <h3 class="numero-pizza">Pizza #1</h3>
                                                <label for="pizza_0"></label>
                                                    <select id="pizza_0" name="pizzas[0][id]" required class="select-pizza">
                                                        <?php foreach ($pizzas as $pizza): ?>
                                                            <option value="<?php echo $pizza['id']; ?>">
                                                                <?php echo $pizza['nombre']; ?> (<?php echo $pizza['tamaño']; ?>) - $<?php echo $pizza['precio']; ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>

                                                <label for="cantidad_0">Cantidad:</label>
                                                <input type="number" id="cantidad_0" name="pizzas[0][cantidad]" value="1" min="1" required>

                                                <div class="toppings-label">Toppings:</div>
                            
                                                    <div class="toppings-grid">
                                                        <?php foreach (array_chunk($toppings, ceil(count($toppings)/2)) as $topping_chunk): ?>
                                                                <div class="columna-toppings">
                                                                    <?php foreach ($topping_chunk as $topping): ?>
                                                                        <label class="topping-item">
                                                                                <input type="checkbox" 
                                                                                    name="pizzas[0][toppings][]" 
                                                                                    value="<?php echo $topping['id']; ?>"
                                                                                    data-precio-familiar="<?php echo $topping['precio_familiar']; ?>"
                                                                                    data-precio-pequeña="<?php echo $topping['precio_pequeña']; ?>">
                                                                                <span class="topping-name"><?php echo $topping['nombre']; ?></span>
                                                                                <span class="topping-price">($<?php echo $topping['precio_familiar']; ?>)</span>
                                                                        </label>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                        <?php endforeach; ?>
                                                    </div>
                        
                                                 <button type="button" class="eliminar-pizza">- Pizza</button>
                                              </div>
                                           </div>
                            
                                
                                            <div class="floating-actions">
                                                <button id="agregarPizzaBtn" class="fab-button" type="button" aria-label="Añadir pizza">+</button>
                                                <button id="confirmarPedidoBtn" class="fab-button" aria-label="Confirmar pedido">✔</button>
                                                <button id="cancelarPedidoBtn" class="fab-button fab-cancel" aria-label="Cancelar pedido">X</button>
                                            </div>
                
                </form>
           </div>

            <!-- lista de Pedidos -->
        <div id="listaPedidos" class="lista-pedidos">
                 
                    <h2 class="font_resumen">Pedidos Pendientes del Día</h2>
                                                         
            <table border="1">
                            <thead>
                                <tr class="font_tabla">
                                    <th>Teléfono Cliente</th>
                                    <th>Total</th>
                                    <th>Accion</th>
                                </tr>
                        </thead>
                <tbody>
                    <?php foreach ($pedidos as $pedido): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($pedido['telefono'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>$<?php echo number_format($pedido['total'], 2); ?></td>
                            <td>
                                <a href="ventas.php?id=<?php echo $pedido['id']; ?>" class="btn-procesar">Procesar</a>
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
<script src="<?php echo JS_URL; ?>/scrollManager.js"></script>
<script src="<?php echo JS_URL; ?>/gestionar_pedido.js"></script>
</body>
</html>
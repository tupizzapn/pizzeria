<?php
session_start();
include __DIR__ . '/../includes/config.php'; // Incluir config.php
include __DIR__ . '/../includes/db.php'; // Incluir conexión a la base de datos

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/controllers/login.php'); // Redirigir al login
    exit();
}

// Verificar si el usuario tiene el rol de vendedor o admin
if ($_SESSION['rol'] !== 'vendedor' && $_SESSION['rol'] !== 'admin') {
    echo "Acceso denegado. Solo vendedores y administradores pueden ver los pedidos procesados.";
    exit();
}

// Obtener la fecha actual
$fecha_actual = date('Y-m-d');

// Obtener los pedidos procesados del día con nombres de repartidores, pizzeros y teléfono del cliente
$query = "
    SELECT 
        p.id, 
        p.fecha_pedido, 
        p.total, 
        v.delivery_id, 
        v.pizzero_id, 
        v.fecha_venta, 
        d.nombre AS nombre_delivery, 
        pz.nombre AS nombre_pizzero,
        c.telefono AS telefono_cliente
    FROM pedidos p
    INNER JOIN ventas v ON p.id = v.pedido_id
    LEFT JOIN delivery d ON v.delivery_id = d.usuario_id
    LEFT JOIN pizzero pz ON v.pizzero_id = pz.usuario_id
    LEFT JOIN clientes c ON p.cliente_id = c.id
    WHERE DATE(v.fecha_venta) = :fecha_actual
    ORDER BY v.fecha_venta DESC
";
$stmt = $conn->prepare($query);
$stmt->execute(['fecha_actual' => $fecha_actual]);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos procesados</title>
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
                <h1>Ventas del Día</h1>
                <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?>!</p>
                   <div class="menu-navegacion">
                                <ul>
                                    <li><a href="<?php echo BASE_URL; ?>/views/index.php">Home</a></li> 
                                    <li><a href="<?php echo BASE_URL; ?>/views/pedidos.php">Pedidos</a></li> 
                                </ul>
                  </div>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-error"><?php echo $error; ?></div>
                    <?php endif; ?>
             </div>  
                <div class="lista-pedidos" id="pedidos-container">
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Teléfono</th>
                <th>Total</th>
                <th>Repartidor</th>
                <th>Pizzero</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pedidos as $pedido): ?>
                <tr>
                    <td><?php echo htmlspecialchars($pedido['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($pedido['telefono_cliente'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>$<?php echo htmlspecialchars($pedido['total'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($pedido['nombre_delivery'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($pedido['nombre_pizzero'], ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>                   
        </div>                            
    </div>
    <script src="<?php echo JS_URL; ?>/scrollManager.js"></script>
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    const scrollManagerPedidos = new ScrollManager({
      containerSelector: '#pedidos-container',
      itemSelector: 'tbody tr',
      maxHeight: 50, // Ajusta la altura máxima según necesites (en vh)
      autoScroll: false, // No necesitamos auto-scroll en la carga inicial
      smoothScroll: true, // Opcional: para un scroll suave
      scrollbarColor: '#3498db' // Opcional: cambia el color de la barra de scroll
    });
  });
</script>                              
</body>
</html>
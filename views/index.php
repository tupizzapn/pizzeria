<?php
session_start();
include __DIR__ . '/../includes/config.php';
include __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/controllers/login.php'); // Ruta corregida
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu Pizza</title>
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/styles.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Indie+Flower&family=Lugrasimo&display=swap" rel="stylesheet">
    <style>
    .contenedor_menu {
        background-image: url('<?php echo IMG_URL; ?>/backgrounds/background_menu.jpg');
        }
     

    </style>
      
</head>
<body>
    <div id="Contenedor_menu" class="contenedor_menu">
    <div class="menu"> 
        <div class="logo" > <img src="<?php echo IMG_URL; ?>/logos/logo_negro.png" alt="logo tu pizza"></div>
        <div class="titulo_font" > <h1>Gestionar</h1> </div>
        <div>
            <nav>
            <ul class="menu_vertical">
                <?php if ($_SESSION['rol'] === 'admin'): ?>
                <li data-icon="🍕"><a href="<?php echo BASE_URL; ?>/controllers/gestionar_pizzas.php">Pizzas</a> </li>
                <li data-icon="🍄"><a href="<?php echo BASE_URL; ?>/controllers/gestionar_toppins.php">Toppings</a> </li>
                <li data-icon="👤"><a href="<?php echo BASE_URL; ?>/controllers/gestionar_usuarios.php">Usuarios</a> </li>
            <?php endif; ?>
            <?php if ($_SESSION['rol'] === 'vendedor'): ?>
                <li data-icon="🛒"><a href="<?php echo BASE_URL; ?>/views/pedidos_procesados.php">Ventas</a> </li>
                <li data-icon="📋"><a href="<?php echo BASE_URL; ?>/views/pedidos.php">Pedidos</a> </li>
            <?php endif; ?>
                <li data-icon="🚪"><a href="<?php echo BASE_URL; ?>/controllers/logout.php">Cerrar sesión</a> </li>
            </ul>
            </nav>
        </div>

    </div>


  </div>
    
</body>
</html>
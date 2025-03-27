<?php
// Configuración de rutas
define('BASE_URL', '/pizzeria'); // Cambia esto según tu entorno
define('CONTROLLERS_DIR', __DIR__ . '/../controllers');
define('VIEWS_DIR', __DIR__ . '/../views');
define('CSS_DIR', __DIR__ . '/../public/css'); // Ruta actualizada para CSS
define('IMG_DIR', __DIR__ . '/../public/img'); // Nueva ruta para imágenes
define('INCLUDES_DIR', __DIR__);

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'pizzeria');
define('DB_USER', 'achebecerra');
define('DB_PASSWORD', '971#$Sbas');

// Configuración adicional para rutas públicas
define('PUBLIC_URL', BASE_URL . '/public'); // URL base para archivos públicos
define('CSS_URL', PUBLIC_URL . '/css');     // URL para archivos CSS
define('IMG_URL', PUBLIC_URL . '/img');     // URL para imágenes
define('JS_URL', PUBLIC_URL . '/js');     // URL para Javascript
?>
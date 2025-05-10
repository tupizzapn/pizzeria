<?php
// procesar_asignacion.php - Versión de prueba
header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Prueba de Ejecución</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 50px;
            background-color: #f0f0f0;
        }
        h1 {
            color: #2c3e50;
        }
        .info {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin: 20px auto;
            max-width: 600px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <h1>¡Hola Mundo!</h1>
    
    <div class='info'>
        <h2>Esta es una página de prueba</h2>
        <p>El archivo procesar_asignacion.php se está ejecutando correctamente.</p>
        <p><strong>Método de solicitud:</strong> ".$_SERVER['REQUEST_METHOD']."</p>
    </div>";

// Mostrar datos recibidos si es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<div class='info'>
            <h3>Datos Recibidos (POST):</h3>
            <pre>".print_r($_POST, true)."</pre>
          </div>";
}

echo "</body>
</html>";
exit;
?>
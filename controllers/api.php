<?php
session_start();
header('Content-Type: application/json');

// Verificar si la constante BASE_URL está definida
if (!defined('BASE_URL')) {
    include __DIR__ . '/../includes/config.php';
}

include __DIR__ . '/../includes/db.php';

// Verificación de sesión y rol mejorada
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit();
}

if ($_SESSION['rol'] !== 'admin') {
    echo json_encode(['error' => 'Acceso no autorizado']);
    exit();
}

// Procesamiento de la solicitud
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['username'])) {
    try {
        $username = trim($_GET['username']);
        $excludeId = isset($_GET['exclude_id']) ? (int)$_GET['exclude_id'] : null;
        
        if (empty($username)) {
            echo json_encode(['error' => 'Nombre de usuario requerido']);
            exit();
        }

        $query = "SELECT id FROM usuarios WHERE username = ?";
        $params = [$username];
        
        if ($excludeId) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        
        echo json_encode([
            'existe' => (bool)$stmt->fetch(),
            'valid' => true
        ]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error en la base de datos']);
    }
} 
// Agregar después de la sección de usuarios en api.php
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['nombre_pizza'])) {
    try {
        $nombre = trim($_GET['nombre_pizza']);
        $tamaño = trim($_GET['tamaño_pizza']);
        $excludeId = isset($_GET['exclude_id']) ? (int)$_GET['exclude_id'] : null;
        
        $query = "SELECT id FROM pizzas WHERE nombre = ? AND tamaño = ?";
        $params = [$nombre, $tamaño];
        
        if ($excludeId) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        
        echo json_encode([
            'existe' => (bool)$stmt->fetch(),
            'valid' => true
        ]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error en la base de datos']);
    }
}
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['nombre_topping'])) {
    try {
        $nombre = trim($_GET['nombre_topping']);
        $excludeId = isset($_GET['exclude_id']) ? (int)$_GET['exclude_id'] : null;
        
        $query = "SELECT id FROM toppings WHERE nombre = ?";
        $params = [$nombre];
        
        if ($excludeId) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        
        echo json_encode([
            'existe' => (bool)$stmt->fetch(),
            'valid' => true
        ]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error en la base de datos']);
    }
}
// ... (código existente) ...

elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_precios') {
    // Permitir tanto a admin como a vendedor
    if ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'vendedor') {
        echo json_encode(['error' => 'Acceso no autorizado']);
        exit();
    }
    
    try {
        // Obtener precios de pizzas
        $pizzas = $conn->query("SELECT id, nombre, tamaño, precio FROM pizzas")->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener precios de toppings
        $toppings = $conn->query("SELECT id, nombre, precio_familiar, precio_pequeña FROM toppings")->fetchAll(PDO::FETCH_ASSOC);
        
        // Estructurar la respuesta
        $response = [
            'pizzas' => array_map(function($pizza) {
                return [
                    'id' => $pizza['id'],
                    'nombre' => $pizza['nombre'],
                    'tamaño' => $pizza['tamaño'],
                    'precio' => $pizza['precio']
                ];
            }, $pizzas),
            'toppings' => array_map(function($topping) {
                return [
                    'id' => $topping['id'],
                    'nombre' => $topping['nombre'],
                    'precio_familiar' => $topping['precio_familiar'],
                    'precio_pequeña' => $topping['precio_pequeña']
                ];
            }, $toppings)
        ];
        
        echo json_encode($response);
        
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al obtener precios', 'details' => $e->getMessage()]);
    }
}

else {
    echo json_encode(['error' => 'Solicitud inválida']);
}
?>

# Sistema de Gestión para Pizzería 🍕

Sistema completo para administrar pedidos, usuarios y productos de una pizzería, con roles diferenciados y flujo de trabajo optimizado.

## 🌟 Características Principales
- Gestión completa de pedidos (creación, seguimiento, confirmación)
- Administración de usuarios con 4 roles diferentes
- Catálogo de pizzas y toppings personalizables
- Sistema de asignación de personal (pizzero/delivery)
- Integración con WhatsApp para comunicación con clientes
- Interfaz intuitiva y responsive

## 🛠 Tecnologías Utilizadas
| Área          | Tecnologías                 |
|---------------|-----------------------------|
| Frontend      | HTML5, CSS3, JavaScript ES6 |
| Backend       | PHP 8+                      |
| Base de Datos | MySQL 8                     |
| Servidor      | Apache HTTP Server          |
| Desarrollo    | Visual Studio Code          |

## 🚀 Instalación Rápida

1. Clonar repositorio:
```bash
git clone https://github.com/tu-usuario/pizzeria.git
cd pizzeria

Flujo de Trabajo

graph TD
    A[Consulta WhatsApp] --> B[Registro Pedido]
    B --> C{Confirmación Cliente}
    C -->|Sí| D[Cambio a Estado "Venta"]
    D --> E[Asignación Delivery]
    E --> F[Notificación a Pizzero/Delivery]
    F --> G[Preparación y Entrega]
    G --> H[Registro en Ventas]

┌───────────┐     ┌───────────────┐     ┌─────────────────┐
│  pedidos  │───┐ │detalles_pedido│◄──┐ │ toppings_pedido │
└───────────┘   │ └───────────────┘   │ └─────────────────┘
  ▲           │   ▲                 │     ▲
  │           └───┤                 └─────┤
┌───────────┐     └──┐                ┌───┴──────────┐
│ clientes  │        │  ┌───────────┐ │   toppings   │
└───────────┘        └─►│  pizzas   │─┘ └────────────┘
                        └───────────┘

# Tablas de la Base de Datos
pedidos: Almacena los pedidos realizados.
detalles_pedido: Detalles de cada pedido (pizzas y toppings).
toppings_pedido: Toppings específicos para cada pizza en un pedido.
clientes: Información de los clientes.
pizzas: Catálogo de pizzas disponibles.
toppings: Catálogo de toppings disponibles.
usuarios: Usuarios del sistema (admin, vendedores, pizzero. delivery).
delivery: informacion del personal que hace las entregas
pizzero: Informacion del personal que realiza la pizza
ventas: Informacion de pedidos confirmados


Rol	Permisos
Admin	Gestión completa del sistema
Vendedor	Creación/modificación de pedidos
Pizzero	Visualización de pedidos asignados
Delivery	Acceso a información de entregas


# Tablas del Proyecto 
pedidos
    |id
    |cliente_id
    |fecha_pedido
    |total
    |estado
    |direccion_entrega
    |requiere_delivery

detalles_pedido
    |id
    |pedido_id
    |pizza_id
    |cantidad

toppings_pedido
    |id
    |detalle_pedido_id
    |topping_id

clientes
    |id
    |nombre
    |telefono
    |direccion    

pizzas
    |id
    |nombre
    |tamaño enum('Familiar', 'Pequeña')
    |precio
    |activo
    |fecha_eliminacion         

toppings
    |id
    |nombre
    |precio_familiar
    |precio_pequeña
    |activo
    |fecha_eliminacion       
    |cantidad_familiar  
    |cantidad_pequeña          


usuarios
    |id
    |username
    |nombre
    |telefono        
    |password
    |rol enum(ádmin, 'vendedor', pizzero, delivery)
    |created_at
    |fecha_eliminacion        

delivery
    |id
    |usuario_id
    |nombre
    |telefono

pizzero
    id
    |usuario_id
    |nombre
    |telefono

ventas
    id
    |pedido_id
    |delivery_id
    |pizzero_id
    |fecha_venta    


# Estructura
## Estructura Actual del Proyecto

```bash
# Ejecutar este comando desde la raíz del proyecto para ver la estructura actual
find . -type f | grep -v 'node_modules' | grep -v 'vendor' | grep -v '.git' | sort

var/www/html/proyecto/          # Directorio raíz del proyecto
│
├── public/  
|     |──   css             # Directorio público (accesible desde el navegador)
│     |      |── style.css     
│     |       
│     |── img
|     |    |── backgrounds
|     |    |        |── background.png
|     |    |        |── background_adm.jpg
|     |    |        |── background_menu.jpg
|     |    |        |── background_sm.jpg
|     |    |        |── nota_sf.png
|     |    |── icons
|     |    |── logos
|     |    |     |── logo.png
|     |    |     |── logo_negro.png
|     |    |── .htacces
|     |
|     |── js
|     |    |── bienvenida.js
|     |    |── gestion_usuario.js 
|     |    |── gestion_pizza.js 
|     |    |── gestion_topping.js 
|     |    |── .htacces
│     |── .htacces 
│
├── controllers/                 # Controladores (no accesibles desde el navegador)
|   ├── agregar_direccion.php
|   |── api.php 
|   ├── cambiar_estado_pedido.php
│   ├── editar_pizza.php
│   ├── editar_topping.php
│   ├── editar_usuario.php
│   ├── eliminar_pizza.php
│   ├── eliminar_topping.php
│   ├── eliminar_usuario.php
│   ├── gestionar_pizzas.php
│   ├── gestionar_toppins.php
│   ├── gestionar_usuario.php
│   ├── login.php
│   ├── logout.php
|   ├── procesar_asignacion.php 
│   └── procesar_pedido.php
|   └── verificar_usuario.php 
│
├── views/                       # Vistas (accesibles desde el navegador)
│   ├── layouts/
│   │   
│   ├── partials/
│   |
|   ├── .htaacces
|   ├── asignar_delivery.php
|   ├── confirmacion_pedido.php
│   ├── index.php
|   ├── pedidos_procesados.php
│   ├── pedidos.php
│   ├── realizar_pedido.php
│   |── resumen_pedido.php
│   ├── .htacces
|
├── includes/                    # Archivos sensibles (no accesibles desde el navegador)
│   ├── db.php
│   |── config.php
|   └── .htacces
│
├── sql/                         # Archivos SQL (no accesibles desde el navegador)
│   └── pizzeria.sql
│
└── .htaccess                    # Configuración global de Apache

# Licencias

### 4. **Próximos Pasos**
- **Implementar el código**: Comienza a desarrollar los controladores y vistas basados en la estructura que has definido.
- **Pruebas**: Asegúrate de probar cada funcionalidad (registro de pedidos, gestión de usuarios, etc.).
- **Documentación**: Mantén el `README.md` actualizado a medida que avanzas.

 Flujo de pedidos
 1 El vendedor recibe por whatsap consultas de pizzas
 2 En la seccion realizar pedido coloca nombre (Hay que modificar para que no sea obligatorio) telefono (obligatorio) carga pedido y comparte con el cliente el pedido con los metodos de pago.
 3 En ver pedidos deben mostrarse los pedidos del dia.
 4 Una vez el cliente acepte el pedido (el pago puede ser al momento o a la entrega). en la views pedido el vendedor cambia de estatus el pedido a Venta y se pasa a la vista de asignar_delivery.
 5 En esta seccion se carga el nombre (en caso de no haberlo proporcionado, Obligatorio).
 6 Si necesita delivery el cliente enviara su ubicacion para la entrega. el vendedor coloca la direccion o un link de ubicacion.
 7 Asigna Personal para la entrega y preparacion de la pizza.
 8 se debe almacenar en la tabla ventas (no programada actualmente) Pedido con los detalles, personal de delivery, personal de preparacion de la pizza, para futuras consulta.
 9 Se envia por whatsap el pedido identificado por el numero telefonico al pizzero. se envia en caso de requerir entrega al repartidor el numero y la direccion del cliente.

 Rol
 admin. Administrador carga al sistema pizza, toppins, usuario y su rol.
 vendedor administra pedidos
 pizzero prepara la pizza (necesitara entrar al sistema a fututo)
 delivery reparte la pizza (No necesita instervencion en el sistema)

 Integracion de pago
 No requerido

 Pruebas
 No se ha llegado a esta etapa 

 ## 🔐 Validación de Usuarios

El sistema implementa:
- Verificación AJAX en tiempo real de nombres de usuario
- Protocolo de seguridad:
  ```javascript
  // Ejemplo de llamada segura
  fetch(`${API_BASE_URL}/api.php?username=${encodeURIComponent(username)}`)


  //Paquetes para integracion//

 //************************************************************************//
  config.php

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

//**************************************************************************//
 db.php
 <?php
include __DIR__ . '/config.php';

try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
    exit();
}
?>

//************************************************************************//
api.php
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



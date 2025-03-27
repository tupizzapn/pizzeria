# Sistema de Pizzería

Este es un sistema de gestión de pedidos para una pizzería. Permite a los clientes realizar pedidos, gestionar usuarios y administrar pizzas y toppings.

## 🚀 Instalación

1. Clona el repositorio:
   ```bash
   git clone https://github.com/tu-usuario/pizzeria.git

# Tecnologias utilizadas
Visual code Studio
PHP
MySQL
HTML/CSS/JavaScript
Apache (servidor web)

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

pizzas
    |id
    |nombre
    |tamaño enum('Familiar', 'Pequeña')
    |precio

toppings
    |id
    |nombre
    |precio_familiar
    |precio_pequeña

usuarios
    |id
    |username
    |password
    |rol enum(ádmin, 'vendedor', pizzero, delivery)

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
|     |    |── icons
|     |    |── logos
|     |    |     |── logo.png
|     |    |     |── logo_negro.png
|     |    |── .htacces
|     |
|     |── js
|     |    |── bienvenida.js
|     |    |── gestion_usuario.js 
|     |    |── gestion_usuario.js 
|     |    |── .htacces
│     |── .htacces 
│
├── controllers/                 # Controladores (no accesibles desde el navegador)
|   ├── agregar_direccion.php
|   ├── cambiar_estado_pedido.php
│   ├── editar_pizza.php
│   ├── editar_topping.php
│   ├── editar_usuario.php
│   ├── eliminar_pizza.php
│   ├── eliminar_topping.php
│   ├── eliminar_usuario.php
│   ├── gestionar_usuario.php
│   ├── gestionar.php
│   ├── login.php
│   ├── logout.php
|   ├── procesar_asignacion.php 
│   └── procesar_pedido.php
|   |── api.php 
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
 

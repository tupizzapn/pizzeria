<?php
// includes/functions.php

/**
 * Formatea un número de teléfono para uso en enlaces de WhatsApp
 * @param string $telefono Número en cualquier formato local/internacional
 * @return string Número formateado internacionalmente sin símbolos
 */
function formatearTelefonoWhatsApp($telefono) {
    // Eliminar todo excepto dígitos
    $telefono = preg_replace('/\D/', '', $telefono);
    
    // Verificar si el número está vacío después de limpiar
    if (empty($telefono)) {
        return '';
    }

    // Si empieza con 0 (formato local), reemplazar por 58 (código Venezuela)
    if (strpos($telefono, '0') === 0) {
        $telefono = '58' . substr($telefono, 1);
    }
    
    // Si tiene 10 dígitos y no tiene código de país, agregar 58
    if (strlen($telefono) === 10 && strpos($telefono, '58') !== 0) {
        $telefono = '58' . $telefono;
    }
    
    return $telefono;
}

/**
 * Obtiene el nombre del rol a partir del valor en la base de datos
 * @param string $rol Valor del campo 'rol' en la tabla usuarios
 * @return string Nombre legible del rol
 */
function obtenerNombreRol($rol) {
    $roles = [
        'admin' => 'Administrador',
        'vendedor' => 'Vendedor',
        'pizzero' => 'Pizzero',
        'delivery' => 'Repartidor'
    ];
    return $roles[$rol] ?? 'Desconocido';
}
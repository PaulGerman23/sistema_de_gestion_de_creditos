<?php
/**
 * FUNCIONES DE VALIDACIÓN REUTILIZABLES
 * Sistema de Gestión de Créditos
 * 
 * Este archivo contiene todas las funciones de validación
 * que se utilizan en diferentes módulos del sistema.
 */

/**
 * Valida un email
 * @param string $email Email a validar
 * @return mixed true si es válido, string con mensaje de error si no lo es
 */
function validarEmail($email) {
    if (empty($email)) return true; // Email es opcional
    
    // Validar formato básico
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "El formato del email no es válido";
    }
    
    // Validar longitud
    if (strlen($email) > 100) {
        return "El email no puede tener más de 100 caracteres";
    }
    
    // Validar dominios permitidos
    $dominios_permitidos = [
        'gmail.com', 
        'hotmail.com', 
        'hotmail.ar',
        'hotmail.es',
        'yahoo.com', 
        'yahoo.com.ar',
        'yahoo.es',
        'outlook.com',
        'outlook.com.ar',
        'outlook.es'
    ];
    
    $partes = explode('@', $email);
    if (count($partes) == 2) {
        $dominio = strtolower($partes[1]);
        if (!in_array($dominio, $dominios_permitidos)) {
            return "El dominio del email debe ser uno de los siguientes: gmail.com, hotmail.com, yahoo.com, outlook.com (y sus variantes .ar, .es)";
        }
    }
    
    return true;
}

/**
 * Valida un nombre o apellido
 * @param string $nombre Nombre a validar
 * @param string $campo Nombre del campo para mensajes de error
 * @return mixed true si es válido, string con mensaje de error si no lo es
 */
function validarNombre($nombre, $campo = 'nombre') {
    if (empty($nombre)) {
        return "El $campo es obligatorio";
    }
    
    // Eliminar espacios extras
    $nombre = preg_replace('/\s+/', ' ', trim($nombre));
    
    if (strlen($nombre) < 2) {
        return "El $campo debe tener al menos 2 caracteres";
    }
    
    if (strlen($nombre) > 100) {
        return "El $campo no puede tener más de 100 caracteres";
    }
    
    // Solo letras, espacios, tildes y caracteres especiales del español
    if (!preg_match("/^[a-záéíóúñüA-ZÁÉÍÓÚÑÜ\s]+$/u", $nombre)) {
        return "El $campo solo puede contener letras y espacios";
    }
    
    return true;
}

/**
 * Valida un DNI argentino
 * @param string $dni DNI a validar
 * @return mixed DNI limpio si es válido, string con mensaje de error si no lo es
 */
function validarDNI($dni) {
    if (empty($dni)) {
        return "El DNI es obligatorio";
    }
    
    // Eliminar puntos, espacios y guiones
    $dni = preg_replace('/[.\s\-]/', '', $dni);
    
    // Solo números
    if (!preg_match('/^\d+$/', $dni)) {
        return "El DNI solo puede contener números";
    }
    
    // Longitud válida para DNI argentino (7 u 8 dígitos)
    if (strlen($dni) < 7 || strlen($dni) > 8) {
        return "El DNI debe tener 7 u 8 dígitos";
    }
    
    // Validar que no sean todos números iguales
    if (preg_match('/^(\d)\1+$/', $dni)) {
        return "El DNI no es válido";
    }
    
    // Validar que no sea un número secuencial obvio
    if ($dni == '12345678' || $dni == '11111111' || $dni == '00000000') {
        return "El DNI no es válido";
    }
    
    return $dni; // Retornar DNI limpio
}

/**
 * Valida un teléfono argentino
 * @param string $telefono Teléfono a validar
 * @return mixed Teléfono limpio si es válido, string con mensaje de error si no lo es, true si está vacío
 */
function validarTelefono($telefono) {
    if (empty($telefono)) return true; // Teléfono es opcional
    
    // Eliminar espacios, guiones, paréntesis y el símbolo +
    $telefono_limpio = preg_replace('/[\s\-\(\)]/', '', $telefono);
    
    // Quitar el código de país si existe (+54 o 54)
    $telefono_limpio = preg_replace('/^\+?54/', '', $telefono_limpio);
    
    // Solo números
    if (!preg_match('/^\d+$/', $telefono_limpio)) {
        return "El teléfono solo puede contener números";
    }
    
    // Longitud válida para teléfono argentino (10 dígitos: código de área + número)
    if (strlen($telefono_limpio) < 10 || strlen($telefono_limpio) > 11) {
        return "El teléfono debe tener 10 dígitos (código de área + número, ej: 3814567890)";
    }
    
    // Validar que no sean todos números iguales
    if (preg_match('/^(\d)\1+$/', $telefono_limpio)) {
        return "El teléfono no es válido";
    }
    
    return $telefono_limpio;
}

/**
 * Valida una ciudad
 * @param string $ciudad Ciudad a validar
 * @return mixed true si es válido, string con mensaje de error si no lo es
 */
function validarCiudad($ciudad) {
    if (empty($ciudad)) return true; // Ciudad es opcional
    
    if (strlen($ciudad) < 2) {
        return "La ciudad debe tener al menos 2 caracteres";
    }
    
    if (strlen($ciudad) > 100) {
        return "La ciudad no puede tener más de 100 caracteres";
    }
    
    // Solo letras, espacios, puntos y guiones
    if (!preg_match("/^[a-záéíóúñüA-ZÁÉÍÓÚÑÜ\s\.\-]+$/u", $ciudad)) {
        return "La ciudad solo puede contener letras, espacios, puntos y guiones";
    }
    
    return true;
}

/**
 * Valida un monto (para créditos o pagos)
 * @param mixed $monto Monto a validar
 * @param float $minimo Monto mínimo permitido
 * @param float $maximo Monto máximo permitido
 * @return mixed true si es válido, string con mensaje de error si no lo es
 */
function validarMonto($monto, $minimo = 100, $maximo = 1000000) {
    if (empty($monto) && $monto !== '0') {
        return "El monto es obligatorio";
    }
    
    // Eliminar comas y espacios
    $monto = str_replace([',', ' '], '', $monto);
    
    // Validar que sea un número
    if (!is_numeric($monto)) {
        return "El monto debe ser un número válido";
    }
    
    $monto = floatval($monto);
    
    if ($monto < $minimo) {
        return "El monto mínimo es $" . number_format($minimo, 2);
    }
    
    if ($monto > $maximo) {
        return "El monto máximo es $" . number_format($maximo, 2);
    }
    
    if ($monto < 0) {
        return "El monto no puede ser negativo";
    }
    
    return true;
}

/**
 * Valida una cantidad de cuotas
 * @param mixed $cuotas Cantidad de cuotas a validar
 * @param int $minimo Cantidad mínima de cuotas
 * @param int $maximo Cantidad máxima de cuotas
 * @return mixed true si es válido, string con mensaje de error si no lo es
 */
function validarCuotas($cuotas, $minimo = 1, $maximo = 120) {
    if (empty($cuotas)) {
        return "La cantidad de cuotas es obligatoria";
    }
    
    if (!is_numeric($cuotas)) {
        return "La cantidad de cuotas debe ser un número";
    }
    
    $cuotas = intval($cuotas);
    
    if ($cuotas < $minimo) {
        return "La cantidad mínima de cuotas es $minimo";
    }
    
    if ($cuotas > $maximo) {
        return "La cantidad máxima de cuotas es $maximo";
    }
    
    return true;
}

/**
 * Valida una tasa de interés
 * @param mixed $interes Tasa de interés a validar
 * @return mixed true si es válido, string con mensaje de error si no lo es
 */
function validarInteres($interes) {
    // El interés puede ser 0
    if ($interes === '' || $interes === null) {
        return "El interés es obligatorio (puede ser 0 para créditos sin interés)";
    }
    
    if (!is_numeric($interes)) {
        return "El interés debe ser un número válido";
    }
    
    $interes = floatval($interes);
    
    if ($interes < 0) {
        return "El interés no puede ser negativo";
    }
    
    if ($interes > 100) {
        return "El interés no puede ser mayor al 100%";
    }
    
    return true;
}

/**
 * Valida una fecha
 * @param string $fecha Fecha a validar (formato Y-m-d)
 * @param bool $permitir_pasado Si se permite una fecha pasada
 * @return mixed true si es válido, string con mensaje de error si no lo es
 */
function validarFecha($fecha, $permitir_pasado = false) {
    if (empty($fecha)) {
        return "La fecha es obligatoria";
    }
    
    // Validar formato
    $partes = explode('-', $fecha);
    if (count($partes) != 3) {
        return "El formato de fecha debe ser YYYY-MM-DD";
    }
    
    list($anio, $mes, $dia) = $partes;
    
    if (!checkdate($mes, $dia, $anio)) {
        return "La fecha no es válida";
    }
    
    // Validar que no sea una fecha pasada si no se permite
    if (!$permitir_pasado) {
        $fecha_obj = new DateTime($fecha);
        $hoy = new DateTime();
        $hoy->setTime(0, 0, 0);
        
        if ($fecha_obj < $hoy) {
            return "La fecha no puede ser anterior a hoy";
        }
    }
    
    return true;
}

/**
 * Sanitiza un string para prevenir XSS
 * @param string $string String a sanitizar
 * @return string String sanitizado
 */
function sanitizarString($string) {
    return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
}

/**
 * Valida que un ID sea válido
 * @param mixed $id ID a validar
 * @param string $nombre_campo Nombre del campo para mensajes
 * @return mixed true si es válido, string con mensaje de error si no lo es
 */
function validarID($id, $nombre_campo = 'ID') {
    if (empty($id)) {
        return "El $nombre_campo es obligatorio";
    }
    
    if (!is_numeric($id)) {
        return "El $nombre_campo debe ser un número";
    }
    
    $id = intval($id);
    
    if ($id <= 0) {
        return "El $nombre_campo debe ser un número positivo";
    }
    
    return true;
}

/**
 * Valida que un campo select tenga un valor válido
 * @param mixed $valor Valor a validar
 * @param array $valores_permitidos Array de valores permitidos
 * @param string $nombre_campo Nombre del campo para mensajes
 * @return mixed true si es válido, string con mensaje de error si no lo es
 */
function validarSelect($valor, $valores_permitidos, $nombre_campo = 'campo') {
    if (empty($valor)) {
        return "Debe seleccionar un $nombre_campo";
    }
    
    if (!in_array($valor, $valores_permitidos)) {
        return "El valor seleccionado para $nombre_campo no es válido";
    }
    
    return true;
}

/**
 * Capitaliza correctamente nombres y apellidos
 * @param string $texto Texto a capitalizar
 * @return string Texto capitalizado
 */
function capitalizarNombre($texto) {
    // Convertir a minúsculas
    $texto = mb_strtolower($texto, 'UTF-8');
    
    // Capitalizar cada palabra
    $palabras = explode(' ', $texto);
    $resultado = [];
    
    foreach ($palabras as $palabra) {
        if (!empty($palabra)) {
            // Capitalizar primera letra de cada palabra
            $resultado[] = mb_strtoupper(mb_substr($palabra, 0, 1, 'UTF-8'), 'UTF-8') . 
                          mb_substr($palabra, 1, null, 'UTF-8');
        }
    }
    
    return implode(' ', $resultado);
}

/**
 * Valida una descripción de crédito
 * @param string $descripcion Descripción a validar
 * @return mixed true si es válido, string con mensaje de error si no lo es
 */
function validarDescripcion($descripcion) {
    if (empty($descripcion)) {
        return "La descripción es obligatoria";
    }
    
    if (strlen($descripcion) < 5) {
        return "La descripción debe tener al menos 5 caracteres";
    }
    
    if (strlen($descripcion) > 255) {
        return "La descripción no puede tener más de 255 caracteres";
    }
    
    return true;
}
?>
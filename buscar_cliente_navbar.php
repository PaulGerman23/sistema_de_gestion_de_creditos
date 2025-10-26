<?php
// archivo: /sistema_creditos/buscar_cliente_navbar.php
include 'conexion.php';

header('Content-Type: application/json');

// Obtener el término de búsqueda
$busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';

// Validar que la búsqueda tenga al menos 2 caracteres
if (strlen($busqueda) < 2) {
    echo json_encode([
        'success' => false,
        'message' => 'Ingrese al menos 2 caracteres'
    ]);
    exit;
}

// Preparar consulta SQL
$sql = "SELECT 
            c.id_cliente, 
            c.nombre, 
            c.apellido, 
            c.dni, 
            c.telefono, 
            c.email,
            c.ciudad,
            c.estado,
            COUNT(DISTINCT cr.id_credito) as total_creditos,
            COUNT(DISTINCT CASE WHEN cr.estado = 'activo' THEN cr.id_credito END) as creditos_activos,
            COALESCE(SUM(CASE WHEN cr.estado = 'activo' THEN cr.monto_total END), 0) as deuda_total
        FROM clientes c
        LEFT JOIN creditos cr ON c.id_cliente = cr.id_cliente
        WHERE (
            c.nombre LIKE ? 
            OR c.apellido LIKE ? 
            OR c.dni LIKE ? 
            OR CONCAT(c.nombre, ' ', c.apellido) LIKE ?
            OR c.email LIKE ?
            OR c.telefono LIKE ?
        )
        GROUP BY c.id_cliente
        ORDER BY c.nombre, c.apellido
        LIMIT 8";

$stmt = $conn->prepare($sql);
$busqueda_param = "%{$busqueda}%";
$stmt->bind_param("ssssss", $busqueda_param, $busqueda_param, $busqueda_param, $busqueda_param, $busqueda_param, $busqueda_param);
$stmt->execute();
$resultado = $stmt->get_result();

$clientes = [];

while ($cliente = $resultado->fetch_assoc()) {
    // Determinar badge según estado
    $badge_class = 'secondary';
    $badge_text = 'Inactivo';
    
    if ($cliente['estado'] == 'activo') {
        if ($cliente['creditos_activos'] > 0) {
            $badge_class = 'warning';
            $badge_text = $cliente['creditos_activos'] . ' crédito(s) activo(s)';
        } else {
            $badge_class = 'success';
            $badge_text = 'Sin créditos';
        }
    }
    
    $clientes[] = [
        'id' => $cliente['id_cliente'],
        'nombre' => $cliente['nombre'] . ' ' . $cliente['apellido'],
        'dni' => $cliente['dni'],
        'telefono' => $cliente['telefono'] ?: 'Sin teléfono',
        'email' => $cliente['email'] ?: 'Sin email',
        'ciudad' => $cliente['ciudad'] ?: 'Sin ciudad',
        'estado' => $cliente['estado'],
        'creditos_activos' => $cliente['creditos_activos'],
        'total_creditos' => $cliente['total_creditos'],
        'deuda_total' => number_format($cliente['deuda_total'], 2, '.', ','),
        'badge_class' => $badge_class,
        'badge_text' => $badge_text
    ];
}

if (count($clientes) > 0) {
    echo json_encode([
        'success' => true,
        'clientes' => $clientes,
        'total' => count($clientes)
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No se encontraron clientes con: "' . htmlspecialchars($busqueda) . '"'
    ]);
}

$stmt->close();
$conn->close();
?>
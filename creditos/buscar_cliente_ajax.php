<?php
include '../conexion.php';

// Obtener el término de búsqueda
$busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';

// Validar que la búsqueda tenga al menos 2 caracteres
if (strlen($busqueda) < 2) {
    echo '<div class="alert alert-warning">
            <i class="fas fa-info-circle"></i> Ingrese al menos 2 caracteres para buscar
          </div>';
    exit;
}

// Preparar consulta SQL para buscar clientes activos
$sql = "SELECT c.id_cliente, c.nombre, c.apellido, c.dni, c.telefono, c.email, c.ciudad, c.direccion,
        COUNT(DISTINCT cr.id_credito) as total_creditos,
        COUNT(DISTINCT CASE WHEN cr.estado = 'activo' THEN cr.id_credito END) as creditos_activos,
        COALESCE(SUM(CASE WHEN cr.estado = 'activo' THEN cr.monto_total END), 0) as deuda_total
        FROM clientes c
        LEFT JOIN creditos cr ON c.id_cliente = cr.id_cliente
        WHERE c.estado = 'activo' 
        AND (
            c.nombre LIKE ? 
            OR c.apellido LIKE ? 
            OR c.dni LIKE ? 
            OR CONCAT(c.nombre, ' ', c.apellido) LIKE ?
            OR c.email LIKE ?
            OR c.telefono LIKE ?
        )
        GROUP BY c.id_cliente
        ORDER BY c.nombre, c.apellido
        LIMIT 15";

$stmt = $conn->prepare($sql);
$busqueda_param = "%{$busqueda}%";
$stmt->bind_param("ssssss", $busqueda_param, $busqueda_param, $busqueda_param, $busqueda_param, $busqueda_param, $busqueda_param);
$stmt->execute();
$resultado = $stmt->get_result();

// Verificar si hay resultados
if ($resultado->num_rows > 0) {
    echo '<div class="card shadow-sm mb-3">';
    echo '<div class="card-header bg-primary text-white py-2">';
    echo '<small><i class="fas fa-search"></i> Se encontraron <strong>' . $resultado->num_rows . '</strong> cliente(s)</small>';
    echo '</div>';
    echo '<div class="list-group list-group-flush">';
    
    while ($cliente = $resultado->fetch_assoc()) {
        $nombre_completo = htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']);
        $dni = htmlspecialchars($cliente['dni']);
        $telefono = htmlspecialchars($cliente['telefono'] ?: 'Sin teléfono');
        $email = htmlspecialchars($cliente['email'] ?: 'Sin email');
        $ciudad = htmlspecialchars($cliente['ciudad'] ?: 'Sin ciudad');
        $direccion = htmlspecialchars($cliente['direccion'] ?: 'Sin dirección');
        $id = $cliente['id_cliente'];
        $total_creditos = $cliente['total_creditos'];
        $creditos_activos = $cliente['creditos_activos'];
        $deuda_total = number_format($cliente['deuda_total'], 2, '.', ',');
        
        // Determinar color según créditos activos
        $badge_color = 'secondary';
        $badge_icon = 'fa-user';
        if ($creditos_activos > 0) {
            $badge_color = 'warning';
            $badge_icon = 'fa-exclamation-triangle';
        }
        
        echo '<a href="registrar_credito.php?id_cliente=' . $id . '" class="list-group-item list-group-item-action p-3 border-bottom">';
        
        // Cabecera con nombre y badge
        echo '<div class="d-flex w-100 justify-content-between align-items-start mb-2">';
        echo '<div>';
        echo '<h6 class="mb-1 font-weight-bold">';
        echo '<i class="fas fa-user-circle text-primary"></i> ' . $nombre_completo;
        echo '</h6>';
        echo '</div>';
        echo '<div class="text-right">';
        echo '<span class="badge badge-' . $badge_color . '">';
        echo '<i class="fas ' . $badge_icon . '"></i> ';
        echo $creditos_activos > 0 ? $creditos_activos . ' crédito(s) activo(s)' : 'Sin créditos';
        echo '</span>';
        echo '</div>';
        echo '</div>';
        
        // Información del cliente
        echo '<div class="row small text-muted mb-2">';
        echo '<div class="col-md-6">';
        echo '<i class="fas fa-id-card text-info"></i> <strong>DNI:</strong> ' . $dni . '<br>';
        echo '<i class="fas fa-phone text-success"></i> <strong>Tel:</strong> ' . $telefono . '<br>';
        echo '<i class="fas fa-envelope text-warning"></i> <strong>Email:</strong> ' . $email;
        echo '</div>';
        echo '<div class="col-md-6">';
        echo '<i class="fas fa-map-marker-alt text-danger"></i> <strong>Ciudad:</strong> ' . $ciudad . '<br>';
        echo '<i class="fas fa-home text-secondary"></i> <strong>Dirección:</strong> ' . $direccion . '<br>';
        echo '<i class="fas fa-hashtag text-primary"></i> <strong>ID:</strong> ' . $id;
        echo '</div>';
        echo '</div>';
        
        // Información de créditos si tiene
        if ($creditos_activos > 0) {
            echo '<div class="alert alert-warning mb-0 py-2 small">';
            echo '<i class="fas fa-info-circle"></i> ';
            echo '<strong>Deuda actual:</strong> $' . $deuda_total . ' | ';
            echo '<strong>Total de créditos:</strong> ' . $total_creditos;
            echo '</div>';
        }
        
        echo '</a>';
    }
    
    echo '</div>'; // Cierre list-group
    echo '</div>'; // Cierre card
    
    if ($resultado->num_rows >= 15) {
        echo '<div class="alert alert-info mb-0">';
        echo '<i class="fas fa-lightbulb"></i> <strong>Tip:</strong> Se muestran los primeros 15 resultados. Refine su búsqueda para encontrar un cliente específico.';
        echo '</div>';
    }
    
} else {
    echo '<div class="alert alert-warning mt-2">';
    echo '<h6><i class="fas fa-exclamation-triangle"></i> No se encontraron clientes</h6>';
    echo '<p class="mb-2">No se encontraron clientes activos con el criterio: <strong>"' . htmlspecialchars($busqueda) . '"</strong></p>';
    echo '<hr>';
    echo '<p class="mb-2"><strong>Sugerencias:</strong></p>';
    echo '<ul class="mb-3">';
    echo '<li>Verifique que el cliente esté registrado y en estado <strong>ACTIVO</strong></li>';
    echo '<li>Intente buscar por nombre, apellido, DNI, email o teléfono</li>';
    echo '<li>Revise que no haya errores de tipeo</li>';
    echo '</ul>';
    echo '<a href="../clientes/registrar_cliente.php" class="btn btn-primary btn-sm">';
    echo '<i class="fas fa-user-plus"></i> Registrar Nuevo Cliente';
    echo '</a>';
    echo ' ';
    echo '<a href="../clientes/ver_clientes.php" class="btn btn-secondary btn-sm">';
    echo '<i class="fas fa-users"></i> Ver Todos los Clientes';
    echo '</a>';
    echo '</div>';
}

$stmt->close();
$conn->close();
?>
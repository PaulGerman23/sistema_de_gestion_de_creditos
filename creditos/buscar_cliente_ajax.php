<?php
include '../conexion.php';

// Obtener el término de búsqueda
$busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';

// Validar que la búsqueda tenga al menos 2 caracteres
if (strlen($busqueda) < 2) {
    echo '<div class="alert alert-warning">Ingrese al menos 2 caracteres para buscar</div>';
    exit;
}

// Preparar consulta SQL para buscar clientes activos
$sql = "SELECT id_cliente, nombre, apellido, dni, telefono, ciudad 
        FROM clientes 
        WHERE estado = 'activo' 
        AND (
            nombre LIKE ? 
            OR apellido LIKE ? 
            OR dni LIKE ? 
            OR CONCAT(nombre, ' ', apellido) LIKE ?
        )
        ORDER BY nombre, apellido
        LIMIT 10";

$stmt = $conn->prepare($sql);
$busqueda_param = "%{$busqueda}%";
$stmt->bind_param("ssss", $busqueda_param, $busqueda_param, $busqueda_param, $busqueda_param);
$stmt->execute();
$resultado = $stmt->get_result();

// Verificar si hay resultados
if ($resultado->num_rows > 0) {
    echo '<div class="list-group mt-2">';
    
    while ($cliente = $resultado->fetch_assoc()) {
        $nombre_completo = htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']);
        $dni = htmlspecialchars($cliente['dni']);
        $telefono = htmlspecialchars($cliente['telefono'] ?: 'Sin teléfono');
        $ciudad = htmlspecialchars($cliente['ciudad'] ?: 'Sin ciudad');
        $id = $cliente['id_cliente'];
        
        echo '<a href="registrar_credito.php?id_cliente=' . $id . '" class="list-group-item list-group-item-action">';
        echo '<div class="d-flex w-100 justify-content-between">';
        echo '<h6 class="mb-1"><i class="fas fa-user text-primary"></i> ' . $nombre_completo . '</h6>';
        echo '<small class="text-muted">ID: ' . $id . '</small>';
        echo '</div>';
        echo '<p class="mb-1"><strong>DNI:</strong> ' . $dni . ' | <strong>Teléfono:</strong> ' . $telefono . '</p>';
        echo '<small class="text-muted"><i class="fas fa-map-marker-alt"></i> ' . $ciudad . '</small>';
        echo '</a>';
    }
    
    echo '</div>';
    
    if ($resultado->num_rows == 10) {
        echo '<div class="alert alert-info mt-2 mb-0">';
        echo '<i class="fas fa-info-circle"></i> Se muestran los primeros 10 resultados. Refine su búsqueda para ver más.';
        echo '</div>';
    }
    
} else {
    echo '<div class="alert alert-warning mt-2">';
    echo '<i class="fas fa-exclamation-triangle"></i> No se encontraron clientes activos con el criterio: <strong>' . htmlspecialchars($busqueda) . '</strong>';
    echo '<hr>';
    echo '<small>Asegúrese de que el cliente esté registrado y en estado <strong>ACTIVO</strong>.</small><br>';
    echo '<a href="../clientes/registrar_cliente.php" class="btn btn-sm btn-primary mt-2">';
    echo '<i class="fas fa-user-plus"></i> Registrar Nuevo Cliente';
    echo '</a>';
    echo '</div>';
}

$stmt->close();
$conn->close();
?>
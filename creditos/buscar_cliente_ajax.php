<?php
include '../conexion.php';

$q = $_GET['q'] ?? '';

if (strlen($q) >= 2) {
    $q_param = "%$q%";
    $stmt = $conn->prepare("SELECT id_cliente, nombre, apellido, dni, telefono, ciudad 
                            FROM clientes 
                            WHERE (nombre LIKE ? OR apellido LIKE ? OR dni LIKE ?) 
                            AND estado = 'activo'
                            ORDER BY nombre, apellido 
                            LIMIT 10");
    $stmt->bind_param("sss", $q_param, $q_param, $q_param);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo '<div class="list-group">';
        while ($cliente = $result->fetch_assoc()) {
            echo '<a href="registrar_credito.php?id_cliente=' . $cliente['id_cliente'] . '" class="list-group-item list-group-item-action">';
            echo '<div class="d-flex w-100 justify-content-between">';
            echo '<h6 class="mb-1">' . htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']) . '</h6>';
            echo '<small class="text-muted">' . htmlspecialchars($cliente['ciudad'] ?: 'Sin ciudad') . '</small>';
            echo '</div>';
            echo '<p class="mb-1"><strong>DNI:</strong> ' . htmlspecialchars($cliente['dni']) . ' | ';
            echo '<strong>Tel:</strong> ' . htmlspecialchars($cliente['telefono'] ?: 'No registrado') . '</p>';
            echo '</a>';
        }
        echo '</div>';
    } else {
        echo '<div class="alert alert-warning mt-2">';
        echo '<i class="fas fa-exclamation-triangle"></i> No se encontraron clientes activos con ese criterio.';
        echo '</div>';
    }
} else {
    echo '<div class="alert alert-info mt-2">';
    echo '<i class="fas fa-info-circle"></i> Ingrese al menos 2 caracteres para buscar.';
    echo '</div>';
}
?>
<?php
include '../conexion.php';

$q = $_GET['q'] ?? '';
$accion = $_GET['accion'] ?? '';

if (strlen($q) >= 2) {
    $q_param = "%$q%";
    
    // Buscar solo clientes con cuotas pendientes
    $stmt = $conn->prepare("SELECT DISTINCT c.id_cliente, c.nombre, c.apellido, c.dni, c.telefono, c.ciudad,
                            (SELECT COUNT(*) FROM cuotas cu 
                             JOIN creditos cr ON cu.id_credito = cr.id_credito 
                             WHERE cr.id_cliente = c.id_cliente AND cu.estado IN ('pendiente', 'vencida')) as cuotas_pendientes
                            FROM clientes c
                            INNER JOIN creditos cr ON c.id_cliente = cr.id_cliente
                            INNER JOIN cuotas cu ON cr.id_credito = cu.id_credito
                            WHERE (c.nombre LIKE ? OR c.apellido LIKE ? OR c.dni LIKE ?)
                            AND cu.estado IN ('pendiente', 'vencida')
                            GROUP BY c.id_cliente
                            HAVING cuotas_pendientes > 0
                            ORDER BY c.nombre, c.apellido 
                            LIMIT 10");
    $stmt->bind_param("sss", $q_param, $q_param, $q_param);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo '<div class="list-group">';
        while ($cliente = $result->fetch_assoc()) {
            $url = ($accion == 'pagar') ? 'pagar_cuota.php' : 'ver_cuotas_cliente.php';
            echo '<a href="' . $url . '?id_cliente=' . $cliente['id_cliente'] . '" class="list-group-item list-group-item-action">';
            echo '<div class="d-flex w-100 justify-content-between">';
            echo '<h6 class="mb-1">' . htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']) . '</h6>';
            echo '<small><span class="badge badge-warning">' . $cliente['cuotas_pendientes'] . ' cuota(s) pendiente(s)</span></small>';
            echo '</div>';
            echo '<p class="mb-1"><strong>DNI:</strong> ' . htmlspecialchars($cliente['dni']) . ' | ';
            echo '<strong>Tel:</strong> ' . htmlspecialchars($cliente['telefono'] ?: 'No registrado') . '</p>';
            echo '</a>';
        }
        echo '</div>';
    } else {
        echo '<div class="alert alert-warning mt-2">';
        echo '<i class="fas fa-exclamation-triangle"></i> No se encontraron clientes con cuotas pendientes.';
        echo '</div>';
    }
} else {
    echo '<div class="alert alert-info mt-2">';
    echo '<i class="fas fa-info-circle"></i> Ingrese al menos 2 caracteres para buscar.';
    echo '</div>';
}
?>
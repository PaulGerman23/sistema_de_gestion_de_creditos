<?php
include '../conexion.php';

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename=creditos_exportados.xls');

echo "<table border='1'>";
echo "<tr><th>ID Cliente</th><th>Nombre</th><th>Monto Total</th><th>Estado</th><th>Fecha Inicio</th></tr>";

$sql = "SELECT c.nombre, cr.monto_total, cr.estado, cr.fecha_inicio
        FROM creditos cr
        JOIN clientes c ON cr.id_cliente = c.id_cliente";

$result = $conn->query($sql);
while ($row = $result->fetch_assoc()):
    echo "<tr>";
    echo "<td>{$row['id_cliente']}</td>";
    echo "<td>{$row['nombre']}</td>";
    echo "<td>{$row['monto_total']}</td>";
    echo "<td>{$row['estado']}</td>";
    echo "<td>{$row['fecha_inicio']}</td>";
    echo "</tr>";
endwhile;
echo "</table>";
?>
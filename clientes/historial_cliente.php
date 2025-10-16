<?php
include '../conexion.php';

$id_cliente = $_GET['id'] ?? 0;

if (!$id_cliente) {
    die("Cliente no especificado.");
}

$sql = "SELECT c.nombre, c.apellido FROM clientes c WHERE c.id_cliente = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_cliente);
$stmt->execute();
$cliente = $stmt->get_result()->fetch_assoc();

if (!$cliente) {
    die("Cliente no encontrado.");
}

echo "<h3>Historial de operaciones para: {$cliente['nombre']} {$cliente['apellido']}</h3>";

// Créditos
echo "<h4>Créditos:</h4>";
$sql = "SELECT * FROM creditos WHERE id_cliente = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_cliente);
$stmt->execute();
$creditos = $stmt->get_result();

while ($credito = $creditos->fetch_assoc()):
    echo "<p>Crédito: {$credito['descripcion']} - Monto: {
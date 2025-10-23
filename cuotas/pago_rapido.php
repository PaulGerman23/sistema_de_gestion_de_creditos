<?php
include '../conexion.php';

if ($_POST) {
    $id_cuota = $_POST['id_cuota'];
    $id_cliente = $_POST['id_cliente'];
    $monto_pagado = $_POST['monto_pagado'];
    $metodo_pago = $_POST['metodo_pago'];
    $observaciones = $_POST['observaciones'] ?? '';
    
    // Obtener información de la cuota
    $stmt = $conn->prepare("SELECT cu.*, cr.id_credito FROM cuotas cu 
                            JOIN creditos cr ON cu.id_credito = cr.id_credito 
                            WHERE cu.id_cuota = ?");
    $stmt->bind_param("i", $id_cuota);
    $stmt->execute();
    $cuota = $stmt->get_result()->fetch_assoc();
    
    if ($cuota && $cuota['estado'] != 'pagada') {
        // Registrar el pago
        $stmt_pago = $conn->prepare("INSERT INTO pagos (id_cuota, monto_pagado, fecha_pago, metodo_pago, observaciones) VALUES (?, ?, NOW(), ?, ?)");
        $stmt_pago->bind_param("idss", $id_cuota, $monto_pagado, $metodo_pago, $observaciones);
        $stmt_pago->execute();
        
        // Actualizar estado de la cuota
        $stmt_upd = $conn->prepare("UPDATE cuotas SET estado = 'pagada', fecha_pago = NOW() WHERE id_cuota = ?");
        $stmt_upd->bind_param("i", $id_cuota);
        $stmt_upd->execute();
        
        // Actualizar estado del crédito
        include '../creditos/actualizar_estado_credito.php';
        actualizarEstadoCredito($cuota['id_credito'], $conn);
        
        // Redirigir con mensaje de éxito
        header("Location: ver_cuotas_cliente.php?id_cliente=$id_cliente&pago=success");
        exit;
    } else {
        header("Location: ver_cuotas_cliente.php?id_cliente=$id_cliente&pago=error");
        exit;
    }
} else {
    header("Location: ver_cuotas_cliente.php");
    exit;
}
?>
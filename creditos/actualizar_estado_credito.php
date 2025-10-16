<?php
include '../conexion.php';

// Variables para el template
$base_url = '../';
$page_title = 'Actualizar Estado de Crédito';
$active_page = 'creditos';

$mensaje = '';
$tipo_mensaje = '';

// Función para actualizar el estado del crédito basado en las cuotas
function actualizarEstadoCredito($id_credito, $conn) {
    $sql = "SELECT estado FROM cuotas WHERE id_credito = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_credito);
    $stmt->execute();
    $result = $stmt->get_result();

    $estados = [];
    while ($row = $result->fetch_assoc()) {
        $estados[] = $row['estado'];
    }

    // Determinar nuevo estado
    if (in_array('vencida', $estados)) {
        $nuevo_estado = 'moroso';
    } elseif (!in_array('pendiente', $estados) && !in_array('vencida', $estados)) {
        $nuevo_estado = 'pagado';
    } else {
        $nuevo_estado = 'activo';
    }

    $stmt2 = $conn->prepare("UPDATE creditos SET estado = ? WHERE id_credito = ?");
    $stmt2->bind_param("si", $nuevo_estado, $id_credito);
    return $stmt2->execute();
}

// Actualizar cuotas vencidas
function actualizarCuotasVencidas($conn) {
    $hoy = date('Y-m-d');
    $stmt = $conn->prepare("UPDATE cuotas SET estado = 'vencida' WHERE fecha_vencimiento < ? AND estado = 'pendiente'");
    $stmt->bind_param("s", $hoy);
    return $stmt->execute();
}

// Si se envía un ID específico
if (isset($_GET['id'])) {
    $id_credito = $_GET['id'];
    
    // Primero actualizar cuotas vencidas
    actualizarCuotasVencidas($conn);
    
    // Luego actualizar estado del crédito
    if (actualizarEstadoCredito($id_credito, $conn)) {
        $mensaje = "Estado del crédito actualizado correctamente.";
        $tipo_mensaje = "success";
    } else {
        $mensaje = "Error al actualizar el estado del crédito.";
        $tipo_mensaje = "danger";
    }
}

// Si se envía POST para actualizar todos
if ($_POST && isset($_POST['actualizar_todos'])) {
    // Actualizar todas las cuotas vencidas
    actualizarCuotasVencidas($conn);
    
    // Obtener todos los créditos activos o morosos
    $stmt = $conn->prepare("SELECT DISTINCT id_credito FROM creditos WHERE estado IN ('activo', 'moroso')");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $actualizados = 0;
    while ($row = $result->fetch_assoc()) {
        if (actualizarEstadoCredito($row['id_credito'], $conn)) {
            $actualizados++;
        }
    }
    
    $mensaje = "Se actualizaron $actualizados créditos correctamente.";
    $tipo_mensaje = "success";
}

include '../includes/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Actualizar Estados de Créditos</h1>
    <a href="ver_creditos.php" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm"></i> Volver al Listado
    </a>
</div>

<!-- Mensaje de respuesta -->
<?php if ($mensaje): ?>
<div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
    <strong><?php echo $tipo_mensaje == 'success' ? '¡Éxito!' : '¡Error!'; ?></strong> <?php echo $mensaje; ?>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<div class="row">
    <!-- Información sobre la actualización -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4 border-left-info">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-info">
                    <i class="fas fa-info-circle"></i> ¿Qué hace esta función?
                </h6>
            </div>
            <div class="card-body">
                <p class="mb-3">Esta herramienta actualiza automáticamente el estado de los créditos basándose en el estado de sus cuotas:</p>
                
                <div class="mb-3">
                    <span class="badge badge-success mr-2">ACTIVO</span>
                    <p class="small mb-0 mt-1">Cuando todas las cuotas están pendientes o algunas pagadas, sin cuotas vencidas.</p>
                </div>
                
                <div class="mb-3">
                    <span class="badge badge-danger mr-2">MOROSO</span>
                    <p class="small mb-0 mt-1">Cuando al menos una cuota está vencida.</p>
                </div>
                
                <div class="mb-3">
                    <span class="badge badge-info mr-2">PAGADO</span>
                    <p class="small mb-0 mt-1">Cuando todas las cuotas han sido pagadas.</p>
                </div>
                
                <hr>
                
                <p class="small text-muted mb-0">
                    <strong>Nota:</strong> Esta actualización también marca como "vencidas" las cuotas que superaron su fecha de vencimiento.
                </p>
            </div>
        </div>
    </div>
    
    <!-- Formulario de actualización masiva -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Actualización Masiva</h6>
            </div>
            <div class="card-body">
                <p class="mb-3">Actualice el estado de todos los créditos del sistema de una sola vez.</p>
                
                <form method="POST" action="" onsubmit="return confirm('¿Está seguro de actualizar todos los créditos? Esta acción puede tardar unos momentos.');">
                    <input type="hidden" name="actualizar_todos" value="1">
                    <button type="submit" class="btn btn-warning btn-icon-split btn-lg btn-block">
                        <span class="icon text-white-50">
                            <i class="fas fa-sync"></i>
                        </span>
                        <span class="text">Actualizar Todos los Créditos</span>
                    </button>
                </form>
                
                <hr>
                
                <p class="small text-muted mb-0">
                    <i class="fas fa-exclamation-triangle text-warning"></i> 
                    Esta operación puede tardar varios segundos si hay muchos créditos en el sistema.
                </p>
            </div>
        </div>
        
        <!-- Estadísticas actuales -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Estado Actual del Sistema</h6>
            </div>
            <div class="card-body">
                <?php
                $stats_activos = $conn->query("SELECT COUNT(*) as total FROM creditos WHERE estado = 'activo'")->fetch_assoc()['total'];
                $stats_morosos = $conn->query("SELECT COUNT(*) as total FROM creditos WHERE estado = 'moroso'")->fetch_assoc()['total'];
                $stats_pagados = $conn->query("SELECT COUNT(*) as total FROM creditos WHERE estado = 'pagado'")->fetch_assoc()['total'];
                $stats_cuotas_vencidas = $conn->query("SELECT COUNT(*) as total FROM cuotas WHERE estado = 'vencida'")->fetch_assoc()['total'];
                $stats_cuotas_pendientes = $conn->query("SELECT COUNT(*) as total FROM cuotas WHERE fecha_vencimiento < CURDATE() AND estado = 'pendiente'")->fetch_assoc()['total'];
                ?>
                
                <div class="mb-2">
                    <div class="d-flex justify-content-between">
                        <span>Créditos Activos:</span>
                        <strong class="text-success"><?php echo $stats_activos; ?></strong>
                    </div>
                </div>
                
                <div class="mb-2">
                    <div class="d-flex justify-content-between">
                        <span>Créditos Morosos:</span>
                        <strong class="text-danger"><?php echo $stats_morosos; ?></strong>
                    </div>
                </div>
                
                <div class="mb-2">
                    <div class="d-flex justify-content-between">
                        <span>Créditos Pagados:</span>
                        <strong class="text-info"><?php echo $stats_pagados; ?></strong>
                    </div>
                </div>
                
                <hr>
                
                <div class="mb-2">
                    <div class="d-flex justify-content-between">
                        <span>Cuotas Vencidas:</span>
                        <strong class="text-danger"><?php echo $stats_cuotas_vencidas; ?></strong>
                    </div>
                </div>
                
                <?php if ($stats_cuotas_pendientes > 0): ?>
                <div class="mb-2">
                    <div class="d-flex justify-content-between">
                        <span>Cuotas por marcar como vencidas:</span>
                        <strong class="text-warning"><?php echo $stats_cuotas_pendientes; ?></strong>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Lista de créditos que requieren atención -->
<?php
$creditos_atencion = $conn->query("
    SELECT c.id_credito, cl.nombre, cl.apellido, c.descripcion, c.estado,
           (SELECT COUNT(*) FROM cuotas cu WHERE cu.id_credito = c.id_credito AND cu.fecha_vencimiento < CURDATE() AND cu.estado = 'pendiente') as cuotas_por_vencer
    FROM creditos c
    JOIN clientes cl ON c.id_cliente = cl.id_cliente
    WHERE c.estado IN ('activo', 'moroso')
    HAVING cuotas_por_vencer > 0
    ORDER BY cuotas_por_vencer DESC
");

if ($creditos_atencion->num_rows > 0):
?>
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4 border-left-warning">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-warning">
                    <i class="fas fa-exclamation-triangle"></i> Créditos que Requieren Atención
                </h6>
            </div>
            <div class="card-body">
                <p class="mb-3">Los siguientes créditos tienen cuotas pendientes que ya pasaron su fecha de vencimiento:</p>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Descripción</th>
                                <th>Estado Actual</th>
                                <th>Cuotas Vencidas</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($cred = $creditos_atencion->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $cred['id_credito']; ?></td>
                                <td><?php echo $cred['nombre'] . ' ' . $cred['apellido']; ?></td>
                                <td><?php echo $cred['descripcion']; ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $cred['estado'] == 'activo' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($cred['estado']); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-danger"><?php echo $cred['cuotas_por_vencer']; ?></span>
                                </td>
                                <td class="text-center">
                                    <a href="actualizar_estado_credito.php?id=<?php echo $cred['id_credito']; ?>" 
                                       class="btn btn-warning btn-sm">
                                        <i class="fas fa-sync"></i> Actualizar
                                    </a>
                                    <a href="detalle_credito.php?id=<?php echo $cred['id_credito']; ?>" 
                                       class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
include '../includes/footer.php';
?>
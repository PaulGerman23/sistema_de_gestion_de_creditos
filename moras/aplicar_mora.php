<?php
include '../conexion.php';

// Variables para el template
$base_url = '../';
$page_title = 'Aplicar Moras';
$active_page = 'alertas';

$mensaje = '';
$tipo_mensaje = '';
$moras_aplicadas = [];

// Porcentaje de mora configurable
$porcentaje_mora = 5; // 5% por defecto

// Procesar aplicación de moras
if ($_POST && isset($_POST['aplicar_moras'])) {
    $hoy = date('Y-m-d');
    
    // Buscar cuotas vencidas que no han sido pagadas
    $sql = "SELECT c.id_cuota, c.monto_cuota, c.fecha_vencimiento, c.id_credito,
                   cr.id_cliente, cl.nombre, cl.apellido,
                   DATEDIFF(CURDATE(), c.fecha_vencimiento) as dias_vencidos
            FROM cuotas c
            JOIN creditos cr ON c.id_credito = cr.id_credito
            JOIN clientes cl ON cr.id_cliente = cl.id_cliente
            WHERE c.fecha_vencimiento < ? 
            AND c.estado = 'pendiente'";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $hoy);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $total_aplicadas = 0;
    $monto_total_moras = 0;
    
    while ($row = $result->fetch_assoc()) {
        $id_credito = $row['id_credito'];
        $id_cuota = $row['id_cuota'];
        $monto_cuota = $row['monto_cuota'];
        $dias_vencidos = $row['dias_vencidos'];
        
        // Calcular mora: porcentaje del monto de la cuota
        $monto_mora = $monto_cuota * ($porcentaje_mora / 100);
        $fecha_aplicacion = date('Y-m-d H:i:s');
        
        // Verificar si ya se aplicó mora para esta cuota hoy
        $check_mora = $conn->query("SELECT id_mora FROM moras 
                                    WHERE id_credito = $id_credito 
                                    AND DATE(fecha_aplicacion) = CURDATE() 
                                    AND id_cuota = $id_cuota");
        
        if ($check_mora->num_rows == 0) {
            // Insertar la mora
            $stmt2 = $conn->prepare("INSERT INTO moras (id_credito, id_cuota, monto_mora, fecha_aplicacion, dias_vencidos) VALUES (?, ?, ?, ?, ?)");
            $stmt2->bind_param("iidsi", $id_credito, $id_cuota, $monto_mora, $fecha_aplicacion, $dias_vencidos);
            $stmt2->execute();
            
            // Actualizar estado de la cuota a "vencida"
            $stmt3 = $conn->prepare("UPDATE cuotas SET estado = 'vencida' WHERE id_cuota = ?");
            $stmt3->bind_param("i", $id_cuota);
            $stmt3->execute();
            
            // Guardar información para mostrar
            $moras_aplicadas[] = [
                'cliente' => $row['nombre'] . ' ' . $row['apellido'],
                'id_credito' => $id_credito,
                'monto_cuota' => $monto_cuota,
                'monto_mora' => $monto_mora,
                'dias_vencidos' => $dias_vencidos
            ];
            
            $total_aplicadas++;
            $monto_total_moras += $monto_mora;
        }
    }
    
    // Actualizar estados de créditos
    include '../creditos/actualizar_estado_credito.php';
    $creditos_afectados = $conn->query("SELECT DISTINCT id_credito FROM moras WHERE DATE(fecha_aplicacion) = CURDATE()");
    while ($cred = $creditos_afectados->fetch_assoc()) {
        actualizarEstadoCredito($cred['id_credito'], $conn);
    }
    
    if ($total_aplicadas > 0) {
        $mensaje = "Se aplicaron $total_aplicadas mora(s) por un total de $" . number_format($monto_total_moras, 2);
        $tipo_mensaje = "success";
    } else {
        $mensaje = "No se encontraron cuotas vencidas pendientes para aplicar mora.";
        $tipo_mensaje = "info";
    }
}

// Obtener estadísticas de moras
$total_moras = $conn->query("SELECT COUNT(*) as total FROM moras")->fetch_assoc()['total'];
$moras_pendientes = $conn->query("SELECT COUNT(*) as total FROM moras WHERE pagada = 0")->fetch_assoc()['total'];
$monto_total_pendiente = $conn->query("SELECT SUM(monto_mora) as total FROM moras WHERE pagada = 0")->fetch_assoc()['total'] ?? 0;
$moras_hoy = $conn->query("SELECT COUNT(*) as total FROM moras WHERE DATE(fecha_aplicacion) = CURDATE()")->fetch_assoc()['total'];

// Cuotas pendientes de aplicar mora
$cuotas_pendientes_mora = $conn->query("SELECT COUNT(*) as total FROM cuotas WHERE fecha_vencimiento < CURDATE() AND estado = 'pendiente'")->fetch_assoc()['total'];

include '../includes/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-percentage"></i> Aplicar Moras Automáticas
    </h1>
    <a href="alertas_vencimientos.php" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm"></i> Volver a Alertas
    </a>
</div>

<!-- Mensaje de respuesta -->
<?php if ($mensaje): ?>
<div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
    <strong><?php echo $tipo_mensaje == 'success' ? '¡Éxito!' : 'Información'; ?></strong> <?php echo $mensaje; ?>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<!-- Estadísticas de Moras -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Cuotas para Aplicar Mora
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $cuotas_pendientes_mora; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Moras Pendientes
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $moras_pendientes; ?></div>
                        <small class="text-muted">$<?php echo number_format($monto_total_pendiente, 2); ?></small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-coins fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Moras Aplicadas Hoy
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $moras_hoy; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Moras Históricas
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_moras; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-list fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Aplicar Moras -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4 border-left-warning">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-warning">
                    <i class="fas fa-percentage"></i> Aplicar Moras Automáticamente
                </h6>
            </div>
            <div class="card-body">
                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <strong>¡Atención!</strong> Esta acción aplicará un recargo del <strong><?php echo $porcentaje_mora; ?>%</strong> 
                    a todas las cuotas vencidas que aún no tienen mora aplicada.
                </div>
                
                <form method="POST" action="" onsubmit="return confirm('¿Está seguro de aplicar moras a todas las cuotas vencidas? Esta acción no se puede deshacer.');">
                    <input type="hidden" name="aplicar_moras" value="1">
                    
                    <div class="mb-3">
                        <label class="font-weight-bold">Configuración:</label>
                        <div class="form-group">
                            <label for="porcentaje">Porcentaje de Mora:</label>
                            <div class="input-group">
                                <input type="number" 
                                       class="form-control" 
                                       id="porcentaje" 
                                       name="porcentaje" 
                                       value="<?php echo $porcentaje_mora; ?>" 
                                       min="0" 
                                       max="100" 
                                       step="0.1" 
                                       readonly>
                                <div class="input-group-append">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                La mora se calcula sobre el monto de cada cuota vencida
                            </small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="font-weight-bold">Cuotas a procesar:</label>
                        <p class="mb-0">
                            <i class="fas fa-check-circle text-success"></i> 
                            Se procesarán <strong><?php echo $cuotas_pendientes_mora; ?></strong> cuota(s) vencida(s)
                        </p>
                    </div>
                    
                    <hr>
                    
                    <button type="submit" class="btn btn-warning btn-icon-split btn-lg btn-block" <?php echo ($cuotas_pendientes_mora == 0) ? 'disabled' : ''; ?>>
                        <span class="icon text-white-50">
                            <i class="fas fa-percentage"></i>
                        </span>
                        <span class="text">Aplicar Moras Ahora</span>
                    </button>
                    
                    <?php if ($cuotas_pendientes_mora == 0): ?>
                    <p class="text-center text-muted mt-2 mb-0">
                        <small>No hay cuotas vencidas para procesar</small>
                    </p>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Información -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4 border-left-info">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-info">
                    <i class="fas fa-info-circle"></i> ¿Cómo funciona?
                </h6>
            </div>
            <div class="card-body">
                <h6 class="font-weight-bold mb-3">Proceso Automático</h6>
                <ol class="pl-3 mb-3">
                    <li>El sistema identifica todas las cuotas vencidas</li>
                    <li>Calcula el <?php echo $porcentaje_mora; ?>% sobre el monto de cada cuota</li>
                    <li>Registra la mora en la base de datos</li>
                    <li>Actualiza el estado de la cuota a "vencida"</li>
                    <li>Actualiza el estado del crédito a "moroso"</li>
                </ol>
                
                <hr>
                
                <h6 class="font-weight-bold mb-2">Importante:</h6>
                <ul class="pl-3 mb-3 small">
                    <li>Las moras se aplican solo una vez por cuota</li>
                    <li>No se duplican si ya fueron aplicadas</li>
                    <li>El registro queda guardado con fecha y hora</li>
                    <li>Los clientes pueden consultar sus moras</li>
                </ul>
                
                <hr>
                
                <h6 class="font-weight-bold mb-2">Ejemplo de Cálculo:</h6>
                <div class="alert alert-light">
                    <p class="mb-1"><strong>Cuota:</strong> $10,000</p>
                    <p class="mb-1"><strong>Mora (<?php echo $porcentaje_mora; ?>%):</strong> $<?php echo number_format(10000 * ($porcentaje_mora / 100), 2); ?></p>
                    <p class="mb-0"><strong>Total a pagar:</strong> $<?php echo number_format(10000 + (10000 * ($porcentaje_mora / 100)), 2); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Resultados de Moras Aplicadas -->
<?php if (!empty($moras_aplicadas)): ?>
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-success text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-check-circle"></i> Moras Aplicadas Exitosamente
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>Cliente</th>
                                <th>ID Crédito</th>
                                <th>Monto Cuota</th>
                                <th>Mora Aplicada</th>
                                <th>Días Vencidos</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($moras_aplicadas as $mora): ?>
                            <tr>
                                <td><?php echo $mora['cliente']; ?></td>
                                <td><?php echo $mora['id_credito']; ?></td>
                                <td class="text-right">$<?php echo number_format($mora['monto_cuota'], 2); ?></td>
                                <td class="text-right text-danger">
                                    <strong>$<?php echo number_format($mora['monto_mora'], 2); ?></strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-danger"><?php echo $mora['dias_vencidos']; ?></span>
                                </td>
                                <td class="text-right">
                                    <strong>$<?php echo number_format($mora['monto_cuota'] + $mora['monto_mora'], 2); ?></strong>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="font-weight-bold">
                            <tr class="table-info">
                                <td colspan="3" class="text-right">TOTAL MORAS APLICADAS:</td>
                                <td class="text-right text-danger">
                                    <strong>$<?php echo number_format(array_sum(array_column($moras_aplicadas, 'monto_mora')), 2); ?></strong>
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Historial de Moras -->
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-history"></i> Historial de Moras Aplicadas (Últimas 50)
                </h6>
            </div>
            <div class="card-body">
                <?php
                $historial = $conn->query("SELECT m.*, cr.descripcion, c.nombre, c.apellido, cu.numero_cuota
                                          FROM moras m
                                          JOIN creditos cr ON m.id_credito = cr.id_credito
                                          JOIN clientes c ON cr.id_cliente = c.id_cliente
                                          LEFT JOIN cuotas cu ON m.id_cuota = cu.id_cuota
                                          ORDER BY m.fecha_aplicacion DESC
                                          LIMIT 50");
                ?>
                
                <?php if ($historial->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="tablaMoras">
                        <thead class="thead-light">
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Crédito</th>
                                <th>N° Cuota</th>
                                <th>Días Vencidos</th>
                                <th>Monto Mora</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($m = $historial->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $m['id_mora']; ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($m['fecha_aplicacion'])); ?></td>
                                <td>
                                    <strong><?php echo $m['nombre'] . ' ' . $m['apellido']; ?></strong>
                                </td>
                                <td>
                                    <?php echo $m['descripcion']; ?>
                                    <br><small class="text-muted">ID: <?php echo $m['id_credito']; ?></small>
                                </td>
                                <td class="text-center">
                                    <?php if ($m['numero_cuota']): ?>
                                        <span class="badge badge-secondary"><?php echo $m['numero_cuota']; ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-danger">
                                        <?php echo $m['dias_vencidos'] ?? 0; ?> días
                                    </span>
                                </td>
                                <td class="text-right">
                                    <strong class="text-danger">$<?php echo number_format($m['monto_mora'], 2); ?></strong>
                                </td>
                                <td class="text-center">
                                    <?php if ($m['pagada']): ?>
                                        <span class="badge badge-success">Pagada</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Pendiente</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle fa-3x mb-3"></i>
                    <h5>Sin Historial</h5>
                    <p class="mb-0">No hay moras registradas en el sistema.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$extra_js = '
<script src="../vendor/datatables/jquery.dataTables.min.js"></script>
<script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script>
    $(document).ready(function() {
        $("#tablaMoras").DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
            },
            "order": [[ 1, "desc" ]],
            "pageLength": 25
        });
    });
</script>
';

include '../includes/footer.php';
?>
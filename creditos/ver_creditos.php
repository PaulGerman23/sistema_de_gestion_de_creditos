<?php
include '../conexion.php';

// Variables para el template
$base_url = '../';
$page_title = 'Ver Créditos';
$active_page = 'creditos';
$active_subpage = 'ver_creditos';

// CSS adicional
$extra_css = '<link href="../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">';

// Consulta de créditos con información del cliente
$sql = "SELECT cr.id_credito, c.nombre, c.apellido, c.dni, cr.descripcion, cr.monto_total, 
               cr.cantidad_cuotas, cr.cuota_mensual, cr.interes_anual, cr.estado, 
               cr.fecha_inicio, cr.fecha_vencimiento,
               (SELECT COUNT(*) FROM cuotas cu WHERE cu.id_credito = cr.id_credito AND cu.estado = 'pagada') as cuotas_pagadas
        FROM creditos cr
        JOIN clientes c ON cr.id_cliente = c.id_cliente
        ORDER BY cr.fecha_inicio DESC";

$result = $conn->query($sql);

include '../includes/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Gestión de Créditos</h1>
    <a href="registrar_credito.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Registrar Nuevo Crédito
    </a>
</div>

<!-- Tarjetas de Resumen -->
<div class="row mb-4">
    <?php
    $total_creditos = $conn->query("SELECT COUNT(*) as total FROM creditos")->fetch_assoc()['total'];
    $creditos_activos = $conn->query("SELECT COUNT(*) as total FROM creditos WHERE estado = 'activo'")->fetch_assoc()['total'];
    $creditos_pagados = $conn->query("SELECT COUNT(*) as total FROM creditos WHERE estado = 'pagado'")->fetch_assoc()['total'];
    $creditos_morosos = $conn->query("SELECT COUNT(*) as total FROM creditos WHERE estado = 'moroso'")->fetch_assoc()['total'];
    $monto_total = $conn->query("SELECT SUM(monto_total) as total FROM creditos WHERE estado = 'activo'")->fetch_assoc()['total'] ?? 0;
    ?>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Créditos</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_creditos; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-credit-card fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Activos</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $creditos_activos; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Morosos</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $creditos_morosos; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Monto Total Activo</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($monto_total, 2); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Créditos -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Listado Completo de Créditos</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Descripción</th>
                        <th>Monto Total</th>
                        <th>Cuotas</th>
                        <th>Cuota Mensual</th>
                        <th>Progreso</th>
                        <th>Estado</th>
                        <th>Vencimiento</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): 
                        // Determinar el badge según el estado
                        $badge_class = 'secondary';
                        if ($row['estado'] == 'activo') $badge_class = 'success';
                        elseif ($row['estado'] == 'moroso') $badge_class = 'danger';
                        elseif ($row['estado'] == 'pagado') $badge_class = 'info';
                        
                        // Calcular progreso
                        $progreso = ($row['cuotas_pagadas'] / $row['cantidad_cuotas']) * 100;
                        $progreso_class = $progreso < 30 ? 'danger' : ($progreso < 70 ? 'warning' : 'success');
                    ?>
                    <tr>
                        <td><?php echo $row['id_credito']; ?></td>
                        <td>
                            <strong><?php echo $row['nombre'] . ' ' . $row['apellido']; ?></strong>
                            <br><small class="text-muted">DNI: <?php echo $row['dni']; ?></small>
                        </td>
                        <td><?php echo $row['descripcion']; ?></td>
                        <td><strong>$<?php echo number_format($row['monto_total'], 2); ?></strong></td>
                        <td class="text-center">
                            <span class="badge badge-light"><?php echo $row['cuotas_pagadas']; ?>/<?php echo $row['cantidad_cuotas']; ?></span>
                        </td>
                        <td>$<?php echo number_format($row['cuota_mensual'], 2); ?></td>
                        <td>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-<?php echo $progreso_class; ?>" 
                                     role="progressbar" 
                                     style="width: <?php echo $progreso; ?>%;" 
                                     aria-valuenow="<?php echo $progreso; ?>" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    <?php echo round($progreso); ?>%
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $badge_class; ?>">
                                <?php echo ucfirst($row['estado']); ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($row['fecha_vencimiento'])); ?></td>
                        <td>
                            <a href="../cuotas/generar_plan_pago.php?id_credito=<?php echo $row['id_credito']; ?>" 
                               class="btn btn-info btn-sm btn-circle" 
                               title="Ver Plan de Pago">
                                <i class="fas fa-calendar-alt"></i>
                            </a>
                            <a href="detalle_credito.php?id=<?php echo $row['id_credito']; ?>" 
                               class="btn btn-primary btn-sm btn-circle" 
                               title="Ver Detalle">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if ($row['estado'] != 'pagado'): ?>
                            <a href="actualizar_estado_credito.php?id=<?php echo $row['id_credito']; ?>" 
                               class="btn btn-warning btn-sm btn-circle" 
                               title="Actualizar Estado">
                                <i class="fas fa-sync"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$extra_js = '
<script src="../vendor/datatables/jquery.dataTables.min.js"></script>
<script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script>
    $(document).ready(function() {
        $("#dataTable").DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
            },
            "order": [[ 0, "desc" ]],
            "pageLength": 25
        });
    });
</script>
';

include '../includes/footer.php';
?>
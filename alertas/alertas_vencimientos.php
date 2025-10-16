<?php
include '../conexion.php';

// Variables para el template
$base_url = '../';
$page_title = 'Alertas de Vencimiento';
$active_page = 'alertas';

// CSS adicional
$extra_css = '<link href="../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">';

$hoy = date('Y-m-d');
$limite_dias = $_GET['dias'] ?? 7;
$tipo_alerta = $_GET['tipo'] ?? 'todos';
$prioridad = $_GET['prioridad'] ?? 'todas';

$limite_fecha = date('Y-m-d', strtotime("+{$limite_dias} days"));

// Consulta de créditos próximos a vencer
$sql_creditos = "SELECT c.nombre, c.apellido, c.dni, c.telefono, c.email,
                        cr.id_credito, cr.descripcion, cr.fecha_vencimiento, cr.monto_total, 
                        cr.estado, cr.cantidad_cuotas,
                        DATEDIFF(cr.fecha_vencimiento, CURDATE()) as dias_restantes,
                        (SELECT COUNT(*) FROM cuotas cu WHERE cu.id_credito = cr.id_credito AND cu.estado = 'pendiente') as cuotas_pendientes,
                        (SELECT COUNT(*) FROM cuotas cu WHERE cu.id_credito = cr.id_credito AND cu.estado = 'vencida') as cuotas_vencidas
                 FROM creditos cr
                 JOIN clientes c ON cr.id_cliente = c.id_cliente
                 WHERE cr.fecha_vencimiento BETWEEN ? AND ? 
                 AND cr.estado = 'activo'
                 ORDER BY cr.fecha_vencimiento ASC";

$stmt_creditos = $conn->prepare($sql_creditos);
$stmt_creditos->bind_param("ss", $hoy, $limite_fecha);
$stmt_creditos->execute();
$creditos_vencer = $stmt_creditos->get_result();

// Consulta de cuotas próximas a vencer
$sql_cuotas = "SELECT c.id_cliente, c.nombre, c.apellido, c.dni, c.telefono,
                      cr.id_credito, cr.descripcion as descripcion_credito,
                      cu.id_cuota, cu.numero_cuota, cu.monto_cuota, cu.fecha_vencimiento,
                      DATEDIFF(cu.fecha_vencimiento, CURDATE()) as dias_restantes
               FROM cuotas cu
               JOIN creditos cr ON cu.id_credito = cr.id_credito
               JOIN clientes c ON cr.id_cliente = c.id_cliente
               WHERE cu.fecha_vencimiento BETWEEN ? AND ?
               AND cu.estado = 'pendiente'
               ORDER BY cu.fecha_vencimiento ASC";

$stmt_cuotas = $conn->prepare($sql_cuotas);
$stmt_cuotas->bind_param("ss", $hoy, $limite_fecha);
$stmt_cuotas->execute();
$cuotas_vencer = $stmt_cuotas->get_result();

// Consulta de cuotas vencidas
$sql_vencidas = "SELECT c.id_cliente, c.nombre, c.apellido, c.dni, c.telefono,
                        cr.id_credito, cr.descripcion as descripcion_credito, cr.estado as estado_credito,
                        cu.id_cuota, cu.numero_cuota, cu.monto_cuota, cu.fecha_vencimiento,
                        DATEDIFF(CURDATE(), cu.fecha_vencimiento) as dias_vencidos
                 FROM cuotas cu
                 JOIN creditos cr ON cu.id_credito = cr.id_credito
                 JOIN clientes c ON cr.id_cliente = c.id_cliente
                 WHERE cu.estado = 'vencida'
                 ORDER BY cu.fecha_vencimiento ASC";

$cuotas_vencidas = $conn->query($sql_vencidas);

// Estadísticas
$total_creditos_vencer = $creditos_vencer->num_rows;
$total_cuotas_vencer = $cuotas_vencer->num_rows;
$total_cuotas_vencidas = $cuotas_vencidas->num_rows;

$monto_cuotas_vencer = $conn->query("SELECT SUM(cu.monto_cuota) as total 
                                     FROM cuotas cu 
                                     WHERE cu.fecha_vencimiento BETWEEN '$hoy' AND '$limite_fecha' 
                                     AND cu.estado = 'pendiente'")->fetch_assoc()['total'] ?? 0;

$monto_cuotas_vencidas = $conn->query("SELECT SUM(cu.monto_cuota) as total 
                                       FROM cuotas cu 
                                       WHERE cu.estado = 'vencida'")->fetch_assoc()['total'] ?? 0;

// Clientes con más alertas
$clientes_criticos = $conn->query("SELECT c.id_cliente, c.nombre, c.apellido, c.dni,
                                   COUNT(cu.id_cuota) as cuotas_vencidas,
                                   SUM(cu.monto_cuota) as monto_total_vencido
                                   FROM cuotas cu
                                   JOIN creditos cr ON cu.id_credito = cr.id_credito
                                   JOIN clientes c ON cr.id_cliente = c.id_cliente
                                   WHERE cu.estado = 'vencida'
                                   GROUP BY c.id_cliente
                                   ORDER BY cuotas_vencidas DESC
                                   LIMIT 5");

include '../includes/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-bell"></i> Alertas de Vencimiento
    </h1>
    <div>
        <button onclick="window.print()" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-print fa-sm"></i> Imprimir Reporte
        </button>
        <a href="../creditos/actualizar_estado_credito.php" class="btn btn-sm btn-warning shadow-sm">
            <i class="fas fa-sync fa-sm"></i> Actualizar Estados
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-filter"></i> Configurar Alertas
                </h6>
            </div>
            <div class="card-body">
                <form method="GET" action="" class="form-inline">
                    <div class="form-group mr-3 mb-2">
                        <label for="dias" class="mr-2">Próximos:</label>
                        <select name="dias" id="dias" class="form-control" onchange="this.form.submit()">
                            <option value="3" <?php echo ($limite_dias == 3) ? 'selected' : ''; ?>>3 días</option>
                            <option value="7" <?php echo ($limite_dias == 7) ? 'selected' : ''; ?>>7 días</option>
                            <option value="15" <?php echo ($limite_dias == 15) ? 'selected' : ''; ?>>15 días</option>
                            <option value="30" <?php echo ($limite_dias == 30) ? 'selected' : ''; ?>>30 días</option>
                        </select>
                    </div>
                    
                    <span class="text-muted">
                        <i class="fas fa-info-circle"></i> 
                        Mostrando alertas hasta el <strong><?php echo date('d/m/Y', strtotime($limite_fecha)); ?></strong>
                    </span>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Resumen de Alertas -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Créditos por Vencer
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_creditos_vencer; ?></div>
                        <small class="text-muted">Próximos <?php echo $limite_dias; ?> días</small>
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
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Cuotas por Vencer
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_cuotas_vencer; ?></div>
                        <small class="text-muted">$<?php echo number_format($monto_cuotas_vencer, 2); ?></small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                            Cuotas Vencidas
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_cuotas_vencidas; ?></div>
                        <small class="text-muted">$<?php echo number_format($monto_cuotas_vencidas, 2); ?></small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-times-circle fa-2x text-gray-300"></i>
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
                            Total en Riesgo
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            $<?php echo number_format($monto_cuotas_vencer + $monto_cuotas_vencidas, 2); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabs de Alertas -->
<ul class="nav nav-tabs" id="alertasTab" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="cuotas-vencidas-tab" data-toggle="tab" href="#cuotas-vencidas" role="tab">
            <i class="fas fa-times-circle text-danger"></i> 
            Cuotas Vencidas 
            <span class="badge badge-danger"><?php echo $total_cuotas_vencidas; ?></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="cuotas-vencer-tab" data-toggle="tab" href="#cuotas-vencer" role="tab">
            <i class="fas fa-clock text-warning"></i> 
            Cuotas por Vencer 
            <span class="badge badge-warning"><?php echo $total_cuotas_vencer; ?></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="creditos-vencer-tab" data-toggle="tab" href="#creditos-vencer" role="tab">
            <i class="fas fa-exclamation-triangle text-info"></i> 
            Créditos por Vencer 
            <span class="badge badge-info"><?php echo $total_creditos_vencer; ?></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="clientes-criticos-tab" data-toggle="tab" href="#clientes-criticos" role="tab">
            <i class="fas fa-user-times text-danger"></i> 
            Clientes Críticos
        </a>
    </li>
</ul>

<div class="tab-content" id="alertasTabContent">
    
    <!-- Tab: Cuotas Vencidas -->
    <div class="tab-pane fade show active" id="cuotas-vencidas" role="tabpanel">
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-danger text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-times-circle"></i> Cuotas Vencidas - Acción Inmediata Requerida
                </h6>
            </div>
            <div class="card-body">
                <?php if ($total_cuotas_vencidas > 0): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <strong>¡Atención!</strong> Hay <?php echo $total_cuotas_vencidas; ?> cuota(s) vencida(s) que requieren acción inmediata.
                    Total adeudado: <strong>$<?php echo number_format($monto_cuotas_vencidas, 2); ?></strong>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="tablaVencidas">
                        <thead class="thead-light">
                            <tr>
                                <th>Cliente</th>
                                <th>Crédito</th>
                                <th>N° Cuota</th>
                                <th>Monto</th>
                                <th>Vencimiento</th>
                                <th>Días Vencidos</th>
                                <th>Contacto</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $cuotas_vencidas->data_seek(0);
                            while ($cuota = $cuotas_vencidas->fetch_assoc()): 
                                $prioridad_class = '';
                                if ($cuota['dias_vencidos'] > 30) {
                                    $prioridad_class = 'table-danger';
                                } elseif ($cuota['dias_vencidos'] > 15) {
                                    $prioridad_class = 'table-warning';
                                }
                            ?>
                            <tr class="<?php echo $prioridad_class; ?>">
                                <td>
                                    <strong><?php echo $cuota['nombre'] . ' ' . $cuota['apellido']; ?></strong>
                                    <br><small class="text-muted">DNI: <?php echo $cuota['dni']; ?></small>
                                </td>
                                <td>
                                    <?php echo $cuota['descripcion_credito']; ?>
                                    <br><small class="text-muted">ID: <?php echo $cuota['id_credito']; ?></small>
                                    <?php if ($cuota['estado_credito'] == 'moroso'): ?>
                                        <br><span class="badge badge-danger">MOROSO</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-dark"><?php echo $cuota['numero_cuota']; ?></span>
                                </td>
                                <td class="text-right">
                                    <strong class="text-danger">$<?php echo number_format($cuota['monto_cuota'], 2); ?></strong>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($cuota['fecha_vencimiento'])); ?></td>
                                <td class="text-center">
                                    <span class="badge badge-danger">
                                        <?php echo $cuota['dias_vencidos']; ?> días
                                    </span>
                                </td>
                                <td>
                                    <?php if ($cuota['telefono']): ?>
                                        <a href="tel:<?php echo $cuota['telefono']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-phone"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="../cuotas/pagar_cuota.php?id_cuota=<?php echo $cuota['id_cuota']; ?>" 
                                       class="btn btn-success btn-sm">
                                        <i class="fas fa-dollar-sign"></i> Cobrar
                                    </a>
                                    <a href="../creditos/detalle_credito.php?id=<?php echo $cuota['id_credito']; ?>" 
                                       class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="alert alert-success text-center">
                    <i class="fas fa-check-circle fa-3x mb-3"></i>
                    <h5>¡Excelente!</h5>
                    <p class="mb-0">No hay cuotas vencidas en este momento.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Tab: Cuotas por Vencer -->
    <div class="tab-pane fade" id="cuotas-vencer" role="tabpanel">
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-warning">
                <h6 class="m-0 font-weight-bold text-white">
                    <i class="fas fa-clock"></i> Cuotas por Vencer - Próximos <?php echo $limite_dias; ?> días
                </h6>
            </div>
            <div class="card-body">
                <?php if ($total_cuotas_vencer > 0): ?>
                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-info-circle"></i> 
                    Hay <?php echo $total_cuotas_vencer; ?> cuota(s) que vencerán en los próximos <?php echo $limite_dias; ?> días.
                    Monto total: <strong>$<?php echo number_format($monto_cuotas_vencer, 2); ?></strong>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="tablaVencer">
                        <thead class="thead-light">
                            <tr>
                                <th>Cliente</th>
                                <th>Crédito</th>
                                <th>N° Cuota</th>
                                <th>Monto</th>
                                <th>Vence</th>
                                <th>Días Restantes</th>
                                <th>Contacto</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $cuotas_vencer->data_seek(0);
                            while ($cuota = $cuotas_vencer->fetch_assoc()): 
                                $urgencia_class = '';
                                if ($cuota['dias_restantes'] <= 2) {
                                    $urgencia_class = 'table-danger';
                                    $badge_class = 'danger';
                                } elseif ($cuota['dias_restantes'] <= 5) {
                                    $urgencia_class = 'table-warning';
                                    $badge_class = 'warning';
                                } else {
                                    $badge_class = 'info';
                                }
                            ?>
                            <tr class="<?php echo $urgencia_class; ?>">
                                <td>
                                    <strong><?php echo $cuota['nombre'] . ' ' . $cuota['apellido']; ?></strong>
                                    <br><small class="text-muted">DNI: <?php echo $cuota['dni']; ?></small>
                                </td>
                                <td>
                                    <?php echo $cuota['descripcion_credito']; ?>
                                    <br><small class="text-muted">ID: <?php echo $cuota['id_credito']; ?></small>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-secondary"><?php echo $cuota['numero_cuota']; ?></span>
                                </td>
                                <td class="text-right">
                                    <strong>$<?php echo number_format($cuota['monto_cuota'], 2); ?></strong>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($cuota['fecha_vencimiento'])); ?></td>
                                <td class="text-center">
                                    <span class="badge badge-<?php echo $badge_class; ?>">
                                        <?php echo $cuota['dias_restantes']; ?> días
                                    </span>
                                </td>
                                <td>
                                    <?php if ($cuota['telefono']): ?>
                                        <a href="tel:<?php echo $cuota['telefono']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-phone"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="../cuotas/pagar_cuota.php?id_cuota=<?php echo $cuota['id_cuota']; ?>" 
                                       class="btn btn-success btn-sm">
                                        <i class="fas fa-dollar-sign"></i> Cobrar
                                    </a>
                                    <a href="../creditos/detalle_credito.php?id=<?php echo $cuota['id_credito']; ?>" 
                                       class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle fa-3x mb-3"></i>
                    <h5>Sin alertas</h5>
                    <p class="mb-0">No hay cuotas próximas a vencer en los próximos <?php echo $limite_dias; ?> días.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Tab: Créditos por Vencer -->
    <div class="tab-pane fade" id="creditos-vencer" role="tabpanel">
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-info text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-exclamation-triangle"></i> Créditos Completos por Vencer
                </h6>
            </div>
            <div class="card-body">
                <?php if ($total_creditos_vencer > 0): ?>
                <div class="alert alert-info" role="alert">
                    <i class="fas fa-info-circle"></i> 
                    Hay <?php echo $total_creditos_vencer; ?> crédito(s) que vencerán completamente en los próximos <?php echo $limite_dias; ?> días.
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="tablaCreditosVencer">
                        <thead class="thead-light">
                            <tr>
                                <th>Cliente</th>
                                <th>Descripción</th>
                                <th>Monto Total</th>
                                <th>Cuotas Pendientes</th>
                                <th>Vencimiento</th>
                                <th>Días Restantes</th>
                                <th>Contacto</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $creditos_vencer->data_seek(0);
                            while ($credito = $creditos_vencer->fetch_assoc()): 
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo $credito['nombre'] . ' ' . $credito['apellido']; ?></strong>
                                    <br><small class="text-muted">DNI: <?php echo $credito['dni']; ?></small>
                                </td>
                                <td>
                                    <?php echo $credito['descripcion']; ?>
                                    <br><small class="text-muted">ID: <?php echo $credito['id_credito']; ?></small>
                                </td>
                                <td class="text-right">
                                    <strong>$<?php echo number_format($credito['monto_total'], 2); ?></strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-warning"><?php echo $credito['cuotas_pendientes']; ?></span>
                                    <?php if ($credito['cuotas_vencidas'] > 0): ?>
                                        <span class="badge badge-danger"><?php echo $credito['cuotas_vencidas']; ?> vencidas</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($credito['fecha_vencimiento'])); ?></td>
                                <td class="text-center">
                                    <span class="badge badge-info">
                                        <?php echo $credito['dias_restantes']; ?> días
                                    </span>
                                </td>
                                <td>
                                    <?php if ($credito['telefono']): ?>
                                        <a href="tel:<?php echo $credito['telefono']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-phone"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($credito['email']): ?>
                                        <a href="mailto:<?php echo $credito['email']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-envelope"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="../creditos/detalle_credito.php?id=<?php echo $credito['id_credito']; ?>" 
                                       class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                    <a href="../cuotas/ver_cuotas_cliente.php?id_cliente=<?php echo $credito['id_cliente']; ?>" 
                                       class="btn btn-warning btn-sm">
                                        <i class="fas fa-calendar-alt"></i> Cuotas
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="alert alert-success text-center">
                    <i class="fas fa-check-circle fa-3x mb-3"></i>
                    <h5>Todo en orden</h5>
                    <p class="mb-0">No hay créditos completos próximos a vencer.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Tab: Clientes Críticos -->
    <div class="tab-pane fade" id="clientes-criticos" role="tabpanel">
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-danger text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-user-times"></i> Top 5 Clientes con Mayor Morosidad
                </h6>
            </div>
            <div class="card-body">
                <?php if ($clientes_criticos->num_rows > 0): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <strong>¡Atención!</strong> Estos clientes tienen múltiples cuotas vencidas y requieren seguimiento prioritario.
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>Posición</th>
                                <th>Cliente</th>
                                <th>DNI</th>
                                <th>Cuotas Vencidas</th>
                                <th>Monto Total Adeudado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $posicion = 1;
                            while ($cliente = $clientes_criticos->fetch_assoc()): 
                                $medal = '';
                                if ($posicion == 1) $medal = '<i class="fas fa-trophy text-danger"></i>';
                                elseif ($posicion == 2) $medal = '<i class="fas fa-medal text-danger"></i>';
                                elseif ($posicion == 3) $medal = '<i class="fas fa-award text-danger"></i>';
                            ?>
                            <tr class="table-danger">
                                <td class="text-center">
                                    <?php echo $medal; ?>
                                    <strong>#<?php echo $posicion; ?></strong>
                                </td>
                                <td>
                                    <strong><?php echo $cliente['nombre'] . ' ' . $cliente['apellido']; ?></strong>
                                </td>
                                <td><?php echo $cliente['dni']; ?></td>
                                <td class="text-center">
                                    <span class="badge badge-danger badge-pill">
                                        <?php echo $cliente['cuotas_vencidas']; ?>
                                    </span>
                                </td>
                                <td class="text-right">
                                    <strong class="text-danger">
                                        $<?php echo number_format($cliente['monto_total_vencido'], 2); ?>
                                    </strong>
                                </td>
                                <td class="text-center">
                                    <a href="../clientes/historial_cliente.php?id=<?php echo $cliente['id_cliente']; ?>" 
                                       class="btn btn-info btn-sm">
                                        <i class="fas fa-history"></i> Historial
                                    </a>
                                    <a href="../cuotas/ver_cuotas_cliente.php?id_cliente=<?php echo $cliente['id_cliente']; ?>" 
                                       class="btn btn-warning btn-sm">
                                        <i class="fas fa-calendar-alt"></i> Cuotas
                                    </a>
                                </td>
                            </tr>
                            <?php 
                            $posicion++;
                            endwhile; 
                            ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    <h6 class="font-weight-bold">Recomendaciones:</h6>
                    <ul class="pl-3">
                        <li>Contactar a estos clientes de manera prioritaria</li>
                        <li>Ofrecer planes de refinanciación si es necesario</li>
                        <li>Evaluar el estado crediticio para futuros créditos</li>
                        <li>Documentar todas las comunicaciones realizadas</li>
                    </ul>
                </div>
                <?php else: ?>
                <div class="alert alert-success text-center">
                    <i class="fas fa-check-circle fa-3x mb-3"></i>
                    <h5>¡Excelente!</h5>
                    <p class="mb-0">No hay clientes con cuotas vencidas en este momento.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
</div>

<!-- Acciones Rápidas -->
<div class="row mt-4">
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4 border-left-warning">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-warning">
                    <i class="fas fa-bolt"></i> Acciones Rápidas
                </h6>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <a href="../moras/aplicar_mora.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-percentage text-danger"></i>
                        <strong>Aplicar Moras Automáticas</strong>
                        <br><small class="text-muted">Aplica recargos a cuotas vencidas</small>
                    </a>
                    <a href="../creditos/actualizar_estado_credito.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-sync text-primary"></i>
                        <strong>Actualizar Estados de Créditos</strong>
                        <br><small class="text-muted">Actualiza el estado de todos los créditos</small>
                    </a>
                    <a href="../cuotas/pagar_cuota.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-dollar-sign text-success"></i>
                        <strong>Registrar Pago</strong>
                        <br><small class="text-muted">Registra un pago de cuota</small>
                    </a>
                    <a href="exportar_alertas.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-file-excel text-info"></i>
                        <strong>Exportar Reporte</strong>
                        <br><small class="text-muted">Descarga el reporte de alertas en Excel</small>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4 border-left-info">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-info">
                    <i class="fas fa-info-circle"></i> Información y Consejos
                </h6>
            </div>
            <div class="card-body">
                <h6 class="font-weight-bold mb-3">¿Cómo usar las alertas?</h6>
                
                <div class="mb-3">
                    <span class="badge badge-danger">ALTA PRIORIDAD</span>
                    <p class="small mb-0 mt-1">Cuotas vencidas hace más de 30 días</p>
                </div>
                
                <div class="mb-3">
                    <span class="badge badge-warning">MEDIA PRIORIDAD</span>
                    <p class="small mb-0 mt-1">Cuotas vencidas hace 15-30 días</p>
                </div>
                
                <div class="mb-3">
                    <span class="badge badge-info">BAJA PRIORIDAD</span>
                    <p class="small mb-0 mt-1">Cuotas que vencen en más de 5 días</p>
                </div>
                
                <hr>
                
                <h6 class="font-weight-bold mb-2">Recomendaciones:</h6>
                <ul class="small pl-3 mb-0">
                    <li>Revise las alertas diariamente</li>
                    <li>Contacte a los clientes antes del vencimiento</li>
                    <li>Documente todas las comunicaciones</li>
                    <li>Use recordatorios automáticos</li>
                    <li>Ofrezca facilidades de pago cuando sea necesario</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Resumen Ejecutivo para Imprimir -->
<div class="d-none d-print-block">
    <div class="text-center mb-4">
        <h2>Reporte de Alertas de Vencimiento</h2>
        <p>Generado el: <?php echo date('d/m/Y H:i'); ?></p>
        <p>Período: Próximos <?php echo $limite_dias; ?> días</p>
    </div>
    
    <div class="row">
        <div class="col-12">
            <h4>Resumen Ejecutivo</h4>
            <table class="table table-bordered">
                <tr>
                    <th>Concepto</th>
                    <th>Cantidad</th>
                    <th>Monto</th>
                </tr>
                <tr>
                    <td>Cuotas Vencidas</td>
                    <td><?php echo $total_cuotas_vencidas; ?></td>
                    <td>$<?php echo number_format($monto_cuotas_vencidas, 2); ?></td>
                </tr>
                <tr>
                    <td>Cuotas por Vencer</td>
                    <td><?php echo $total_cuotas_vencer; ?></td>
                    <td>$<?php echo number_format($monto_cuotas_vencer, 2); ?></td>
                </tr>
                <tr class="font-weight-bold">
                    <td>TOTAL</td>
                    <td><?php echo $total_cuotas_vencidas + $total_cuotas_vencer; ?></td>
                    <td>$<?php echo number_format($monto_cuotas_vencidas + $monto_cuotas_vencer, 2); ?></td>
                </tr>
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
        // Inicializar DataTables para cada tabla
        $("#tablaVencidas, #tablaVencer, #tablaCreditosVencer").DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
            },
            "order": [[ 5, "asc" ]], // Ordenar por días (columna 5)
            "pageLength": 25
        });
        
        // Auto-refresh cada 5 minutos (300000 ms)
        setTimeout(function() {
            location.reload();
        }, 300000);
    });
</script>
';

include '../includes/footer.php';
?>
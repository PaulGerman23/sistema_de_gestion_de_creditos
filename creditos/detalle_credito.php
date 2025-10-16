<?php
include '../conexion.php';

// Variables para el template
$base_url = '../';
$page_title = 'Detalle del Crédito';
$active_page = 'creditos';

$id_credito = $_GET['id'] ?? 0;

if (!$id_credito) {
    header("Location: ver_creditos.php");
    exit;
}

// Obtener datos del crédito
$stmt = $conn->prepare("SELECT cr.*, c.nombre, c.apellido, c.dni, c.telefono, c.email, c.direccion, c.ciudad 
                        FROM creditos cr
                        JOIN clientes c ON cr.id_cliente = c.id_cliente
                        WHERE cr.id_credito = ?");
$stmt->bind_param("i", $id_credito);
$stmt->execute();
$credito = $stmt->get_result()->fetch_assoc();

if (!$credito) {
    header("Location: ver_creditos.php");
    exit;
}

// Obtener cuotas del crédito
$cuotas_stmt = $conn->prepare("SELECT * FROM cuotas WHERE id_credito = ? ORDER BY numero_cuota");
$cuotas_stmt->bind_param("i", $id_credito);
$cuotas_stmt->execute();
$cuotas = $cuotas_stmt->get_result();

// Calcular estadísticas
$total_pagado = $conn->query("SELECT SUM(cu.monto_cuota) as total FROM cuotas cu WHERE cu.id_credito = $id_credito AND cu.estado = 'pagada'")->fetch_assoc()['total'] ?? 0;
$total_pendiente = $credito['monto_total'] - $total_pagado;
$cuotas_pagadas = $conn->query("SELECT COUNT(*) as total FROM cuotas WHERE id_credito = $id_credito AND estado = 'pagada'")->fetch_assoc()['total'];
$cuotas_pendientes = $conn->query("SELECT COUNT(*) as total FROM cuotas WHERE id_credito = $id_credito AND estado = 'pendiente'")->fetch_assoc()['total'];
$cuotas_vencidas = $conn->query("SELECT COUNT(*) as total FROM cuotas WHERE id_credito = $id_credito AND estado = 'vencida'")->fetch_assoc()['total'];

// Determinar badge del estado
$badge_class = 'secondary';
if ($credito['estado'] == 'activo') $badge_class = 'success';
elseif ($credito['estado'] == 'moroso') $badge_class = 'danger';
elseif ($credito['estado'] == 'pagado') $badge_class = 'info';

include '../includes/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Detalle del Crédito #<?php echo $id_credito; ?></h1>
    <div>
        <a href="ver_creditos.php" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm"></i> Volver
        </a>
        <a href="../cuotas/generar_plan_pago.php?id_credito=<?php echo $id_credito; ?>" class="btn btn-sm btn-info shadow-sm">
            <i class="fas fa-calendar-alt fa-sm"></i> Plan de Pago
        </a>
        <a href="#" onclick="window.print()" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-print fa-sm"></i> Imprimir
        </a>
    </div>
</div>

<!-- Información del Cliente -->
<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Información del Cliente</h6>
                <span class="badge badge-<?php echo $badge_class; ?> badge-pill">
                    <?php echo strtoupper($credito['estado']); ?>
                </span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Nombre Completo:</strong> <?php echo $credito['nombre'] . ' ' . $credito['apellido']; ?></p>
                        <p class="mb-2"><strong>DNI:</strong> <?php echo $credito['dni']; ?></p>
                        <p class="mb-2"><strong>Teléfono:</strong> <?php echo $credito['telefono'] ?: 'No registrado'; ?></p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Email:</strong> <?php echo $credito['email'] ?: 'No registrado'; ?></p>
                        <p class="mb-2"><strong>Dirección:</strong> <?php echo $credito['direccion'] ?: 'No registrada'; ?></p>
                        <p class="mb-2"><strong>Ciudad:</strong> <?php echo $credito['ciudad'] ?: 'No registrada'; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Estadísticas del Crédito -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Monto Total</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($credito['monto_total'], 2); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Pagado</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($total_pagado, 2); ?></div>
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
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Saldo Pendiente</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($total_pendiente, 2); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Cuota Mensual</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($credito['cuota_mensual'], 2); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detalles del Crédito y Progreso -->
<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Detalles del Crédito</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Descripción:</strong></td>
                            <td><?php echo $credito['descripcion']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Fecha de Inicio:</strong></td>
                            <td><?php echo date('d/m/Y', strtotime($credito['fecha_inicio'])); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Fecha de Vencimiento:</strong></td>
                            <td><?php echo date('d/m/Y', strtotime($credito['fecha_vencimiento'])); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Cantidad de Cuotas:</strong></td>
                            <td><?php echo $credito['cantidad_cuotas']; ?> cuotas</td>
                        </tr>
                        <tr>
                            <td><strong>Interés Anual:</strong></td>
                            <td><?php echo $credito['interes_anual']; ?>%</td>
                        </tr>
                        <tr>
                            <td><strong>Cuotas Pagadas:</strong></td>
                            <td><span class="badge badge-success"><?php echo $cuotas_pagadas; ?></span></td>
                        </tr>
                        <tr>
                            <td><strong>Cuotas Pendientes:</strong></td>
                            <td><span class="badge badge-warning"><?php echo $cuotas_pendientes; ?></span></td>
                        </tr>
                        <?php if ($cuotas_vencidas > 0): ?>
                        <tr>
                            <td><strong>Cuotas Vencidas:</strong></td>
                            <td><span class="badge badge-danger"><?php echo $cuotas_vencidas; ?></span></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Progreso del Pago</h6>
            </div>
            <div class="card-body">
                <?php 
                $progreso_porcentaje = ($credito['monto_total'] > 0) ? ($total_pagado / $credito['monto_total']) * 100 : 0;
                $progreso_cuotas = ($credito['cantidad_cuotas'] > 0) ? ($cuotas_pagadas / $credito['cantidad_cuotas']) * 100 : 0;
                ?>
                
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="small font-weight-bold">Progreso por Monto</span>
                        <span class="small font-weight-bold"><?php echo round($progreso_porcentaje, 1); ?>%</span>
                    </div>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: <?php echo $progreso_porcentaje; ?>%;" 
                             aria-valuenow="<?php echo $progreso_porcentaje; ?>" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            $<?php echo number_format($total_pagado, 2); ?>
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="small font-weight-bold">Progreso por Cuotas</span>
                        <span class="small font-weight-bold"><?php echo round($progreso_cuotas, 1); ?>%</span>
                    </div>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-info" role="progressbar" 
                             style="width: <?php echo $progreso_cuotas; ?>%;" 
                             aria-valuenow="<?php echo $progreso_cuotas; ?>" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            <?php echo $cuotas_pagadas; ?> / <?php echo $credito['cantidad_cuotas']; ?>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-right">
                            <h5 class="text-success mb-0"><?php echo $cuotas_pagadas; ?></h5>
                            <small class="text-muted">Pagadas</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h5 class="text-warning mb-0"><?php echo $cuotas_pendientes + $cuotas_vencidas; ?></h5>
                        <small class="text-muted">Por Pagar</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Cuotas -->
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Detalle de Cuotas</h6>
                <a href="../cuotas/pagar_cuota.php?id_credito=<?php echo $id_credito; ?>" class="btn btn-sm btn-success">
                    <i class="fas fa-plus"></i> Registrar Pago
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>N° Cuota</th>
                                <th>Monto</th>
                                <th>Fecha Vencimiento</th>
                                <th>Fecha Pago</th>
                                <th>Estado</th>
                                <th>Días</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $cuotas->data_seek(0); // Reiniciar el puntero
                            while ($cuota = $cuotas->fetch_assoc()): 
                                $badge_cuota = 'secondary';
                                if ($cuota['estado'] == 'pagada') $badge_cuota = 'success';
                                elseif ($cuota['estado'] == 'vencida') $badge_cuota = 'danger';
                                elseif ($cuota['estado'] == 'pendiente') $badge_cuota = 'warning';
                                
                                // Calcular días
                                $hoy = new DateTime();
                                $fecha_venc = new DateTime($cuota['fecha_vencimiento']);
                                $diferencia = $hoy->diff($fecha_venc);
                                $dias = $diferencia->days;
                                $dias_texto = '';
                                
                                if ($cuota['estado'] == 'pagada') {
                                    $dias_texto = '<span class="badge badge-success">Pagada</span>';
                                } elseif ($fecha_venc < $hoy) {
                                    $dias_texto = '<span class="badge badge-danger">Vencida hace ' . $dias . ' días</span>';
                                } else {
                                    $dias_texto = '<span class="badge badge-info">Vence en ' . $dias . ' días</span>';
                                }
                            ?>
                            <tr>
                                <td class="text-center"><strong><?php echo $cuota['numero_cuota']; ?></strong></td>
                                <td>$<?php echo number_format($cuota['monto_cuota'], 2); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($cuota['fecha_vencimiento'])); ?></td>
                                <td><?php echo $cuota['fecha_pago'] ? date('d/m/Y H:i', strtotime($cuota['fecha_pago'])) : '-'; ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $badge_cuota; ?>">
                                        <?php echo ucfirst($cuota['estado']); ?>
                                    </span>
                                </td>
                                <td><?php echo $dias_texto; ?></td>
                                <td class="text-center">
                                    <?php if ($cuota['estado'] == 'pendiente' || $cuota['estado'] == 'vencida'): ?>
                                    <a href="../cuotas/pagar_cuota.php?id_cuota=<?php echo $cuota['id_cuota']; ?>" 
                                       class="btn btn-success btn-sm">
                                        <i class="fas fa-dollar-sign"></i> Pagar
                                    </a>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
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

<?php
include '../includes/footer.php';
?>
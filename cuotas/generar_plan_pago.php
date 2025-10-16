<?php
include '../conexion.php';

// Variables para el template
$base_url = '../';
$page_title = 'Plan de Pago';
$active_page = 'cuotas';

$id_credito = $_GET['id_credito'] ?? 0;

if (!$id_credito) {
    header("Location: ../creditos/ver_creditos.php");
    exit;
}

// Obtener datos del crédito y cliente
$stmt = $conn->prepare("SELECT cr.*, c.nombre, c.apellido, c.dni, c.telefono 
                        FROM creditos cr
                        JOIN clientes c ON cr.id_cliente = c.id_cliente
                        WHERE cr.id_credito = ?");
$stmt->bind_param("i", $id_credito);
$stmt->execute();
$credito = $stmt->get_result()->fetch_assoc();

if (!$credito) {
    header("Location: ../creditos/ver_creditos.php");
    exit;
}

// Obtener cuotas
$sql = "SELECT * FROM cuotas WHERE id_credito = ? ORDER BY numero_cuota";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_credito);
$stmt->execute();
$cuotas = $stmt->get_result();

// Calcular totales
$total_pagado = 0;
$total_pendiente = 0;
$interes_total = ($credito['cantidad_cuotas'] * $credito['cuota_mensual']) - $credito['monto_total'];

include '../includes/header.php';
?>

<!-- CSS para impresión -->
<style>
@media print {
    .no-print {
        display: none !important;
    }
    .card {
        border: 1px solid #dee2e6 !important;
        box-shadow: none !important;
    }
}
</style>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4 no-print">
    <h1 class="h3 mb-0 text-gray-800">Plan de Pago - Crédito #<?php echo $id_credito; ?></h1>
    <div>
        <a href="../creditos/ver_creditos.php" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm"></i> Volver
        </a>
        <button onclick="window.print()" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-print fa-sm"></i> Imprimir
        </button>
        <a href="exportar_plan_pdf.php?id_credito=<?php echo $id_credito; ?>" class="btn btn-sm btn-danger shadow-sm">
            <i class="fas fa-file-pdf fa-sm"></i> Exportar PDF
        </a>
    </div>
</div>

<!-- Encabezado del Plan de Pago -->
<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="font-weight-bold text-primary mb-3">Información del Cliente</h5>
                        <p class="mb-1"><strong>Nombre:</strong> <?php echo $credito['nombre'] . ' ' . $credito['apellido']; ?></p>
                        <p class="mb-1"><strong>DNI:</strong> <?php echo $credito['dni']; ?></p>
                        <p class="mb-1"><strong>Teléfono:</strong> <?php echo $credito['telefono'] ?: 'No registrado'; ?></p>
                    </div>
                    <div class="col-md-6">
                        <h5 class="font-weight-bold text-primary mb-3">Información del Crédito</h5>
                        <p class="mb-1"><strong>Descripción:</strong> <?php echo $credito['descripcion']; ?></p>
                        <p class="mb-1"><strong>Fecha de inicio:</strong> <?php echo date('d/m/Y', strtotime($credito['fecha_inicio'])); ?></p>
                        <p class="mb-1"><strong>Fecha de vencimiento:</strong> <?php echo date('d/m/Y', strtotime($credito['fecha_vencimiento'])); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Resumen Financiero -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Capital</div>
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
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Intereses</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($interes_total, 2); ?></div>
                        <small class="text-muted"><?php echo $credito['interes_anual']; ?>% anual</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-percent fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Cuota Mensual</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($credito['cuota_mensual'], 2); ?></div>
                        <small class="text-muted"><?php echo $credito['cantidad_cuotas']; ?> cuotas</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total a Pagar</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            $<?php echo number_format($credito['cantidad_cuotas'] * $credito['cuota_mensual'], 2); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Cuotas Detallada -->
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Detalle de Cuotas</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-center">N° Cuota</th>
                                <th class="text-center">Fecha Vencimiento</th>
                                <th class="text-right">Monto Cuota</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Fecha Pago</th>
                                <th class="text-center no-print">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $cuotas->data_seek(0);
                            while ($cuota = $cuotas->fetch_assoc()): 
                                $badge_cuota = 'secondary';
                                $icon = 'fa-clock';
                                if ($cuota['estado'] == 'pagada') {
                                    $badge_cuota = 'success';
                                    $icon = 'fa-check-circle';
                                    $total_pagado += $cuota['monto_cuota'];
                                } elseif ($cuota['estado'] == 'vencida') {
                                    $badge_cuota = 'danger';
                                    $icon = 'fa-times-circle';
                                    $total_pendiente += $cuota['monto_cuota'];
                                } elseif ($cuota['estado'] == 'pendiente') {
                                    $badge_cuota = 'warning';
                                    $icon = 'fa-clock';
                                    $total_pendiente += $cuota['monto_cuota'];
                                }
                                
                                // Calcular días
                                $hoy = new DateTime();
                                $fecha_venc = new DateTime($cuota['fecha_vencimiento']);
                                $diferencia = $hoy->diff($fecha_venc);
                                $dias = $diferencia->days;
                                
                                $clase_fila = '';
                                if ($cuota['estado'] == 'pagada') {
                                    $clase_fila = 'table-success';
                                } elseif ($cuota['estado'] == 'vencida') {
                                    $clase_fila = 'table-danger';
                                } elseif ($fecha_venc < $hoy && $cuota['estado'] == 'pendiente') {
                                    $clase_fila = 'table-warning';
                                }
                            ?>
                            <tr class="<?php echo $clase_fila; ?>">
                                <td class="text-center font-weight-bold"><?php echo $cuota['numero_cuota']; ?></td>
                                <td class="text-center"><?php echo date('d/m/Y', strtotime($cuota['fecha_vencimiento'])); ?></td>
                                <td class="text-right font-weight-bold">$<?php echo number_format($cuota['monto_cuota'], 2); ?></td>
                                <td class="text-center">
                                    <span class="badge badge-<?php echo $badge_cuota; ?>">
                                        <i class="fas <?php echo $icon; ?>"></i>
                                        <?php echo ucfirst($cuota['estado']); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php echo $cuota['fecha_pago'] ? date('d/m/Y', strtotime($cuota['fecha_pago'])) : '-'; ?>
                                </td>
                                <td class="text-center no-print">
                                    <?php if ($cuota['estado'] == 'pendiente' || $cuota['estado'] == 'vencida'): ?>
                                    <a href="pagar_cuota.php?id_cuota=<?php echo $cuota['id_cuota']; ?>" 
                                       class="btn btn-success btn-sm">
                                        <i class="fas fa-dollar-sign"></i> Pagar
                                    </a>
                                    <?php else: ?>
                                    <span class="text-success">
                                        <i class="fas fa-check"></i> Pagada
                                    </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                        <tfoot class="thead-light">
                            <tr>
                                <td colspan="2" class="text-right font-weight-bold">TOTALES:</td>
                                <td class="text-right font-weight-bold">
                                    $<?php echo number_format($credito['cantidad_cuotas'] * $credito['cuota_mensual'], 2); ?>
                                </td>
                                <td colspan="3"></td>
                            </tr>
                            <tr class="table-success">
                                <td colspan="2" class="text-right font-weight-bold">TOTAL PAGADO:</td>
                                <td class="text-right font-weight-bold text-success">
                                    $<?php echo number_format($total_pagado, 2); ?>
                                </td>
                                <td colspan="3"></td>
                            </tr>
                            <tr class="table-warning">
                                <td colspan="2" class="text-right font-weight-bold">SALDO PENDIENTE:</td>
                                <td class="text-right font-weight-bold text-warning">
                                    $<?php echo number_format($total_pendiente, 2); ?>
                                </td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <!-- Leyenda -->
                <div class="mt-3">
                    <h6 class="font-weight-bold">Leyenda:</h6>
                    <div class="row">
                        <div class="col-md-3">
                            <span class="badge badge-success"><i class="fas fa-check-circle"></i> Pagada</span>
                            - Cuota abonada
                        </div>
                        <div class="col-md-3">
                            <span class="badge badge-warning"><i class="fas fa-clock"></i> Pendiente</span>
                            - Por abonar
                        </div>
                        <div class="col-md-3">
                            <span class="badge badge-danger"><i class="fas fa-times-circle"></i> Vencida</span>
                            - Plazo vencido
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notas adicionales -->
<div class="row no-print">
    <div class="col-lg-12">
        <div class="card shadow mb-4 border-left-info">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-info">
                    <i class="fas fa-info-circle"></i> Información Importante
                </h6>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>Las cuotas deben pagarse antes de la fecha de vencimiento para evitar recargos.</li>
                    <li>El incumplimiento en el pago puede generar intereses moratorios adicionales.</li>
                    <li>Para realizar un pago, haga clic en el botón "Pagar" correspondiente a cada cuota.</li>
                    <li>Puede imprimir este plan de pago para tener un registro físico.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
include '../includes/footer.php';
?>
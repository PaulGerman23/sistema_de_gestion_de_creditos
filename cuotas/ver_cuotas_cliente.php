<?php
include '../conexion.php';

// Variables para el template
$base_url = '../';
$page_title = 'Ver Cuotas';
$active_page = 'cuotas';
$active_subpage = 'ver_cuotas';

// CSS adicional
$extra_css = '<link href="../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">';

$id_cliente = $_GET['id_cliente'] ?? 0;
$filtro_estado = $_GET['estado'] ?? 'todos';

// Obtener lista de clientes con créditos
$clientes_query = "SELECT DISTINCT c.id_cliente, c.nombre, c.apellido, c.dni 
                   FROM clientes c
                   INNER JOIN creditos cr ON c.id_cliente = cr.id_cliente
                   ORDER BY c.nombre, c.apellido";
$clientes = $conn->query($clientes_query);

// Si hay un cliente seleccionado, obtener sus cuotas
$cuotas_result = null;
$cliente_info = null;
$estadisticas = null;

if ($id_cliente) {
    // Obtener información del cliente
    $stmt = $conn->prepare("SELECT nombre, apellido, dni, telefono FROM clientes WHERE id_cliente = ?");
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $cliente_info = $stmt->get_result()->fetch_assoc();
    
    // Consulta de cuotas con filtro
    $sql = "SELECT cu.*, cr.id_credito, cr.descripcion as descripcion_credito, 
                   cr.monto_total, cr.estado as estado_credito
            FROM cuotas cu
            JOIN creditos cr ON cu.id_credito = cr.id_credito
            WHERE cr.id_cliente = ?";
    
    if ($filtro_estado != 'todos') {
        $sql .= " AND cu.estado = '$filtro_estado'";
    }
    
    $sql .= " ORDER BY cu.fecha_vencimiento ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $cuotas_result = $stmt->get_result();
    
    // Obtener estadísticas del cliente
    $estadisticas = [
        'total' => $conn->query("SELECT COUNT(*) as total FROM cuotas cu 
                                 JOIN creditos cr ON cu.id_credito = cr.id_credito 
                                 WHERE cr.id_cliente = $id_cliente")->fetch_assoc()['total'],
        'pagadas' => $conn->query("SELECT COUNT(*) as total FROM cuotas cu 
                                   JOIN creditos cr ON cu.id_credito = cr.id_credito 
                                   WHERE cr.id_cliente = $id_cliente AND cu.estado = 'pagada'")->fetch_assoc()['total'],
        'pendientes' => $conn->query("SELECT COUNT(*) as total FROM cuotas cu 
                                      JOIN creditos cr ON cu.id_credito = cr.id_credito 
                                      WHERE cr.id_cliente = $id_cliente AND cu.estado = 'pendiente'")->fetch_assoc()['total'],
        'vencidas' => $conn->query("SELECT COUNT(*) as total FROM cuotas cu 
                                    JOIN creditos cr ON cu.id_credito = cr.id_credito 
                                    WHERE cr.id_cliente = $id_cliente AND cu.estado = 'vencida'")->fetch_assoc()['total'],
        'monto_total' => $conn->query("SELECT SUM(cu.monto_cuota) as total FROM cuotas cu 
                                       JOIN creditos cr ON cu.id_credito = cr.id_credito 
                                       WHERE cr.id_cliente = $id_cliente")->fetch_assoc()['total'] ?? 0,
        'monto_pagado' => $conn->query("SELECT SUM(cu.monto_cuota) as total FROM cuotas cu 
                                        JOIN creditos cr ON cu.id_credito = cr.id_credito 
                                        WHERE cr.id_cliente = $id_cliente AND cu.estado = 'pagada'")->fetch_assoc()['total'] ?? 0,
        'monto_pendiente' => $conn->query("SELECT SUM(cu.monto_cuota) as total FROM cuotas cu 
                                           JOIN creditos cr ON cu.id_credito = cr.id_credito 
                                           WHERE cr.id_cliente = $id_cliente AND cu.estado IN ('pendiente', 'vencida')")->fetch_assoc()['total'] ?? 0
    ];
}

include '../includes/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Visualización de Cuotas por Cliente</h1>
    <?php if ($id_cliente): ?>
    <a href="pagar_cuota.php?id_cliente=<?php echo $id_cliente; ?>" class="btn btn-sm btn-success shadow-sm">
        <i class="fas fa-dollar-sign fa-sm text-white-50"></i> Registrar Pago
    </a>
    <?php endif; ?>
</div>

<!-- Selector de Cliente -->
<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Seleccionar Cliente</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="" class="form-inline">
                    <div class="form-group mr-3">
                        <label for="id_cliente" class="mr-2">Cliente:</label>
                        <select name="id_cliente" id="id_cliente" class="form-control" style="min-width: 300px;" onchange="this.form.submit()">
                            <option value="">-- Seleccione un cliente --</option>
                            <?php 
                            $clientes->data_seek(0);
                            while ($cliente = $clientes->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $cliente['id_cliente']; ?>" 
                                        <?php echo ($id_cliente == $cliente['id_cliente']) ? 'selected' : ''; ?>>
                                    <?php echo $cliente['nombre'] . ' ' . $cliente['apellido'] . ' - DNI: ' . $cliente['dni']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <?php if ($id_cliente): ?>
                    <div class="form-group mr-3">
                        <label for="estado" class="mr-2">Estado:</label>
                        <select name="estado" id="estado" class="form-control" onchange="this.form.submit()">
                            <option value="todos" <?php echo ($filtro_estado == 'todos') ? 'selected' : ''; ?>>Todos</option>
                            <option value="pendiente" <?php echo ($filtro_estado == 'pendiente') ? 'selected' : ''; ?>>Pendientes</option>
                            <option value="pagada" <?php echo ($filtro_estado == 'pagada') ? 'selected' : ''; ?>>Pagadas</option>
                            <option value="vencida" <?php echo ($filtro_estado == 'vencida') ? 'selected' : ''; ?>>Vencidas</option>
                        </select>
                    </div>
                    <?php endif; ?>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                    
                    <?php if ($id_cliente): ?>
                    <a href="ver_cuotas_cliente.php" class="btn btn-secondary ml-2">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if ($id_cliente && $cliente_info): ?>

<!-- Información del Cliente -->
<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card shadow mb-4 border-left-primary">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Información del Cliente</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <p class="mb-1"><strong>Nombre:</strong></p>
                        <h5><?php echo $cliente_info['nombre'] . ' ' . $cliente_info['apellido']; ?></h5>
                    </div>
                    <div class="col-md-3">
                        <p class="mb-1"><strong>DNI:</strong></p>
                        <h5><?php echo $cliente_info['dni']; ?></h5>
                    </div>
                    <div class="col-md-3">
                        <p class="mb-1"><strong>Teléfono:</strong></p>
                        <h5><?php echo $cliente_info['telefono'] ?: 'No registrado'; ?></h5>
                    </div>
                    <div class="col-md-3 text-right">
                        <a href="../clientes/historial_cliente.php?id=<?php echo $id_cliente; ?>" class="btn btn-info">
                            <i class="fas fa-history"></i> Ver Historial Completo
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Estadísticas de Cuotas -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Cuotas</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $estadisticas['total']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-list fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Pagadas</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $estadisticas['pagadas']; ?></div>
                        <small class="text-muted">$<?php echo number_format($estadisticas['monto_pagado'], 2); ?></small>
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
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pendientes</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $estadisticas['pendientes']; ?></div>
                        <small class="text-muted">$<?php echo number_format($estadisticas['monto_pendiente'], 2); ?></small>
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
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Vencidas</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $estadisticas['vencidas']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Progreso de Pago -->
<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Progreso de Pago</h6>
            </div>
            <div class="card-body">
                <?php 
                $progreso = ($estadisticas['monto_total'] > 0) ? ($estadisticas['monto_pagado'] / $estadisticas['monto_total']) * 100 : 0;
                ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Pagado: $<?php echo number_format($estadisticas['monto_pagado'], 2); ?></span>
                        <span>Pendiente: $<?php echo number_format($estadisticas['monto_pendiente'], 2); ?></span>
                    </div>
                    <div class="progress" style="height: 30px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: <?php echo $progreso; ?>%;" 
                             aria-valuenow="<?php echo $progreso; ?>" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            <?php echo round($progreso, 1); ?>% Completado
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    <h4 class="mb-0">Total: $<?php echo number_format($estadisticas['monto_total'], 2); ?></h4>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Cuotas -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Detalle de Cuotas</h6>
        <div class="dropdown no-arrow">
            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown">
                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                <div class="dropdown-header">Opciones:</div>
                <a class="dropdown-item" href="#" onclick="window.print()">
                    <i class="fas fa-print fa-sm fa-fw mr-2 text-gray-400"></i>
                    Imprimir
                </a>
                <a class="dropdown-item" href="exportar_cuotas.php?id_cliente=<?php echo $id_cliente; ?>">
                    <i class="fas fa-download fa-sm fa-fw mr-2 text-gray-400"></i>
                    Exportar Excel
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Crédito</th>
                        <th>N° Cuota</th>
                        <th>Monto</th>
                        <th>Fecha Venc.</th>
                        <th>Estado</th>
                        <th>Días</th>
                        <th>Fecha Pago</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($cuota = $cuotas_result->fetch_assoc()): 
                        $badge_cuota = 'secondary';
                        $icon = 'fa-clock';
                        if ($cuota['estado'] == 'pagada') {
                            $badge_cuota = 'success';
                            $icon = 'fa-check-circle';
                        } elseif ($cuota['estado'] == 'vencida') {
                            $badge_cuota = 'danger';
                            $icon = 'fa-times-circle';
                        } elseif ($cuota['estado'] == 'pendiente') {
                            $badge_cuota = 'warning';
                            $icon = 'fa-clock';
                        }
                        
                        // Calcular días
                        $hoy = new DateTime();
                        $fecha_venc = new DateTime($cuota['fecha_vencimiento']);
                        $diferencia = $hoy->diff($fecha_venc);
                        $dias = $diferencia->days;
                        $dias_texto = '';
                        $badge_dias = 'info';
                        
                        if ($cuota['estado'] == 'pagada') {
                            $dias_texto = 'Pagada';
                            $badge_dias = 'success';
                        } elseif ($fecha_venc < $hoy) {
                            $dias_texto = 'Hace ' . $dias . 'd';
                            $badge_dias = 'danger';
                        } else {
                            $dias_texto = 'En ' . $dias . 'd';
                            $badge_dias = 'info';
                        }
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo $cuota['descripcion_credito']; ?></strong>
                            <br><small class="text-muted">ID: <?php echo $cuota['id_credito']; ?></small>
                        </td>
                        <td class="text-center"><strong><?php echo $cuota['numero_cuota']; ?></strong></td>
                        <td><strong>$<?php echo number_format($cuota['monto_cuota'], 2); ?></strong></td>
                        <td><?php echo date('d/m/Y', strtotime($cuota['fecha_vencimiento'])); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $badge_cuota; ?>">
                                <i class="fas <?php echo $icon; ?>"></i>
                                <?php echo ucfirst($cuota['estado']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $badge_dias; ?>">
                                <?php echo $dias_texto; ?>
                            </span>
                        </td>
                        <td><?php echo $cuota['fecha_pago'] ? date('d/m/Y', strtotime($cuota['fecha_pago'])) : '-'; ?></td>
                        <td class="text-center">
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
    </div>
</div>

<?php else: ?>

<!-- Mensaje cuando no hay cliente seleccionado -->
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-body text-center py-5">
                <i class="fas fa-search fa-3x text-gray-300 mb-3"></i>
                <h4 class="text-gray-600">Seleccione un cliente para ver sus cuotas</h4>
                <p class="text-muted">Utilice el selector de arriba para elegir un cliente</p>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>

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
            "order": [[ 3, "asc" ]],
            "pageLength": 25
        });
    });
</script>
';

include '../includes/footer.php';
?>
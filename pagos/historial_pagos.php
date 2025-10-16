<?php
include '../conexion.php';

// Variables para el template
$base_url = '../';
$page_title = 'Historial de Pagos';
$active_page = 'pagos';

// CSS adicional
$extra_css = '<link href="../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">';

// Filtros
$fecha_desde = $_GET['fecha_desde'] ?? date('Y-m-01');
$fecha_hasta = $_GET['fecha_hasta'] ?? date('Y-m-d');
$metodo_filtro = $_GET['metodo'] ?? 'todos';
$cliente_filtro = $_GET['cliente'] ?? '';

// Consulta principal de pagos
$sql = "SELECT p.id_pago, p.monto_pagado, p.fecha_pago, p.metodo_pago, p.observaciones,
               cu.numero_cuota, cu.monto_cuota,
               cr.id_credito, cr.descripcion,
               cl.id_cliente, cl.nombre, cl.apellido, cl.dni
        FROM pagos p
        JOIN cuotas cu ON p.id_cuota = cu.id_cuota
        JOIN creditos cr ON cu.id_credito = cr.id_credito
        JOIN clientes cl ON cr.id_cliente = cl.id_cliente
        WHERE DATE(p.fecha_pago) BETWEEN ? AND ?";

$params = [$fecha_desde, $fecha_hasta];
$types = "ss";

if ($metodo_filtro != 'todos') {
    $sql .= " AND p.metodo_pago = ?";
    $params[] = $metodo_filtro;
    $types .= "s";
}

if ($cliente_filtro) {
    $sql .= " AND (cl.nombre LIKE ? OR cl.apellido LIKE ? OR cl.dni LIKE ?)";
    $busqueda = "%$cliente_filtro%";
    $params[] = $busqueda;
    $params[] = $busqueda;
    $params[] = $busqueda;
    $types .= "sss";
}

$sql .= " ORDER BY p.fecha_pago DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Calcular estadísticas del período
$stats_total = $conn->query("SELECT SUM(p.monto_pagado) as total FROM pagos p WHERE DATE(p.fecha_pago) BETWEEN '$fecha_desde' AND '$fecha_hasta'")->fetch_assoc()['total'] ?? 0;
$stats_cantidad = $conn->query("SELECT COUNT(*) as total FROM pagos p WHERE DATE(p.fecha_pago) BETWEEN '$fecha_desde' AND '$fecha_hasta'")->fetch_assoc()['total'];
$stats_efectivo = $conn->query("SELECT SUM(p.monto_pagado) as total FROM pagos p WHERE DATE(p.fecha_pago) BETWEEN '$fecha_desde' AND '$fecha_hasta' AND p.metodo_pago = 'efectivo'")->fetch_assoc()['total'] ?? 0;
$stats_transferencia = $conn->query("SELECT SUM(p.monto_pagado) as total FROM pagos p WHERE DATE(p.fecha_pago) BETWEEN '$fecha_desde' AND '$fecha_hasta' AND p.metodo_pago = 'transferencia'")->fetch_assoc()['total'] ?? 0;

include '../includes/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Historial de Pagos</h1>
    <div>
        <a href="../cuotas/pagar_cuota.php" class="btn btn-sm btn-success shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Registrar Pago
        </a>
        <button onclick="window.print()" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-print fa-sm"></i> Imprimir
        </button>
    </div>
</div>

<!-- Filtros -->
<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-filter"></i> Filtros de Búsqueda
                </h6>
            </div>
            <div class="card-body">
                <form method="GET" action="" class="form-inline flex-wrap">
                    <div class="form-group mr-3 mb-2">
                        <label for="fecha_desde" class="mr-2">Desde:</label>
                        <input type="date" class="form-control" id="fecha_desde" name="fecha_desde" 
                               value="<?php echo $fecha_desde; ?>">
                    </div>
                    
                    <div class="form-group mr-3 mb-2">
                        <label for="fecha_hasta" class="mr-2">Hasta:</label>
                        <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta" 
                               value="<?php echo $fecha_hasta; ?>">
                    </div>
                    
                    <div class="form-group mr-3 mb-2">
                        <label for="metodo" class="mr-2">Método:</label>
                        <select name="metodo" id="metodo" class="form-control">
                            <option value="todos" <?php echo ($metodo_filtro == 'todos') ? 'selected' : ''; ?>>Todos</option>
                            <option value="efectivo" <?php echo ($metodo_filtro == 'efectivo') ? 'selected' : ''; ?>>Efectivo</option>
                            <option value="transferencia" <?php echo ($metodo_filtro == 'transferencia') ? 'selected' : ''; ?>>Transferencia</option>
                            <option value="tarjeta" <?php echo ($metodo_filtro == 'tarjeta') ? 'selected' : ''; ?>>Tarjeta</option>
                            <option value="cheque" <?php echo ($metodo_filtro == 'cheque') ? 'selected' : ''; ?>>Cheque</option>
                        </select>
                    </div>
                    
                    <div class="form-group mr-3 mb-2">
                        <label for="cliente" class="mr-2">Cliente:</label>
                        <input type="text" class="form-control" id="cliente" name="cliente" 
                               placeholder="Nombre, apellido o DNI"
                               value="<?php echo htmlspecialchars($cliente_filtro); ?>">
                    </div>
                    
                    <button type="submit" class="btn btn-primary mr-2 mb-2">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                    
                    <a href="historial_pagos.php" class="btn btn-secondary mb-2">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Estadísticas del Período -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Recaudado
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            $<?php echo number_format($stats_total, 2); ?>
                        </div>
                        <small class="text-muted">Período seleccionado</small>
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
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Cantidad de Pagos
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $stats_cantidad; ?>
                        </div>
                        <small class="text-muted">Transacciones</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-receipt fa-2x text-gray-300"></i>
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
                            Efectivo
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            $<?php echo number_format($stats_efectivo, 2); ?>
                        </div>
                        <?php 
                        $porc_efectivo = $stats_total > 0 ? ($stats_efectivo / $stats_total) * 100 : 0;
                        ?>
                        <small class="text-muted"><?php echo round($porc_efectivo, 1); ?>% del total</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Transferencias
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            $<?php echo number_format($stats_transferencia, 2); ?>
                        </div>
                        <?php 
                        $porc_transf = $stats_total > 0 ? ($stats_transferencia / $stats_total) * 100 : 0;
                        ?>
                        <small class="text-muted"><?php echo round($porc_transf, 1); ?>% del total</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exchange-alt fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Pagos -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Detalle de Pagos</h6>
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
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Crédito</th>
                        <th>N° Cuota</th>
                        <th>Monto Pagado</th>
                        <th>Método</th>
                        <th>Observaciones</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): 
                        // Determinar icono según método de pago
                        $icono_metodo = 'fa-dollar-sign';
                        $color_metodo = 'success';
                        
                        switch($row['metodo_pago']) {
                            case 'efectivo':
                                $icono_metodo = 'fa-money-bill-wave';
                                $color_metodo = 'success';
                                break;
                            case 'transferencia':
                                $icono_metodo = 'fa-exchange-alt';
                                $color_metodo = 'primary';
                                break;
                            case 'tarjeta':
                                $icono_metodo = 'fa-credit-card';
                                $color_metodo = 'info';
                                break;
                            case 'cheque':
                                $icono_metodo = 'fa-money-check';
                                $color_metodo = 'warning';
                                break;
                        }
                    ?>
                    <tr>
                        <td><?php echo $row['id_pago']; ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($row['fecha_pago'])); ?></td>
                        <td>
                            <strong><?php echo $row['nombre'] . ' ' . $row['apellido']; ?></strong>
                            <br><small class="text-muted">DNI: <?php echo $row['dni']; ?></small>
                        </td>
                        <td>
                            <?php echo $row['descripcion']; ?>
                            <br><small class="text-muted">ID: <?php echo $row['id_credito']; ?></small>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-secondary"><?php echo $row['numero_cuota']; ?></span>
                        </td>
                        <td class="text-right">
                            <strong class="text-success">$<?php echo number_format($row['monto_pagado'], 2); ?></strong>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $color_metodo; ?>">
                                <i class="fas <?php echo $icono_metodo; ?>"></i>
                                <?php echo ucfirst($row['metodo_pago']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($row['observaciones']): ?>
                                <button type="button" class="btn btn-sm btn-info" 
                                        data-toggle="tooltip" 
                                        title="<?php echo htmlspecialchars($row['observaciones']); ?>">
                                    <i class="fas fa-comment"></i>
                                </button>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <a href="../creditos/detalle_credito.php?id=<?php echo $row['id_credito']; ?>" 
                               class="btn btn-info btn-sm btn-circle" 
                               title="Ver Crédito">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="comprobante_pago.php?id=<?php echo $row['id_pago']; ?>" 
                               class="btn btn-primary btn-sm btn-circle" 
                               title="Ver Comprobante"
                               target="_blank">
                                <i class="fas fa-receipt"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr class="table-info">
                        <td colspan="5" class="text-right"><strong>TOTAL DEL PERÍODO:</strong></td>
                        <td class="text-right"><strong class="text-success">$<?php echo number_format($stats_total, 2); ?></strong></td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Resumen por Método de Pago -->
<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-pie"></i> Distribución por Método de Pago
                </h6>
            </div>
            <div class="card-body">
                <?php
                $metodos = $conn->query("SELECT metodo_pago, SUM(monto_pagado) as total, COUNT(*) as cantidad 
                                        FROM pagos 
                                        WHERE DATE(fecha_pago) BETWEEN '$fecha_desde' AND '$fecha_hasta' 
                                        GROUP BY metodo_pago 
                                        ORDER BY total DESC");
                ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Método</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-right">Monto</th>
                                <th class="text-center">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($metodo = $metodos->fetch_assoc()): 
                                $porcentaje = $stats_total > 0 ? ($metodo['total'] / $stats_total) * 100 : 0;
                                
                                // Iconos y colores
                                $icono = 'fa-dollar-sign';
                                $color = 'secondary';
                                switch($metodo['metodo_pago']) {
                                    case 'efectivo': $icono = 'fa-money-bill-wave'; $color = 'success'; break;
                                    case 'transferencia': $icono = 'fa-exchange-alt'; $color = 'primary'; break;
                                    case 'tarjeta': $icono = 'fa-credit-card'; $color = 'info'; break;
                                    case 'cheque': $icono = 'fa-money-check'; $color = 'warning'; break;
                                }
                            ?>
                            <tr>
                                <td>
                                    <i class="fas <?php echo $icono; ?> text-<?php echo $color; ?>"></i>
                                    <?php echo ucfirst($metodo['metodo_pago']); ?>
                                </td>
                                <td class="text-center"><?php echo $metodo['cantidad']; ?></td>
                                <td class="text-right">$<?php echo number_format($metodo['total'], 2); ?></td>
                                <td class="text-center">
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-<?php echo $color; ?>" 
                                             role="progressbar" 
                                             style="width: <?php echo $porcentaje; ?>%;">
                                            <?php echo round($porcentaje, 1); ?>%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-users"></i> Top 5 Clientes que más Pagaron
                </h6>
            </div>
            <div class="card-body">
                <?php
                $top_clientes = $conn->query("SELECT cl.nombre, cl.apellido, cl.dni, 
                                             SUM(p.monto_pagado) as total, COUNT(p.id_pago) as pagos
                                             FROM pagos p
                                             JOIN cuotas cu ON p.id_cuota = cu.id_cuota
                                             JOIN creditos cr ON cu.id_credito = cr.id_credito
                                             JOIN clientes cl ON cr.id_cliente = cl.id_cliente
                                             WHERE DATE(p.fecha_pago) BETWEEN '$fecha_desde' AND '$fecha_hasta'
                                             GROUP BY cl.id_cliente
                                             ORDER BY total DESC
                                             LIMIT 5");
                ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th class="text-center">Pagos</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $posicion = 1;
                            while ($cliente = $top_clientes->fetch_assoc()): 
                                $medalla = '';
                                if ($posicion == 1) $medalla = '<i class="fas fa-trophy text-warning"></i>';
                                elseif ($posicion == 2) $medalla = '<i class="fas fa-medal text-secondary"></i>';
                                elseif ($posicion == 3) $medalla = '<i class="fas fa-award text-danger"></i>';
                            ?>
                            <tr>
                                <td>
                                    <?php echo $medalla; ?>
                                    <strong><?php echo $cliente['nombre'] . ' ' . $cliente['apellido']; ?></strong>
                                    <br><small class="text-muted">DNI: <?php echo $cliente['dni']; ?></small>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-info"><?php echo $cliente['pagos']; ?></span>
                                </td>
                                <td class="text-right">
                                    <strong class="text-success">$<?php echo number_format($cliente['total'], 2); ?></strong>
                                </td>
                            </tr>
                            <?php 
                            $posicion++;
                            endwhile; 
                            ?>
                        </tbody>
                    </table>
                </div>
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
        $("#dataTable").DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
            },
            "order": [[ 1, "desc" ]],
            "pageLength": 25
        });
        
        // Activar tooltips
        $("[data-toggle=\"tooltip\"]").tooltip();
    });
</script>
';


include '../includes/footer.php';
?>
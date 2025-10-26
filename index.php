<?php
// Configuración del layout
$base_url = './';
$page_title = 'Dashboard';
$active_page = 'dashboard';

include 'conexion.php';

// Obtener estadísticas
$total_clientes = $conn->query("SELECT COUNT(*) as total FROM clientes")->fetch_assoc()['total'];
$creditos_activos = $conn->query("SELECT COUNT(*) as total FROM creditos WHERE estado = 'activo'")->fetch_assoc()['total'];
$creditos_morosos = $conn->query("SELECT COUNT(*) as total FROM creditos WHERE estado = 'moroso'")->fetch_assoc()['total'];
$monto_total_creditos = $conn->query("SELECT SUM(monto_total) as total FROM creditos WHERE estado = 'activo'")->fetch_assoc()['total'] ?? 0;

// Incluir header
include 'includes/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
</div>

<!-- Content Row - Estadísticas -->
<div class="row">
    
    <!-- Total Clientes Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Clientes</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_clientes; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Créditos Activos Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Créditos Activos</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $creditos_activos; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-credit-card fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Créditos Morosos Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Créditos Morosos</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $creditos_morosos; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Monto Total Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Monto Total en Créditos</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($monto_total_creditos, 2); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content Row -->
<div class="row">
    
    <!-- Créditos Recientes -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Créditos Recientes</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Monto</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $creditos_recientes = $conn->query("SELECT c.nombre, c.apellido, cr.monto_total, cr.estado 
                                FROM creditos cr 
                                JOIN clientes c ON cr.id_cliente = c.id_cliente 
                                ORDER BY cr.fecha_inicio DESC 
                                LIMIT 5");
                            while ($row = $creditos_recientes->fetch_assoc()):
                                $badge_class = $row['estado'] == 'activo' ? 'success' : ($row['estado'] == 'moroso' ? 'danger' : 'secondary');
                            ?>
                            <tr>
                                <td><?php echo $row['nombre'] . ' ' . $row['apellido']; ?></td>
                                <td>$<?php echo number_format($row['monto_total'], 2); ?></td>
                                <td><span class="badge badge-<?php echo $badge_class; ?>"><?php echo ucfirst($row['estado']); ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Alertas de Vencimiento -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-warning">Alertas de Vencimiento</h6>
            </div>
            <div class="card-body">
                <?php
                $hoy = date('Y-m-d');
                $limite = date('Y-m-d', strtotime('+7 days'));
                $alertas = $conn->query("SELECT c.nombre, cr.descripcion, cr.fecha_vencimiento 
                    FROM creditos cr 
                    JOIN clientes c ON cr.id_cliente = c.id_cliente 
                    WHERE cr.fecha_vencimiento BETWEEN '$hoy' AND '$limite' 
                    AND cr.estado = 'activo'
                    LIMIT 5");
                
                if ($alertas->num_rows > 0):
                    while ($row = $alertas->fetch_assoc()):
                ?>
                <div class="alert alert-warning" role="alert">
                    <strong><?php echo $row['nombre']; ?></strong> - <?php echo $row['descripcion']; ?>
                    <br><small>Vence: <?php echo date('d/m/Y', strtotime($row['fecha_vencimiento'])); ?></small>
                </div>
                <?php 
                    endwhile;
                else:
                ?>
                <p class="text-muted">No hay créditos próximos a vencer.</p>
                <?php endif; ?>
                <a href="alertas/alertas_vencimientos.php" class="btn btn-warning btn-sm btn-block">Ver Todas las Alertas</a>
            </div>
        </div>
    </div>
    
</div>

<?php include 'includes/footer.php'; ?>
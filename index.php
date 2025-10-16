<?php
include 'conexion.php';

// Obtener estadísticas
$total_clientes = $conn->query("SELECT COUNT(*) as total FROM clientes")->fetch_assoc()['total'];
$creditos_activos = $conn->query("SELECT COUNT(*) as total FROM creditos WHERE estado = 'activo'")->fetch_assoc()['total'];
$creditos_morosos = $conn->query("SELECT COUNT(*) as total FROM creditos WHERE estado = 'moroso'")->fetch_assoc()['total'];
$monto_total_creditos = $conn->query("SELECT SUM(monto_total) as total FROM creditos WHERE estado = 'activo'")->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Sistema de Gestión de Clientes y Créditos</title>
    
    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    
    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
</head>

<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
        
        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            
            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-money-check-alt"></i>
                </div>
                <div class="sidebar-brand-text mx-3">Créditos <sup>v1.0</sup></div>
            </a>
            
            <!-- Divider -->
            <hr class="sidebar-divider my-0">
            
            <!-- Nav Item - Dashboard -->
            <li class="nav-item active">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <!-- Divider -->
            <hr class="sidebar-divider">
            
            <!-- Heading -->
            <div class="sidebar-heading">Gestión</div>
            
            <!-- Nav Item - Clientes -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseClientes">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Clientes</span>
                </a>
                <div id="collapseClientes" class="collapse">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Opciones:</h6>
                        <a class="collapse-item" href="clientes/listar_clientes.php">Listar Clientes</a>
                        <a class="collapse-item" href="clientes/registrar_cliente.php">Registrar Cliente</a>
                        <a class="collapse-item" href="clientes/buscar_cliente.php">Buscar Cliente</a>
                    </div>
                </div>
            </li>
            
            <!-- Nav Item - Créditos -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseCreditos">
                    <i class="fas fa-fw fa-credit-card"></i>
                    <span>Créditos</span>
                </a>
                <div id="collapseCreditos" class="collapse">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Opciones:</h6>
                        <a class="collapse-item" href="creditos/ver_creditos.php">Ver Créditos</a>
                        <a class="collapse-item" href="creditos/registrar_credito.php">Registrar Crédito</a>
                    </div>
                </div>
            </li>
            
            <!-- Nav Item - Cuotas -->
            <li class="nav-item">
                <a class="nav-link" href="cuotas/ver_cuotas_cliente.php">
                    <i class="fas fa-fw fa-calendar-alt"></i>
                    <span>Cuotas</span>
                </a>
            </li>
            
            <!-- Nav Item - Pagos -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePagos">
                    <i class="fas fa-fw fa-dollar-sign"></i>
                    <span>Pagos</span>
                </a>
                <div id="collapsePagos" class="collapse">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Opciones:</h6>
                        <a class="collapse-item" href="pagos/historial_pagos.php">Historial de Pagos</a>
                        <a class="collapse-item" href="cuotas/pagar_cuota.php">Registrar Pago</a>
                    </div>
                </div>
            </li>
            
            <!-- Divider -->
            <hr class="sidebar-divider">
            
            <!-- Heading -->
            <div class="sidebar-heading">Alertas y Reportes</div>
            
            <!-- Nav Item - Alertas -->
            <li class="nav-item">
                <a class="nav-link" href="alertas/alertas_vencimientos.php">
                    <i class="fas fa-fw fa-exclamation-triangle"></i>
                    <span>Alertas de Vencimiento</span>
                </a>
            </li>
            
            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">
            
            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
            
        </ul>
        <!-- End of Sidebar -->
        
        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            
            <!-- Main Content -->
            <div id="content">
                
                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    
                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    
                    <!-- Topbar Search -->
                    <form class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                        <div class="input-group">
                            <input type="text" class="form-control bg-light border-0 small" placeholder="Buscar cliente..." aria-label="Search">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button">
                                    <i class="fas fa-search fa-sm"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">
                        
                        <!-- Nav Item - Alerts -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown">
                                <i class="fas fa-bell fa-fw"></i>
                                <!-- Counter - Alerts -->
                                <span class="badge badge-danger badge-counter"><?php echo $creditos_morosos; ?></span>
                            </a>
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in">
                                <h6 class="dropdown-header">Centro de Alertas</h6>
                                <a class="dropdown-item d-flex align-items-center" href="alertas/alertas_vencimientos.php">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-warning">
                                            <i class="fas fa-exclamation-triangle text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">Créditos</div>
                                        <?php echo $creditos_morosos; ?> créditos morosos requieren atención
                                    </div>
                                </a>
                                <a class="dropdown-item text-center small text-gray-500" href="alertas/alertas_vencimientos.php">Ver Todas las Alertas</a>
                            </div>
                        </li>
                        
                        <div class="topbar-divider d-none d-sm-block"></div>
                        
                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">Administrador</span>
                                <img class="img-profile rounded-circle" src="img/undraw_profile.svg">
                            </a>
                        </li>
                        
                    </ul>
                    
                </nav>
                <!-- End of Topbar -->
                
                <!-- Begin Page Content -->
                <div class="container-fluid">
                    
                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
                    </div>
                    
                    <!-- Content Row -->
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
                    
                </div>
                <!-- /.container-fluid -->
                
            </div>
            <!-- End of Main Content -->
            
            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; Sistema de Gestión de Créditos 2025</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->
            
        </div>
        <!-- End of Content Wrapper -->
        
    </div>
    <!-- End of Page Wrapper -->
    
    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
    
    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    
    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    
    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>
    
</body>
</html>
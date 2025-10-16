<?php
include '../conexion.php';

$sql = "SELECT * FROM clientes ORDER BY fecha_registro DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Listar Clientes - Sistema de Gestión</title>
    
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
</head>

<body id="page-top">
    <div id="wrapper">
        
        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="../index.php">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-money-check-alt"></i>
                </div>
                <div class="sidebar-brand-text mx-3">Créditos <sup>v1.0</sup></div>
            </a>
            
            <hr class="sidebar-divider my-0">
            
            <li class="nav-item">
                <a class="nav-link" href="../index.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <hr class="sidebar-divider">
            
            <div class="sidebar-heading">Gestión</div>
            
            <li class="nav-item active">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseClientes">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Clientes</span>
                </a>
                <div id="collapseClientes" class="collapse show">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Opciones:</h6>
                        <a class="collapse-item active" href="listar_clientes.php">Listar Clientes</a>
                        <a class="collapse-item" href="registrar_cliente.php">Registrar Cliente</a>
                        <a class="collapse-item" href="buscar_cliente.php">Buscar Cliente</a>
                    </div>
                </div>
            </li>
            
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseCreditos">
                    <i class="fas fa-fw fa-credit-card"></i>
                    <span>Créditos</span>
                </a>
                <div id="collapseCreditos" class="collapse">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Opciones:</h6>
                        <a class="collapse-item" href="../creditos/ver_creditos.php">Ver Créditos</a>
                        <a class="collapse-item" href="../creditos/registrar_credito.php">Registrar Crédito</a>
                    </div>
                </div>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="../cuotas/ver_cuotas_cliente.php">
                    <i class="fas fa-fw fa-calendar-alt"></i>
                    <span>Cuotas</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="../pagos/historial_pagos.php">
                    <i class="fas fa-fw fa-dollar-sign"></i>
                    <span>Pagos</span>
                </a>
            </li>
            
            <hr class="sidebar-divider">
            
            <div class="sidebar-heading">Alertas</div>
            
            <li class="nav-item">
                <a class="nav-link" href="../alertas/alertas_vencimientos.php">
                    <i class="fas fa-fw fa-exclamation-triangle"></i>
                    <span>Alertas de Vencimiento</span>
                </a>
            </li>
            
            <hr class="sidebar-divider d-none d-md-block">
            
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
            
        </ul>
        <!-- End of Sidebar -->
        
        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            
            <div id="content">
                
                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">Administrador</span>
                                <img class="img-profile rounded-circle" src="../img/undraw_profile.svg">
                            </a>
                        </li>
                    </ul>
                    
                </nav>
                <!-- End of Topbar -->
                
                <!-- Begin Page Content -->
                <div class="container-fluid">
                    
                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Gestión de Clientes</h1>
                        <a href="registrar_cliente.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                            <i class="fas fa-plus fa-sm text-white-50"></i> Registrar Nuevo Cliente
                        </a>
                    </div>
                    
                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Listado de Clientes</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre Completo</th>
                                            <th>DNI</th>
                                            <th>Teléfono</th>
                                            <th>Ciudad</th>
                                            <th>Estado</th>
                                            <th>Fecha Registro</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $result->fetch_assoc()): 
                                            // Determinar el badge según el estado
                                            $badge_class = 'secondary';
                                            if ($row['estado'] == 'activo') $badge_class = 'success';
                                            elseif ($row['estado'] == 'moroso') $badge_class = 'danger';
                                            elseif ($row['estado'] == 'inactivo') $badge_class = 'warning';
                                        ?>
                                        <tr>
                                            <td><?php echo $row['id_cliente']; ?></td>
                                            <td>
                                                <strong><?php echo $row['nombre'] . ' ' . $row['apellido']; ?></strong>
                                                <?php if ($row['email']): ?>
                                                <br><small class="text-muted"><?php echo $row['email']; ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $row['dni']; ?></td>
                                            <td><?php echo $row['telefono'] ?: '-'; ?></td>
                                            <td><?php echo $row['ciudad'] ?: '-'; ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $badge_class; ?>">
                                                    <?php echo ucfirst($row['estado']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($row['fecha_registro'])); ?></td>
                                            <td>
                                                <a href="editar_cliente.php?id=<?php echo $row['id_cliente']; ?>" 
                                                   class="btn btn-info btn-sm btn-circle" 
                                                   title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="historial_cliente.php?id=<?php echo $row['id_cliente']; ?>" 
                                                   class="btn btn-primary btn-sm btn-circle" 
                                                   title="Ver Historial">
                                                    <i class="fas fa-history"></i>
                                                </a>
                                                <a href="eliminar_cliente.php?id=<?php echo $row['id_cliente']; ?>" 
                                                   class="btn btn-danger btn-sm btn-circle" 
                                                   title="Eliminar"
                                                   onclick="return confirm('¿Está seguro de eliminar este cliente?');">
                                                    <i class="fas fa-trash"></i>
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
            
        </div>
        <!-- End of Content Wrapper -->
        
    </div>
    <!-- End of Page Wrapper -->
    
    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
    
    <!-- Bootstrap core JavaScript-->
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    
    <!-- Core plugin JavaScript-->
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    
    <!-- Custom scripts for all pages-->
    <script src="../js/sb-admin-2.min.js"></script>
    
    <!-- Page level plugins -->
    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
    
    <!-- Page level custom scripts -->
    <script>
        $(document).ready(function() {
            $('#dataTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
                },
                "order": [[ 0, "desc" ]]
            });
        });
    </script>
    
</body>
</html>
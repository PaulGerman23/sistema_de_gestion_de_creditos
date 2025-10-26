<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo isset($page_title) ? $page_title : 'Sistema de Gestión'; ?> - Créditos</title>
    
    <!-- Custom fonts for this template-->
    <link href="<?php echo $base_url; ?>vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    
    <!-- Custom styles for this template-->
    <link href="<?php echo $base_url; ?>css/sb-admin-2.min.css" rel="stylesheet">
    
    <?php if (isset($extra_css)): ?>
        <?php echo $extra_css; ?>
    <?php endif; ?>
</head>

<body id="page-top">
    <div id="wrapper">
        
        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            
            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?php echo $base_url; ?>index.php">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-money-check-alt"></i>
                </div>
                <div class="sidebar-brand-text mx-3">Créditos <sup>v1.0</sup></div>
            </a>
            
            <!-- Divider -->
            <hr class="sidebar-divider my-0">
            
            <!-- Nav Item - Dashboard -->
            <li class="nav-item <?php echo ($active_page == 'dashboard') ? 'active' : ''; ?>">
                <a class="nav-link" href="<?php echo $base_url; ?>index.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <!-- Divider -->
            <hr class="sidebar-divider">
            
            <!-- Heading -->
            <div class="sidebar-heading">Gestión</div>
            
            <!-- Nav Item - Clientes -->
            <li class="nav-item <?php echo ($active_page == 'clientes') ? 'active' : ''; ?>">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseClientes" 
                   aria-expanded="<?php echo ($active_page == 'clientes') ? 'true' : 'false'; ?>">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Clientes</span>
                </a>
                <div id="collapseClientes" class="collapse <?php echo ($active_page == 'clientes') ? 'show' : ''; ?>">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Opciones:</h6>
                        <a class="collapse-item <?php echo ($active_subpage == 'listar_clientes') ? 'active' : ''; ?>" 
                           href="<?php echo $base_url; ?>clientes/listar_clientes.php">Listar Clientes</a>
                        <a class="collapse-item <?php echo ($active_subpage == 'registrar_cliente') ? 'active' : ''; ?>" 
                           href="<?php echo $base_url; ?>clientes/registrar_cliente.php">Registrar Cliente</a>                        
                    </div>
                </div>
            </li>
            
            <!-- Nav Item - Créditos -->
            <li class="nav-item <?php echo ($active_page == 'creditos') ? 'active' : ''; ?>">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseCreditos"
                   aria-expanded="<?php echo ($active_page == 'creditos') ? 'true' : 'false'; ?>">
                    <i class="fas fa-fw fa-credit-card"></i>
                    <span>Créditos</span>
                </a>
                <div id="collapseCreditos" class="collapse <?php echo ($active_page == 'creditos') ? 'show' : ''; ?>">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Opciones:</h6>
                        <a class="collapse-item <?php echo ($active_subpage == 'ver_creditos') ? 'active' : ''; ?>" 
                           href="<?php echo $base_url; ?>creditos/ver_creditos.php">Ver Créditos</a>
                        <a class="collapse-item <?php echo ($active_subpage == 'registrar_credito') ? 'active' : ''; ?>" 
                           href="<?php echo $base_url; ?>creditos/registrar_credito.php">Registrar Crédito</a>
                    </div>
                </div>
            </li>
            
            <!-- Nav Item - Cuotas -->
            <li class="nav-item <?php echo ($active_page == 'cuotas') ? 'active' : ''; ?>">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseCuotas"
                   aria-expanded="<?php echo ($active_page == 'cuotas') ? 'true' : 'false'; ?>">
                    <i class="fas fa-fw fa-calendar-alt"></i>
                    <span>Cuotas</span>
                </a>
                <div id="collapseCuotas" class="collapse <?php echo ($active_page == 'cuotas') ? 'show' : ''; ?>">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Opciones:</h6>
                        <a class="collapse-item <?php echo ($active_subpage == 'ver_cuotas') ? 'active' : ''; ?>" 
                           href="<?php echo $base_url; ?>cuotas/ver_cuotas_cliente.php">Ver Cuotas</a>
                        <a class="collapse-item <?php echo ($active_subpage == 'pagar_cuota') ? 'active' : ''; ?>" 
                           href="<?php echo $base_url; ?>cuotas/pagar_cuota.php">Registrar Pago</a>
                    </div>
                </div>
            </li>
            
            <!-- Nav Item - Pagos -->
            <li class="nav-item <?php echo ($active_page == 'pagos') ? 'active' : ''; ?>">
                <a class="nav-link" href="<?php echo $base_url; ?>pagos/historial_pagos.php">
                    <i class="fas fa-fw fa-dollar-sign"></i>
                    <span>Historial de Pagos</span>
                </a>
            </li>
            
            <!-- Divider -->
            <hr class="sidebar-divider">
            
            <!-- Heading -->
            <div class="sidebar-heading">Alertas</div>
            
            <!-- Nav Item - Alertas -->
            <li class="nav-item <?php echo ($active_page == 'alertas') ? 'active' : ''; ?>">
                <a class="nav-link" href="<?php echo $base_url; ?>alertas/alertas_vencimientos.php">
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
                            <input type="text" 
                                   class="form-control bg-light border-0 small" 
                                   placeholder="Buscar cliente..." 
                                   aria-label="Search"
                                   aria-describedby="basic-addon2"
                                   autocomplete="off">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button">
                                    <i class="fas fa-search fa-sm"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">
                        
                        <!-- Nav Item - Search Dropdown (Visible Only XS) -->
                        <li class="nav-item dropdown no-arrow d-sm-none">
                            <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-search fa-fw"></i>
                            </a>
                            <!-- Dropdown - Messages -->
                            <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in" aria-labelledby="searchDropdown">
                                <form class="form-inline mr-auto w-100 navbar-search">
                                    <div class="input-group">
                                        <input type="text" class="form-control bg-light border-0 small" placeholder="Buscar cliente..." aria-label="Search" aria-describedby="basic-addon2" autocomplete="off">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="button">
                                                <i class="fas fa-search fa-sm"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </li>
                        
                        <!-- Nav Item - Alerts -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown">
                                <i class="fas fa-bell fa-fw"></i>
                                <?php
                                // Contar créditos morosos para notificaciones
                                $creditos_morosos_count = $conn->query("SELECT COUNT(*) as total FROM creditos WHERE estado = 'moroso'")->fetch_assoc()['total'];
                                if ($creditos_morosos_count > 0):
                                ?>
                                <span class="badge badge-danger badge-counter"><?php echo $creditos_morosos_count; ?></span>
                                <?php endif; ?>
                            </a>
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in">
                                <h6 class="dropdown-header">Centro de Alertas</h6>
                                <a class="dropdown-item d-flex align-items-center" href="<?php echo $base_url; ?>alertas/alertas_vencimientos.php">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-warning">
                                            <i class="fas fa-exclamation-triangle text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">Créditos Morosos</div>
                                        <?php echo $creditos_morosos_count; ?> crédito(s) requieren atención
                                    </div>
                                </a>
                                <a class="dropdown-item text-center small text-gray-500" href="<?php echo $base_url; ?>alertas/alertas_vencimientos.php">Ver Todas las Alertas</a>
                            </div>
                        </li>
                        
                        <div class="topbar-divider d-none d-sm-block"></div>
                        
                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">Administrador</span>
                                <img class="img-profile rounded-circle" src="<?php echo $base_url; ?>img/undraw_profile.svg">
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in">
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Perfil
                                </a>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Configuración
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Cerrar Sesión
                                </a>
                            </div>
                        </li>
                        
                    </ul>
                    
                </nav>
                <!-- End of Topbar -->
                
                <!-- Begin Page Content -->
                <div class="container-fluid">
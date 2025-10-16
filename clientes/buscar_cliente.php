<?php
include '../conexion.php';

// Variables para el template
$base_url = '../';
$page_title = 'Buscar Cliente';
$active_page = 'clientes';
$active_subpage = 'buscar_cliente';

// CSS adicional
$extra_css = '<link href="../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">';

$filtro = $_GET['filtro'] ?? '';
$estado_filtro = $_GET['estado'] ?? 'todos';
$ciudad_filtro = $_GET['ciudad'] ?? '';

// Consulta de búsqueda
$sql = "SELECT * FROM clientes WHERE 1=1";
$params = [];
$types = "";

if ($filtro) {
    $sql .= " AND (nombre LIKE ? OR apellido LIKE ? OR dni LIKE ? OR telefono LIKE ? OR email LIKE ?)";
    $filtro_param = "%$filtro%";
    $params = array_merge($params, [$filtro_param, $filtro_param, $filtro_param, $filtro_param, $filtro_param]);
    $types .= "sssss";
}

if ($estado_filtro != 'todos') {
    $sql .= " AND estado = ?";
    $params[] = $estado_filtro;
    $types .= "s";
}

if ($ciudad_filtro) {
    $sql .= " AND ciudad LIKE ?";
    $ciudad_param = "%$ciudad_filtro%";
    $params[] = $ciudad_param;
    $types .= "s";
}

$sql .= " ORDER BY nombre, apellido";

if ($params) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

// Obtener ciudades para el filtro
$ciudades = $conn->query("SELECT DISTINCT ciudad FROM clientes WHERE ciudad IS NOT NULL AND ciudad != '' ORDER BY ciudad");

// Estadísticas de búsqueda
$total_resultados = $result->num_rows;

include '../includes/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Buscar Cliente</h1>
    <a href="registrar_cliente.php" class="btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Registrar Nuevo Cliente
    </a>
</div>

<!-- Formulario de Búsqueda -->
<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-search"></i> Filtros de Búsqueda
                </h6>
            </div>
            <div class="card-body">
                <form method="GET" action="">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="filtro">Búsqueda General</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="filtro" 
                                   name="filtro" 
                                   placeholder="Nombre, DNI, teléfono, email..."
                                   value="<?php echo htmlspecialchars($filtro); ?>">
                            <small class="form-text text-muted">Busca en múltiples campos</small>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label for="ciudad">Ciudad</label>
                            <select class="form-control" id="ciudad" name="ciudad">
                                <option value="">Todas las ciudades</option>
                                <?php while ($ciudad = $ciudades->fetch_assoc()): ?>
                                    <option value="<?php echo $ciudad['ciudad']; ?>" 
                                            <?php echo ($ciudad_filtro == $ciudad['ciudad']) ? 'selected' : ''; ?>>
                                        <?php echo $ciudad['ciudad']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label for="estado">Estado</label>
                            <select class="form-control" id="estado" name="estado">
                                <option value="todos" <?php echo ($estado_filtro == 'todos') ? 'selected' : ''; ?>>Todos</option>
                                <option value="activo" <?php echo ($estado_filtro == 'activo') ? 'selected' : ''; ?>>Activo</option>
                                <option value="inactivo" <?php echo ($estado_filtro == 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                                <option value="moroso" <?php echo ($estado_filtro == 'moroso') ? 'selected' : ''; ?>>Moroso</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2 mb-3">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                    
                    <?php if ($filtro || $estado_filtro != 'todos' || $ciudad_filtro): ?>
                    <div class="row">
                        <div class="col-12">
                            <a href="buscar_cliente.php" class="btn btn-secondary btn-sm">
                                <i class="fas fa-times"></i> Limpiar Filtros
                            </a>
                            <span class="ml-3 text-muted">
                                <i class="fas fa-info-circle"></i> 
                                Se encontraron <strong><?php echo $total_resultados; ?></strong> resultado(s)
                            </span>
                        </div>
                    </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Resultados de Búsqueda -->
<?php if ($filtro || $estado_filtro != 'todos' || $ciudad_filtro): ?>

    <?php if ($total_resultados > 0): ?>
    
    <!-- Estadísticas Rápidas -->
    <div class="row mb-4">
        <?php
        // Contar por estado en los resultados
        $result->data_seek(0); // Reiniciar el puntero
        $count_activo = 0;
        $count_inactivo = 0;
        $count_moroso = 0;
        
        while ($row = $result->fetch_assoc()) {
            if ($row['estado'] == 'activo') $count_activo++;
            elseif ($row['estado'] == 'inactivo') $count_inactivo++;
            elseif ($row['estado'] == 'moroso') $count_moroso++;
        }
        $result->data_seek(0); // Reiniciar nuevamente
        ?>
        
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Clientes Activos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $count_activo; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Clientes Inactivos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $count_inactivo; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-pause-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Clientes Morosos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $count_moroso; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tabla de Resultados -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Resultados de la Búsqueda (<?php echo $total_resultados; ?> cliente(s))
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre Completo</th>
                            <th>DNI</th>
                            <th>Teléfono</th>
                            <th>Ciudad</th>
                            <th>Email</th>
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
                                <?php if ($row['direccion']): ?>
                                <br><small class="text-muted">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo $row['direccion']; ?>
                                </small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $row['dni']; ?></td>
                            <td>
                                <?php if ($row['telefono']): ?>
                                    <a href="tel:<?php echo $row['telefono']; ?>">
                                        <i class="fas fa-phone"></i> <?php echo $row['telefono']; ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $row['ciudad'] ?: '-'; ?></td>
                            <td>
                                <?php if ($row['email']): ?>
                                    <a href="mailto:<?php echo $row['email']; ?>">
                                        <i class="fas fa-envelope"></i> <?php echo $row['email']; ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo $badge_class; ?>">
                                    <?php echo ucfirst($row['estado']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($row['fecha_registro'])); ?></td>
                            <td class="text-center">
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
                                <a href="../cuotas/ver_cuotas_cliente.php?id_cliente=<?php echo $row['id_cliente']; ?>" 
                                   class="btn btn-warning btn-sm btn-circle" 
                                   title="Ver Cuotas">
                                    <i class="fas fa-calendar-alt"></i>
                                </a>
                                <a href="eliminar_cliente.php?id=<?php echo $row['id_cliente']; ?>" 
                                   class="btn btn-danger btn-sm btn-circle" 
                                   title="Eliminar"
                                   onclick="return confirm('¿Está seguro de eliminar este cliente? Esta acción no se puede deshacer.');">
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
    
    <?php else: ?>
    
    <!-- Sin Resultados -->
    <div class="card shadow mb-4">
        <div class="card-body text-center py-5">
            <i class="fas fa-search fa-3x text-gray-300 mb-3"></i>
            <h4 class="text-gray-600">No se encontraron resultados</h4>
            <p class="text-muted">
                No hay clientes que coincidan con los criterios de búsqueda.
                <br>Intente con otros filtros o 
                <a href="buscar_cliente.php">limpie los filtros</a> para ver todos los clientes.
            </p>
            <a href="registrar_cliente.php" class="btn btn-primary mt-3">
                <i class="fas fa-plus"></i> Registrar Nuevo Cliente
            </a>
        </div>
    </div>
    
    <?php endif; ?>

<?php else: ?>

<!-- Vista Inicial - Sin Búsqueda -->
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-body text-center py-5">
                <i class="fas fa-search fa-3x text-primary mb-3"></i>
                <h4 class="text-gray-800 mb-3">Buscar Clientes</h4>
                <p class="text-muted mb-4">
                    Utilice los filtros de arriba para buscar clientes por nombre, DNI, ciudad o estado.
                    <br>También puede buscar por teléfono o email.
                </p>
                
                <div class="row mt-4">
                    <div class="col-md-4 mb-3">
                        <div class="card border-left-primary h-100">
                            <div class="card-body">
                                <h6 class="font-weight-bold text-primary">
                                    <i class="fas fa-user"></i> Búsqueda General
                                </h6>
                                <p class="small text-muted mb-0">
                                    Busque por nombre, apellido, DNI, teléfono o email en un solo campo.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <div class="card border-left-success h-100">
                            <div class="card-body">
                                <h6 class="font-weight-bold text-success">
                                    <i class="fas fa-filter"></i> Filtros Específicos
                                </h6>
                                <p class="small text-muted mb-0">
                                    Filtre por ciudad o estado del cliente para resultados más precisos.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <div class="card border-left-info h-100">
                            <div class="card-body">
                                <h6 class="font-weight-bold text-info">
                                    <i class="fas fa-list"></i> Ver Todos
                                </h6>
                                <p class="small text-muted mb-0">
                                    <a href="listar_clientes.php">Haga clic aquí</a> para ver el listado completo de clientes.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Estadísticas Generales -->
                <hr class="my-4">
                <h6 class="text-gray-700 mb-3">Estadísticas Generales del Sistema</h6>
                <div class="row">
                    <?php
                    $total_clientes = $conn->query("SELECT COUNT(*) as total FROM clientes")->fetch_assoc()['total'];
                    $clientes_activos = $conn->query("SELECT COUNT(*) as total FROM clientes WHERE estado = 'activo'")->fetch_assoc()['total'];
                    $clientes_con_credito = $conn->query("SELECT COUNT(DISTINCT id_cliente) as total FROM creditos")->fetch_assoc()['total'];
                    ?>
                    
                    <div class="col-md-4">
                        <h3 class="text-primary mb-0"><?php echo $total_clientes; ?></h3>
                        <small class="text-muted">Total de Clientes</small>
                    </div>
                    <div class="col-md-4">
                        <h3 class="text-success mb-0"><?php echo $clientes_activos; ?></h3>
                        <small class="text-muted">Clientes Activos</small>
                    </div>
                    <div class="col-md-4">
                        <h3 class="text-info mb-0"><?php echo $clientes_con_credito; ?></h3>
                        <small class="text-muted">Con Créditos</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Búsquedas Rápidas -->
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-bolt"></i> Búsquedas Rápidas
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="?estado=activo" class="btn btn-success btn-block">
                            <i class="fas fa-check-circle"></i> Clientes Activos
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="?estado=moroso" class="btn btn-danger btn-block">
                            <i class="fas fa-exclamation-triangle"></i> Clientes Morosos
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="?estado=inactivo" class="btn btn-warning btn-block">
                            <i class="fas fa-pause-circle"></i> Clientes Inactivos
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="listar_clientes.php" class="btn btn-primary btn-block">
                            <i class="fas fa-list"></i> Ver Todos
                        </a>
                    </div>
                </div>
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
            "order": [[ 0, "desc" ]],
            "pageLength": 25
        });
    });
</script>
';

include '../includes/footer.php';
?>
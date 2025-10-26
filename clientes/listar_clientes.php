<?php
$base_url = '../';
$page_title = 'Listar Clientes';
$active_page = 'clientes';
$active_subpage = 'listar_clientes';

// DataTables CSS
$extra_css = '<link href="' . $base_url . 'vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">';

include '../conexion.php';
include '../includes/header.php';

// Obtener filtros
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : 'todos';
$filtro_ciudad = isset($_GET['ciudad']) ? $_GET['ciudad'] : '';
$busqueda = isset($_GET['buscar']) ? $_GET['buscar'] : '';

// Construir consulta SQL con filtros
$sql = "SELECT 
            id_cliente,
            nombre,
            apellido,
            CONCAT(nombre, ' ', apellido) as nombre_completo,
            dni,
            telefono,
            ciudad,
            email,
            estado
        FROM clientes 
        WHERE 1=1";

// Filtro por estado
if ($filtro_estado !== 'todos') {
    $sql .= " AND estado = '" . $conn->real_escape_string($filtro_estado) . "'";
}

// Filtro por ciudad
if (!empty($filtro_ciudad)) {
    $sql .= " AND ciudad LIKE '%" . $conn->real_escape_string($filtro_ciudad) . "%'";
}

// Filtro por búsqueda
if (!empty($busqueda)) {
    $sql .= " AND (
        nombre LIKE '%" . $conn->real_escape_string($busqueda) . "%' OR
        apellido LIKE '%" . $conn->real_escape_string($busqueda) . "%' OR
        CONCAT(nombre, ' ', apellido) LIKE '%" . $conn->real_escape_string($busqueda) . "%' OR
        dni LIKE '%" . $conn->real_escape_string($busqueda) . "%' OR
        email LIKE '%" . $conn->real_escape_string($busqueda) . "%'
    )";
}

$sql .= " ORDER BY nombre, apellido";
$result = $conn->query($sql);

// Obtener estadísticas
$total_clientes = $conn->query("SELECT COUNT(*) as total FROM clientes")->fetch_assoc()['total'];
$clientes_activos = $conn->query("SELECT COUNT(*) as total FROM clientes WHERE estado = 'activo'")->fetch_assoc()['total'];
$clientes_morosos = $conn->query("SELECT COUNT(*) as total FROM clientes WHERE estado = 'moroso'")->fetch_assoc()['total'];
$clientes_inactivos = $conn->query("SELECT COUNT(*) as total FROM clientes WHERE estado = 'inactivo'")->fetch_assoc()['total'];
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Listado de Clientes</h1>
    <a href="registrar_cliente.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-user-plus fa-sm text-white-50"></i> Registrar Nuevo Cliente
    </a>
</div>

<!-- Tarjetas de Estadísticas -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Clientes</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_clientes; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Activos</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $clientes_activos; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Morosos</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $clientes_morosos; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-secondary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Inactivos</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $clientes_inactivos; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-slash fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros de Búsqueda -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Buscar y Filtrar Clientes</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="buscar">Búsqueda General</label>
                    <input type="text" 
                           class="form-control" 
                           id="buscar" 
                           name="buscar" 
                           placeholder="Nombre, DNI, Email..." 
                           value="<?php echo htmlspecialchars($busqueda); ?>">
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="estado">Estado</label>
                    <select class="form-control" id="estado" name="estado">
                        <option value="todos" <?php echo $filtro_estado == 'todos' ? 'selected' : ''; ?>>Todos</option>
                        <option value="activo" <?php echo $filtro_estado == 'activo' ? 'selected' : ''; ?>>Activos</option>
                        <option value="moroso" <?php echo $filtro_estado == 'moroso' ? 'selected' : ''; ?>>Morosos</option>
                        <option value="inactivo" <?php echo $filtro_estado == 'inactivo' ? 'selected' : ''; ?>>Inactivos</option>
                    </select>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="ciudad">Ciudad</label>
                    <input type="text" 
                           class="form-control" 
                           id="ciudad" 
                           name="ciudad" 
                           placeholder="Ciudad..." 
                           value="<?php echo htmlspecialchars($filtro_ciudad); ?>">
                </div>
                
                <div class="col-md-2 mb-3">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
            </div>
            
            <?php if (!empty($busqueda) || $filtro_estado != 'todos' || !empty($filtro_ciudad)): ?>
            <div class="row">
                <div class="col-12">
                    <a href="listar_clientes.php" class="btn btn-secondary btn-sm">
                        <i class="fas fa-times"></i> Limpiar Filtros
                    </a>
                    <span class="text-muted ml-2">
                        <i class="fas fa-info-circle"></i> 
                        Mostrando <?php echo $result->num_rows; ?> de <?php echo $total_clientes; ?> clientes
                    </span>
                </div>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- DataTales -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Clientes Registrados</h6>
        <div class="btn-group" role="group">
            <a href="?estado=activo" class="btn btn-sm btn-success <?php echo $filtro_estado == 'activo' ? 'active' : ''; ?>">
                <i class="fas fa-check"></i> Activos
            </a>
            <a href="?estado=moroso" class="btn btn-sm btn-danger <?php echo $filtro_estado == 'moroso' ? 'active' : ''; ?>">
                <i class="fas fa-exclamation-triangle"></i> Morosos
            </a>
            <a href="?estado=inactivo" class="btn btn-sm btn-secondary <?php echo $filtro_estado == 'inactivo' ? 'active' : ''; ?>">
                <i class="fas fa-user-slash"></i> Inactivos
            </a>
            <a href="listar_clientes.php" class="btn btn-sm btn-primary <?php echo $filtro_estado == 'todos' ? 'active' : ''; ?>">
                <i class="fas fa-list"></i> Todos
            </a>
        </div>
    </div>
    <div class="card-body">
        <?php if ($result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre Completo</th>
                        <th>DNI</th>
                        <th>Teléfono</th>
                        <th>Ciudad</th>
                        <th>Email</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): 
                        $badge_class = $row['estado'] == 'activo' ? 'success' : ($row['estado'] == 'moroso' ? 'danger' : 'secondary');
                    ?>
                    <tr>
                        <td><?php echo $row['id_cliente']; ?></td>
                        <td><strong><?php echo htmlspecialchars($row['nombre_completo']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['dni']); ?></td>
                        <td><?php echo htmlspecialchars($row['telefono'] ?? 'Sin teléfono'); ?></td>
                        <td><?php echo htmlspecialchars($row['ciudad'] ?? 'Sin ciudad'); ?></td>
                        <td><?php echo htmlspecialchars($row['email'] ?? 'Sin email'); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $badge_class; ?>">
                                <?php echo ucfirst($row['estado']); ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="editar_cliente.php?id=<?php echo $row['id_cliente']; ?>" 
                               class="btn btn-info btn-sm" 
                               title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="historial_cliente.php?id=<?php echo $row['id_cliente']; ?>" 
                               class="btn btn-warning btn-sm" 
                               title="Historial">
                                <i class="fas fa-history"></i>
                            </a>
                            <a href="eliminar_cliente.php?id=<?php echo $row['id_cliente']; ?>" 
                               class="btn btn-danger btn-sm" 
                               onclick="return confirm('¿Está seguro de eliminar este cliente? Se eliminarán también sus créditos y cuotas.');"
                               title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="alert alert-warning text-center" role="alert">
            <i class="fas fa-search fa-3x mb-3"></i>
            <h5>No se encontraron clientes</h5>
            <p class="mb-0">No hay clientes que coincidan con los filtros seleccionados.</p>
            <a href="listar_clientes.php" class="btn btn-primary mt-3">
                <i class="fas fa-list"></i> Ver Todos los Clientes
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
// DataTables JS con configuración mejorada
$extra_js = '
<script src="' . $base_url . 'vendor/datatables/jquery.dataTables.min.js"></script>
<script src="' . $base_url . 'vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script>
$(document).ready(function() {
    $("#dataTable").DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "pageLength": 25,
        "order": [[1, "asc"]], // Ordenar por nombre completo
        "responsive": true,
        "dom": "<\"row\"<\"col-sm-12 col-md-6\"l><\"col-sm-12 col-md-6\"f>>" +
               "<\"row\"<\"col-sm-12\"tr>>" +
               "<\"row\"<\"col-sm-12 col-md-5\"i><\"col-sm-12 col-md-7\"p>>"
    });
});
</script>
';

include '../includes/footer.php';
?>
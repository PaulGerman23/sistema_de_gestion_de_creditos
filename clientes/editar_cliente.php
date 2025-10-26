<?php
include '../conexion.php';

// Variables para el template
$base_url = '../';
$page_title = 'Editar Cliente';
$active_page = 'clientes';
$active_subpage = 'listar_clientes';

$mensaje = '';
$tipo_mensaje = '';

$id_cliente = $_GET['id'] ?? 0;

if (!$id_cliente) {
    header("Location: listar_clientes.php");
    exit;
}

// Obtener datos del cliente
$stmt = $conn->prepare("SELECT * FROM clientes WHERE id_cliente = ?");
$stmt->bind_param("i", $id_cliente);
$stmt->execute();
$cliente = $stmt->get_result()->fetch_assoc();

if (!$cliente) {
    header("Location: listar_clientes.php");
    exit;
}

// Procesar actualización
if ($_POST) {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $dni = $_POST['dni'];
    $telefono = $_POST['telefono'];
    $direccion = $_POST['direccion'];
    $ciudad = $_POST['ciudad'];
    $email = $_POST['email'];
    $estado = $_POST['estado'];

    // Validar que el DNI no esté duplicado (excepto el actual)
    $stmt = $conn->prepare("SELECT id_cliente FROM clientes WHERE dni = ? AND id_cliente != ?");
    $stmt->bind_param("si", $dni, $id_cliente);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $mensaje = "Error: Ya existe otro cliente con ese DNI.";
        $tipo_mensaje = "danger";
    } else {
        $stmt = $conn->prepare("UPDATE clientes SET nombre=?, apellido=?, dni=?, telefono=?, direccion=?, ciudad=?, email=?, estado=? WHERE id_cliente=?");
        $stmt->bind_param("ssssssssi", $nombre, $apellido, $dni, $telefono, $direccion, $ciudad, $email, $estado, $id_cliente);
        
        if ($stmt->execute()) {
            $mensaje = "¡Cliente actualizado correctamente!";
            $tipo_mensaje = "success";
            
            // Recargar datos actualizados
            $stmt = $conn->prepare("SELECT * FROM clientes WHERE id_cliente = ?");
            $stmt->bind_param("i", $id_cliente);
            $stmt->execute();
            $cliente = $stmt->get_result()->fetch_assoc();
        } else {
            $mensaje = "Error al actualizar el cliente: " . $stmt->error;
            $tipo_mensaje = "danger";
        }
    }
}

include '../includes/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Editar Cliente</h1>
    <a href="listar_clientes.php" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm"></i> Volver al Listado
    </a>
</div>

<!-- Mensaje de respuesta -->
<?php if ($mensaje): ?>
<div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
    <strong><?php echo $tipo_mensaje == 'success' ? '¡Éxito!' : '¡Error!'; ?></strong> <?php echo $mensaje; ?>
    <button type="button" class="close" data-dismiss="alert">
        <span>&times;</span>
    </button>
</div>
<?php endif; ?>

<!-- Formulario de Edición -->
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    Datos del Cliente #<?php echo $id_cliente; ?>
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombre">Nombre <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="nombre" 
                                       name="nombre" 
                                       value="<?php echo htmlspecialchars($cliente['nombre']); ?>"
                                       required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="apellido">Apellido <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="apellido" 
                                       name="apellido" 
                                       value="<?php echo htmlspecialchars($cliente['apellido']); ?>"
                                       required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="dni">DNI <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="dni" 
                                       name="dni" 
                                       value="<?php echo htmlspecialchars($cliente['dni']); ?>"
                                       required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="telefono">Teléfono</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="telefono" 
                                       name="telefono" 
                                       value="<?php echo htmlspecialchars($cliente['telefono']); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="direccion">Dirección</label>
                        <input type="text" 
                               class="form-control" 
                               id="direccion" 
                               name="direccion" 
                               value="<?php echo htmlspecialchars($cliente['direccion']); ?>">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ciudad">Ciudad</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="ciudad" 
                                       name="ciudad" 
                                       value="<?php echo htmlspecialchars($cliente['ciudad']); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       value="<?php echo htmlspecialchars($cliente['email']); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="estado">Estado <span class="text-danger">*</span></label>
                        <select class="form-control" id="estado" name="estado" required>
                            <option value="activo" <?php echo ($cliente['estado'] == 'activo') ? 'selected' : ''; ?>>Activo</option>
                            <option value="inactivo" <?php echo ($cliente['estado'] == 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                            <option value="moroso" <?php echo ($cliente['estado'] == 'moroso') ? 'selected' : ''; ?>>Moroso</option>
                        </select>
                    </div>
                    
                    <hr>
                    
                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary btn-icon-split">
                            <span class="icon text-white-50">
                                <i class="fas fa-save"></i>
                            </span>
                            <span class="text">Guardar Cambios</span>
                        </button>
                        <a href="listar_clientes.php" class="btn btn-secondary btn-icon-split">
                            <span class="icon text-white-50">
                                <i class="fas fa-times"></i>
                            </span>
                            <span class="text">Cancelar</span>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Sidebar con información -->
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Información</h6>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Fecha de Registro:</strong></p>
                <p class="mb-3"><?php echo date('d/m/Y', strtotime($cliente['fecha_registro'])); ?></p>
                
                <hr>
                
                <p class="mb-2"><strong>Estado Actual:</strong></p>
                <p class="mb-3">
                    <span class="badge badge-<?php 
                        echo $cliente['estado'] == 'activo' ? 'success' : 
                            ($cliente['estado'] == 'moroso' ? 'danger' : 'warning'); 
                    ?>">
                        <?php echo ucfirst($cliente['estado']); ?>
                    </span>
                </p>
                
                <hr>
                
                <p class="small text-muted mb-0">
                    <strong>Nota:</strong> Los cambios en el estado del cliente no afectarán sus créditos existentes.
                </p>
            </div>
        </div>
        
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-info">Acciones Rápidas</h6>
            </div>
            <div class="card-body">
                <a href="historial_cliente.php?id=<?php echo $id_cliente; ?>" class="btn btn-info btn-sm btn-block">
                    <i class="fas fa-history"></i> Ver Historial
                </a>
                <a href="../cuotas/ver_cuotas_cliente.php?id_cliente=<?php echo $id_cliente; ?>" class="btn btn-warning btn-sm btn-block">
                    <i class="fas fa-calendar-alt"></i> Ver Cuotas
                </a>
                <a href="eliminar_cliente.php?id=<?php echo $id_cliente; ?>" 
                   class="btn btn-danger btn-sm btn-block"
                   onclick="return confirm('¿Está seguro de eliminar este cliente?');">
                    <i class="fas fa-trash"></i> Eliminar Cliente
                </a>
            </div>
        </div>
    </div>
</div>

<?php
include '../includes/footer.php';
?>
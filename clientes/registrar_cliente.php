<?php
include '../conexion.php';

// Variables para el template
$base_url = '../';
$page_title = 'Registrar Cliente';
$active_page = 'clientes';
$active_subpage = 'registrar_cliente';

$mensaje = '';
$tipo_mensaje = '';

if ($_POST) {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $dni = $_POST['dni'];
    $telefono = $_POST['telefono'];
    $direccion = $_POST['direccion'];
    $ciudad = $_POST['ciudad'];
    $email = $_POST['email'];

    // Validar que el DNI no exista
    $stmt = $conn->prepare("SELECT id_cliente FROM clientes WHERE dni = ?");
    $stmt->bind_param("s", $dni);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $mensaje = "Error: Ya existe un cliente con ese DNI.";
        $tipo_mensaje = "danger";
    } else {
        $stmt = $conn->prepare("INSERT INTO clientes (nombre, apellido, dni, telefono, direccion, ciudad, email, estado, fecha_registro) VALUES (?, ?, ?, ?, ?, ?, ?, 'activo', NOW())");
        $stmt->bind_param("sssssss", $nombre, $apellido, $dni, $telefono, $direccion, $ciudad, $email);
        if ($stmt->execute()) {
            $mensaje = "¡Cliente registrado correctamente!";
            $tipo_mensaje = "success";
            // Limpiar campos después del registro exitoso
            $_POST = array();
        } else {
            $mensaje = "Error al registrar el cliente: " . $stmt->error;
            $tipo_mensaje = "danger";
        }
    }
}

include '../includes/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Registrar Nuevo Cliente</h1>
    <a href="listar_clientes.php" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver al Listado
    </a>
</div>

<!-- Mensaje de respuesta -->
<?php if ($mensaje): ?>
<div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
    <strong><?php echo $tipo_mensaje == 'success' ? '¡Éxito!' : '¡Error!'; ?></strong> <?php echo $mensaje; ?>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<!-- Formulario de Registro -->
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Datos del Cliente</h6>
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
                                       placeholder="Ingrese el nombre" 
                                       value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>"
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
                                       placeholder="Ingrese el apellido"
                                       value="<?php echo isset($_POST['apellido']) ? htmlspecialchars($_POST['apellido']) : ''; ?>"
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
                                       placeholder="Ingrese el DNI"
                                       value="<?php echo isset($_POST['dni']) ? htmlspecialchars($_POST['dni']) : ''; ?>"
                                       required>
                                <small class="form-text text-muted">El DNI debe ser único</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="telefono">Teléfono</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="telefono" 
                                       name="telefono" 
                                       placeholder="Ingrese el teléfono"
                                       value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="direccion">Dirección</label>
                        <input type="text" 
                               class="form-control" 
                               id="direccion" 
                               name="direccion" 
                               placeholder="Ingrese la dirección"
                               value="<?php echo isset($_POST['direccion']) ? htmlspecialchars($_POST['direccion']) : ''; ?>">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ciudad">Ciudad</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="ciudad" 
                                       name="ciudad" 
                                       placeholder="Ingrese la ciudad"
                                       value="<?php echo isset($_POST['ciudad']) ? htmlspecialchars($_POST['ciudad']) : ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       placeholder="ejemplo@correo.com"
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary btn-icon-split">
                            <span class="icon text-white-50">
                                <i class="fas fa-check"></i>
                            </span>
                            <span class="text">Registrar Cliente</span>
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
                <p class="mb-2"><strong>Campos obligatorios (*)</strong></p>
                <ul class="pl-3">
                    <li>Nombre</li>
                    <li>Apellido</li>
                    <li>DNI</li>
                </ul>
                
                <hr>
                
                <p class="mb-2"><strong>Nota:</strong></p>
                <p class="small text-muted mb-0">
                    El DNI debe ser único en el sistema. Si intenta registrar un cliente con un DNI ya existente, 
                    recibirá un mensaje de error.
                </p>
                
                <hr>
                
                <p class="mb-2"><strong>Estado del cliente:</strong></p>
                <p class="small text-muted mb-0">
                    Los nuevos clientes se registran automáticamente con estado "Activo".
                </p>
            </div>
        </div>
    </div>
</div>

<?php
include '../includes/footer.php';
?>
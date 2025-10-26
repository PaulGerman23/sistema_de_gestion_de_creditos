<?php
include '../conexion.php';

// Variables para el template
$base_url = '../';
$page_title = 'Editar Cliente';
$active_page = 'clientes';
$active_subpage = 'listar_clientes';

$mensaje = '';
$tipo_mensaje = '';
$errores = [];

$id_cliente = $_GET['id'] ?? 0;

// Validar que el ID sea numérico y positivo
if (!is_numeric($id_cliente) || $id_cliente <= 0) {
    header("Location: listar_clientes.php");
    exit;
}

// Incluir funciones de validación
include 'funciones_validacion.php';

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
    // Sanitizar datos
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $dni_raw = trim($_POST['dni']);
    $telefono_raw = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $ciudad = trim($_POST['ciudad'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $estado = $_POST['estado'];
    
    // Validar estado
    $estados_validos = ['activo', 'inactivo', 'moroso'];
    if (!in_array($estado, $estados_validos)) {
        $errores[] = "El estado seleccionado no es válido";
    }
    
    // Validar nombre
    $validacion_nombre = validarNombre($nombre, 'nombre');
    if ($validacion_nombre !== true) {
        $errores[] = $validacion_nombre;
    }
    
    // Validar apellido
    $validacion_apellido = validarNombre($apellido, 'apellido');
    if ($validacion_apellido !== true) {
        $errores[] = $validacion_apellido;
    }
    
    // Validar DNI
    $validacion_dni = validarDNI($dni_raw);
    if (is_string($validacion_dni) && strlen($validacion_dni) <= 8) {
        $dni = $validacion_dni;
    } else {
        $errores[] = $validacion_dni;
    }
    
    // Validar teléfono
    $validacion_telefono = validarTelefono($telefono_raw);
    if ($validacion_telefono === true || strlen($validacion_telefono) >= 10) {
        $telefono = $validacion_telefono === true ? '' : $validacion_telefono;
    } else {
        $errores[] = $validacion_telefono;
    }
    
    // Validar email
    $validacion_email = validarEmail($email);
    if ($validacion_email !== true) {
        $errores[] = $validacion_email;
    }
    
    // Validar ciudad
    $validacion_ciudad = validarCiudad($ciudad);
    if ($validacion_ciudad !== true) {
        $errores[] = $validacion_ciudad;
    }
    
    // Validar dirección
    if (strlen($direccion) > 255) {
        $errores[] = "La dirección no puede tener más de 255 caracteres";
    }
    
    // Si no hay errores, verificar DNI duplicado
    if (empty($errores)) {
        $stmt = $conn->prepare("SELECT id_cliente FROM clientes WHERE dni = ? AND id_cliente != ?");
        $stmt->bind_param("si", $dni, $id_cliente);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $errores[] = "Ya existe otro cliente con ese DNI";
            $tipo_mensaje = "danger";
        } else {
            // Capitalizar
            $nombre = ucwords(strtolower($nombre));
            $apellido = ucwords(strtolower($apellido));
            $ciudad = ucwords(strtolower($ciudad));
            
            // Actualizar cliente
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
                $errores[] = "Error al actualizar el cliente en la base de datos";
                $tipo_mensaje = "danger";
            }
        }
    } else {
        $tipo_mensaje = "danger";
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

<!-- Errores de validación -->
<?php if (!empty($errores)): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <h5><i class="fas fa-exclamation-triangle"></i> Se encontraron los siguientes errores:</h5>
    <ul class="mb-0">
        <?php foreach ($errores as $error): ?>
            <li><?php echo htmlspecialchars($error); ?></li>
        <?php endforeach; ?>
    </ul>
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
                <form method="POST" action="" id="formEditarCliente">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombre">Nombre <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="nombre" 
                                       name="nombre" 
                                       value="<?php echo htmlspecialchars($cliente['nombre']); ?>"
                                       pattern="[a-záéíóúñüA-ZÁÉÍÓÚÑÜ\s]+"
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
                                       pattern="[a-záéíóúñüA-ZÁÉÍÓÚÑÜ\s]+"
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
                                       pattern="\d{7,8}"
                                       maxlength="8"
                                       required>
                                <small class="form-text text-muted">Debe ser único en el sistema</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="telefono">Teléfono</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="telefono" 
                                       name="telefono" 
                                       value="<?php echo htmlspecialchars($cliente['telefono']); ?>"
                                       pattern="[\d\s\-\(\)\+]{10,13}">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="direccion">Dirección</label>
                        <input type="text" 
                               class="form-control" 
                               id="direccion" 
                               name="direccion" 
                               value="<?php echo htmlspecialchars($cliente['direccion']); ?>"
                               maxlength="255">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ciudad">Ciudad</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="ciudad" 
                                       name="ciudad" 
                                       value="<?php echo htmlspecialchars($cliente['ciudad']); ?>"
                                       pattern="[a-záéíóúñüA-ZÁÉÍÓÚÑÜ\s\.\-]+"
                                       maxlength="100">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       value="<?php echo htmlspecialchars($cliente['email']); ?>"
                                       pattern="[a-zA-Z0-9._%+-]+@(gmail|hotmail|yahoo|outlook)\.(com|com\.ar|ar|es)">
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
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> El cambio de estado no afectará los créditos existentes
                        </small>
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
                    <strong>Nota:</strong> Todos los cambios se validarán antes de guardar. Asegúrese de que los datos sean correctos.
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
$extra_js = '
<script>
$(document).ready(function() {
    // Las mismas validaciones que en registrar_cliente.php
    $("#dni").on("input", function() {
        var dni = $(this).val().replace(/[^\d]/g, "");
        $(this).val(dni);
        
        if (dni.length >= 7 && dni.length <= 8) {
            $(this).removeClass("is-invalid").addClass("is-valid");
        } else if (dni.length > 0) {
            $(this).addClass("is-invalid");
        }
    });
    
    $("#nombre, #apellido").on("input", function() {
        var valor = $(this).val();
        var regex = /^[a-záéíóúñüA-ZÁÉÍÓÚÑÜ\s]*$/;
        
        if (!regex.test(valor)) {
            var valorLimpio = valor.replace(/[^a-záéíóúñüA-ZÁÉÍÓÚÑÜ\s]/g, "");
            $(this).val(valorLimpio);
        }
    });
    
    $("#email").on("blur", function() {
        var email = $(this).val();
        if (email.length === 0) {
            $(this).removeClass("is-invalid is-valid");
            return;
        }
        
        var dominiosValidos = ["gmail.com", "hotmail.com", "hotmail.ar", "yahoo.com", "yahoo.com.ar", "outlook.com", "outlook.es"];
        var partes = email.split("@");
        
        if (partes.length === 2 && dominiosValidos.includes(partes[1].toLowerCase())) {
            $(this).removeClass("is-invalid").addClass("is-valid");
        } else {
            $(this).removeClass("is-valid").addClass("is-invalid");
        }
    });
    
    $("#formEditarCliente").on("submit", function(e) {
        if (!confirm("¿Confirma la actualización de los datos del cliente?")) {
            e.preventDefault();
            return false;
        }
    });
});
</script>
';

include '../includes/footer.php';
?>
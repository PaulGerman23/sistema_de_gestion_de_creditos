<?php
include '../conexion.php';

// Variables para el template
$base_url = '../';
$page_title = 'Registrar Cliente';
$active_page = 'clientes';
$active_subpage = 'registrar_cliente';

$mensaje = '';
$tipo_mensaje = '';
$errores = [];

// Función para validar email
function validarEmail($email) {
    if (empty($email)) return true; // Email es opcional
    
    // Validar formato básico
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "El formato del email no es válido";
    }
    
    // Validar dominios permitidos
    $dominios_permitidos = ['gmail.com', 'hotmail.com', 'hotmail.ar', 'yahoo.com', 'yahoo.com.ar', 'outlook.com', 'outlook.es'];
    $partes = explode('@', $email);
    if (count($partes) == 2) {
        $dominio = strtolower($partes[1]);
        if (!in_array($dominio, $dominios_permitidos)) {
            return "El dominio del email debe ser uno de los siguientes: " . implode(', ', $dominios_permitidos);
        }
    }
    
    return true;
}

// Función para validar nombre/apellido
function validarNombre($nombre, $campo = 'nombre') {
    if (empty($nombre)) {
        return "El $campo es obligatorio";
    }
    
    if (strlen($nombre) < 2) {
        return "El $campo debe tener al menos 2 caracteres";
    }
    
    if (strlen($nombre) > 100) {
        return "El $campo no puede tener más de 100 caracteres";
    }
    
    // Solo letras, espacios, tildes y caracteres especiales del español
    if (!preg_match("/^[a-záéíóúñüA-ZÁÉÍÓÚÑÜ\s]+$/u", $nombre)) {
        return "El $campo solo puede contener letras y espacios";
    }
    
    return true;
}

// Función para validar DNI argentino
function validarDNI($dni) {
    if (empty($dni)) {
        return "El DNI es obligatorio";
    }
    
    // Eliminar puntos y espacios
    $dni = preg_replace('/[.\s]/', '', $dni);
    
    // Solo números
    if (!preg_match('/^\d+$/', $dni)) {
        return "El DNI solo puede contener números";
    }
    
    // Longitud válida para DNI argentino (7 u 8 dígitos)
    if (strlen($dni) < 7 || strlen($dni) > 8) {
        return "El DNI debe tener 7 u 8 dígitos";
    }
    
    // Validar que no sean todos números iguales
    if (preg_match('/^(\d)\1+$/', $dni)) {
        return "El DNI no es válido";
    }
    
    return $dni; // Retornar DNI limpio
}

// Función para validar teléfono argentino
function validarTelefono($telefono) {
    if (empty($telefono)) return true; // Teléfono es opcional
    
    // Eliminar espacios, guiones y paréntesis
    $telefono_limpio = preg_replace('/[\s\-\(\)]/', '', $telefono);
    
    // Solo números y opcionalmente +
    if (!preg_match('/^[\+]?\d+$/', $telefono_limpio)) {
        return "El teléfono solo puede contener números, espacios, guiones y paréntesis";
    }
    
    // Longitud válida para teléfono argentino (10 dígitos sin código de país)
    $telefono_sin_codigo = preg_replace('/^\+?54/', '', $telefono_limpio);
    if (strlen($telefono_sin_codigo) < 10 || strlen($telefono_sin_codigo) > 13) {
        return "El teléfono debe tener 10 dígitos (ej: 3814567890)";
    }
    
    return $telefono_limpio;
}

// Función para validar ciudad
function validarCiudad($ciudad) {
    if (empty($ciudad)) return true; // Ciudad es opcional
    
    if (strlen($ciudad) > 100) {
        return "La ciudad no puede tener más de 100 caracteres";
    }
    
    // Solo letras, espacios y algunos caracteres especiales
    if (!preg_match("/^[a-záéíóúñüA-ZÁÉÍÓÚÑÜ\s\.\-]+$/u", $ciudad)) {
        return "La ciudad solo puede contener letras, espacios, puntos y guiones";
    }
    
    return true;
}

if ($_POST) {
    // Sanitizar datos
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $dni_raw = trim($_POST['dni']);
    $telefono_raw = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $ciudad = trim($_POST['ciudad'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
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
        $dni = $validacion_dni; // DNI válido y limpio
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
    
    // Si no hay errores de validación, proceder con la base de datos
    if (empty($errores)) {
        // Verificar DNI duplicado
        $stmt = $conn->prepare("SELECT id_cliente FROM clientes WHERE dni = ?");
        $stmt->bind_param("s", $dni);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errores[] = "Ya existe un cliente registrado con el DNI: $dni";
            $tipo_mensaje = "danger";
        } else {
            // Capitalizar nombre y apellido
            $nombre = ucwords(strtolower($nombre));
            $apellido = ucwords(strtolower($apellido));
            $ciudad = ucwords(strtolower($ciudad));
            
            // Insertar cliente
            $stmt = $conn->prepare("INSERT INTO clientes (nombre, apellido, dni, telefono, direccion, ciudad, email, estado, fecha_registro) VALUES (?, ?, ?, ?, ?, ?, ?, 'activo', NOW())");
            $stmt->bind_param("sssssss", $nombre, $apellido, $dni, $telefono, $direccion, $ciudad, $email);
            
            if ($stmt->execute()) {
                $mensaje = "¡Cliente registrado correctamente! Se generó el ID: " . $conn->insert_id;
                $tipo_mensaje = "success";
                // Limpiar campos después del registro exitoso
                $_POST = array();
            } else {
                $errores[] = "Error al registrar el cliente en la base de datos";
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
    <h1 class="h3 mb-0 text-gray-800">Registrar Nuevo Cliente</h1>
    <a href="listar_clientes.php" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm"></i> Volver al Listado
    </a>
</div>

<!-- Mensaje de respuesta -->
<?php if ($mensaje): ?>
<div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
    <strong>¡Éxito!</strong> <?php echo $mensaje; ?>
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

<!-- Formulario de Registro -->
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Datos del Cliente</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="" id="formCliente">
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
                                       pattern="[a-záéíóúñüA-ZÁÉÍÓÚÑÜ\s]+"
                                       title="Solo letras y espacios"
                                       required>
                                <small class="form-text text-muted">Solo letras, sin números ni símbolos</small>
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
                                       pattern="[a-záéíóúñüA-ZÁÉÍÓÚÑÜ\s]+"
                                       title="Solo letras y espacios"
                                       required>
                                <small class="form-text text-muted">Solo letras, sin números ni símbolos</small>
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
                                       placeholder="Ej: 12345678"
                                       value="<?php echo isset($_POST['dni']) ? htmlspecialchars($_POST['dni']) : ''; ?>"
                                       pattern="\d{7,8}"
                                       maxlength="8"
                                       title="7 u 8 dígitos sin puntos"
                                       required>
                                <small class="form-text text-muted">7 u 8 dígitos, sin puntos ni espacios</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="telefono">Teléfono</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="telefono" 
                                       name="telefono" 
                                       placeholder="Ej: 3814567890"
                                       value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>"
                                       pattern="[\d\s\-\(\)\+]{10,13}"
                                       title="10 dígitos mínimo">
                                <small class="form-text text-muted">Código de área + número (ej: 3814567890)</small>
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
                               value="<?php echo isset($_POST['direccion']) ? htmlspecialchars($_POST['direccion']) : ''; ?>"
                               maxlength="255">
                        <small class="form-text text-muted">Máximo 255 caracteres</small>
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
                                       value="<?php echo isset($_POST['ciudad']) ? htmlspecialchars($_POST['ciudad']) : ''; ?>"
                                       pattern="[a-záéíóúñüA-ZÁÉÍÓÚÑÜ\s\.\-]+"
                                       title="Solo letras, espacios, puntos y guiones"
                                       maxlength="100">
                                <small class="form-text text-muted">Solo letras</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       placeholder="ejemplo@gmail.com"
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                       pattern="[a-zA-Z0-9._%+-]+@(gmail|hotmail|yahoo|outlook)\.(com|com\.ar|ar|es)">
                                <small class="form-text text-muted">Dominios válidos: gmail.com, hotmail.com, yahoo.com, outlook.com</small>
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
        <div class="card shadow mb-4 border-left-primary">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-info-circle"></i> Información
                </h6>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Campos obligatorios (*):</strong></p>
                <ul class="pl-3">
                    <li>Nombre</li>
                    <li>Apellido</li>
                    <li>DNI</li>
                </ul>
                
                <hr>
                
                <h6 class="font-weight-bold">Validaciones aplicadas:</h6>
                
                <p class="mb-2"><strong>Nombre y Apellido:</strong></p>
                <ul class="small pl-3">
                    <li>Solo letras y espacios</li>
                    <li>Mínimo 2 caracteres</li>
                    <li>Sin números ni símbolos</li>
                </ul>
                
                <p class="mb-2"><strong>DNI:</strong></p>
                <ul class="small pl-3">
                    <li>Solo números</li>
                    <li>7 u 8 dígitos</li>
                    <li>Sin puntos ni espacios</li>
                    <li>Debe ser único</li>
                </ul>
                
                <p class="mb-2"><strong>Teléfono:</strong></p>
                <ul class="small pl-3">
                    <li>Formato argentino</li>
                    <li>10 dígitos mínimo</li>
                    <li>Código de área + número</li>
                </ul>
                
                <p class="mb-2"><strong>Email:</strong></p>
                <ul class="small pl-3 mb-0">
                    <li>Dominios permitidos:</li>
                    <li>gmail.com, hotmail.com</li>
                    <li>yahoo.com, outlook.com</li>
                    <li>Y sus variantes .ar/.es</li>
                </ul>
            </div>
        </div>
        
        <div class="card shadow mb-4 border-left-success">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-success">
                    <i class="fas fa-check-circle"></i> Ejemplos Válidos
                </h6>
            </div>
            <div class="card-body small">
                <p class="mb-1"><strong>Nombre:</strong> Juan Carlos</p>
                <p class="mb-1"><strong>Apellido:</strong> Pérez García</p>
                <p class="mb-1"><strong>DNI:</strong> 12345678</p>
                <p class="mb-1"><strong>Teléfono:</strong> 3814567890</p>
                <p class="mb-0"><strong>Email:</strong> juan@gmail.com</p>
            </div>
        </div>
    </div>
</div>

<?php
$extra_js = '
<script>
$(document).ready(function() {
    // Validación en tiempo real del DNI
    $("#dni").on("input", function() {
        var dni = $(this).val().replace(/[^\d]/g, "");
        $(this).val(dni);
        
        if (dni.length > 0 && dni.length < 7) {
            $(this).addClass("is-invalid");
        } else if (dni.length >= 7) {
            $(this).removeClass("is-invalid").addClass("is-valid");
        } else {
            $(this).removeClass("is-invalid is-valid");
        }
    });
    
    // Validación de nombre y apellido
    $("#nombre, #apellido").on("input", function() {
        var valor = $(this).val();
        var regex = /^[a-záéíóúñüA-ZÁÉÍÓÚÑÜ\s]*$/;
        
        if (!regex.test(valor)) {
            var valorLimpio = valor.replace(/[^a-záéíóúñüA-ZÁÉÍÓÚÑÜ\s]/g, "");
            $(this).val(valorLimpio);
        }
        
        if (valor.length >= 2 && regex.test(valor)) {
            $(this).removeClass("is-invalid").addClass("is-valid");
        } else if (valor.length > 0) {
            $(this).addClass("is-invalid");
        } else {
            $(this).removeClass("is-invalid is-valid");
        }
    });
    
    // Validación de email
    $("#email").on("blur", function() {
        var email = $(this).val();
        if (email.length === 0) {
            $(this).removeClass("is-invalid is-valid");
            return;
        }
        
        var dominiosValidos = ["gmail.com", "hotmail.com", "hotmail.ar", "yahoo.com", "yahoo.com.ar", "outlook.com", "outlook.es"];
        var partes = email.split("@");
        
        if (partes.length === 2) {
            var dominio = partes[1].toLowerCase();
            if (dominiosValidos.includes(dominio)) {
                $(this).removeClass("is-invalid").addClass("is-valid");
            } else {
                $(this).removeClass("is-valid").addClass("is-invalid");
            }
        } else {
            $(this).removeClass("is-valid").addClass("is-invalid");
        }
    });
    
    // Validación de teléfono
    $("#telefono").on("input", function() {
        var telefono = $(this).val().replace(/[^\d\s\-\(\)\+]/g, "");
        $(this).val(telefono);
        
        var digitos = telefono.replace(/[^\d]/g, "");
        if (digitos.length >= 10) {
            $(this).removeClass("is-invalid").addClass("is-valid");
        } else if (digitos.length > 0) {
            $(this).addClass("is-invalid");
        } else {
            $(this).removeClass("is-invalid is-valid");
        }
    });
    
    // Confirmación antes de enviar
    $("#formCliente").on("submit", function(e) {
        var nombre = $("#nombre").val();
        var apellido = $("#apellido").val();
        var dni = $("#dni").val();
        
        if (!confirm("¿Confirma el registro del cliente " + nombre + " " + apellido + " con DNI " + dni + "?")) {
            e.preventDefault();
            return false;
        }
    });
});
</script>
';

include '../includes/footer.php';
?>
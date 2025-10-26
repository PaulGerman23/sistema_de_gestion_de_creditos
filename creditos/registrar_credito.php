<?php
include '../conexion.php';
include '../clientes/funciones_validacion.php';

// Variables para el template
$base_url = '../';
$page_title = 'Registrar Crédito';
$active_page = 'creditos';
$active_subpage = 'registrar_credito';

$mensaje = '';
$tipo_mensaje = '';
$errores = [];

// Procesar registro de crédito
if ($_POST && isset($_POST['registrar_credito'])) {
    $id_cliente = $_POST['id_cliente'] ?? 0;
    $monto_total = $_POST['monto_total'] ?? '';
    $cantidad_cuotas = $_POST['cantidad_cuotas'] ?? '';
    $interes_anual = $_POST['interes_anual'] ?? 0;
    $descripcion = trim($_POST['descripcion'] ?? '');
    
    // Validar ID del cliente
    $validacion_id = validarID($id_cliente, 'cliente');
    if ($validacion_id !== true) {
        $errores[] = $validacion_id;
    } else {
        // Verificar que el cliente exista y esté activo
        $stmt = $conn->prepare("SELECT estado FROM clientes WHERE id_cliente = ?");
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
        $cliente = $stmt->get_result()->fetch_assoc();
        
        if (!$cliente) {
            $errores[] = "El cliente seleccionado no existe";
        } elseif ($cliente['estado'] != 'activo') {
            $errores[] = "No se puede registrar un crédito a un cliente con estado: " . $cliente['estado'];
        }
    }
    
    // Validar monto
    $validacion_monto = validarMonto($monto_total, 100, 10000000);
    if ($validacion_monto !== true) {
        $errores[] = $validacion_monto;
    }
    
    // Validar cantidad de cuotas
    $validacion_cuotas = validarCuotas($cantidad_cuotas, 1, 120);
    if ($validacion_cuotas !== true) {
        $errores[] = $validacion_cuotas;
    }
    
    // Validar interés
    $validacion_interes = validarInteres($interes_anual);
    if ($validacion_interes !== true) {
        $errores[] = $validacion_interes;
    }
    
    // Validar descripción
    $validacion_desc = validarDescripcion($descripcion);
    if ($validacion_desc !== true) {
        $errores[] = $validacion_desc;
    }
    
    // Si no hay errores, proceder a registrar
    if (empty($errores)) {
        // Limpiar y convertir valores
        $monto_total = floatval(str_replace(',', '', $monto_total));
        $cantidad_cuotas = intval($cantidad_cuotas);
        $interes_anual = floatval($interes_anual);
        
        // Calcular cuota mensual con interés
        if ($interes_anual > 0) {
            $interes_mensual = $interes_anual / 12 / 100;
            $cuota_mensual = $monto_total * ($interes_mensual * pow(1 + $interes_mensual, $cantidad_cuotas)) / (pow(1 + $interes_mensual, $cantidad_cuotas) - 1);
        } else {
            $cuota_mensual = $monto_total / $cantidad_cuotas;
        }
        
        $fecha_inicio = date('Y-m-d');
        $fecha_vencimiento = date('Y-m-d', strtotime("+{$cantidad_cuotas} months"));
        
        // Iniciar transacción
        $conn->begin_transaction();
        
        try {
            // Insertar crédito
            $stmt = $conn->prepare("INSERT INTO creditos (id_cliente, monto_total, cantidad_cuotas, cuota_mensual, interes_anual, fecha_inicio, fecha_vencimiento, descripcion, estado, fecha_creacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'activo', NOW())");
            $stmt->bind_param("ididdsss", $id_cliente, $monto_total, $cantidad_cuotas, $cuota_mensual, $interes_anual, $fecha_inicio, $fecha_vencimiento, $descripcion);
            
            if (!$stmt->execute()) {
                throw new Exception("Error al registrar el crédito");
            }
            
            $id_credito = $conn->insert_id;
            
            // Generar cuotas
            for ($i = 1; $i <= $cantidad_cuotas; $i++) {
                $fecha_cuota = date('Y-m-d', strtotime("+{$i} months", strtotime($fecha_inicio)));
                
                $stmt2 = $conn->prepare("INSERT INTO cuotas (id_credito, numero_cuota, monto_cuota, fecha_vencimiento, estado) VALUES (?, ?, ?, ?, 'pendiente')");
                $stmt2->bind_param("iids", $id_credito, $i, $cuota_mensual, $fecha_cuota);
                
                if (!$stmt2->execute()) {
                    throw new Exception("Error al generar las cuotas");
                }
            }
            
            // Confirmar transacción
            $conn->commit();
            
            $mensaje = "¡Crédito registrado correctamente! ID del crédito: $id_credito. Se generaron $cantidad_cuotas cuotas automáticamente.";
            $tipo_mensaje = "success";
            
            // Limpiar POST
            $_POST = array();
            
        } catch (Exception $e) {
            $conn->rollback();
            $errores[] = "Error en la transacción: " . $e->getMessage();
            $tipo_mensaje = "danger";
        }
    } else {
        $tipo_mensaje = "danger";
    }
}

// Cliente seleccionado desde búsqueda
$cliente_seleccionado = null;
if (isset($_GET['id_cliente'])) {
    $id_cliente_get = intval($_GET['id_cliente']);
    
    // Validar que sea un número positivo
    if ($id_cliente_get > 0) {
        $stmt = $conn->prepare("SELECT * FROM clientes WHERE id_cliente = ? AND estado = 'activo'");
        $stmt->bind_param("i", $id_cliente_get);
        $stmt->execute();
        $cliente_seleccionado = $stmt->get_result()->fetch_assoc();
        
        if (!$cliente_seleccionado) {
            $errores[] = "El cliente seleccionado no existe o no está activo";
        }
    }
}

include '../includes/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Registrar Nuevo Crédito</h1>
    <a href="ver_creditos.php" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm"></i> Volver al Listado
    </a>
</div>

<!-- Mensajes -->
<?php if ($mensaje): ?>
<div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
    <strong>¡Éxito!</strong> <?php echo $mensaje; ?>
    <?php if ($tipo_mensaje == 'success' && isset($id_credito)): ?>
        <br><br>
        <a href="../cuotas/generar_plan_pago.php?id_credito=<?php echo $id_credito; ?>" class="btn btn-sm btn-info">
            <i class="fas fa-eye"></i> Ver Plan de Pago
        </a>
        <a href="detalle_credito.php?id=<?php echo $id_credito; ?>" class="btn btn-sm btn-primary">
            <i class="fas fa-file-invoice-dollar"></i> Ver Detalle del Crédito
        </a>
    <?php endif; ?>
    <button type="button" class="close" data-dismiss="alert">
        <span>&times;</span>
    </button>
</div>
<?php endif; ?>

<?php if (!empty($errores)): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <h5><i class="fas fa-exclamation-triangle"></i> Errores de validación:</h5>
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

<div class="row">
    <!-- Formulario Principal -->
    <div class="col-lg-8">
        
        <!-- Paso 1: Seleccionar Cliente -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-primary text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-user"></i> Paso 1: Seleccionar Cliente
                </h6>
            </div>
            <div class="card-body">
                <?php if (!$cliente_seleccionado): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    Busque y seleccione un cliente <strong>ACTIVO</strong> para asignarle el crédito
                </div>
                
                <form method="GET" action="" id="formBuscarCliente">
                    <div class="form-group">
                        <label for="buscar_cliente">Buscar Cliente:</label>
                        <input type="text" 
                               class="form-control" 
                               id="buscar_cliente" 
                               placeholder="Ingrese nombre, apellido o DNI del cliente..."
                               autocomplete="off"
                               minlength="2">
                        <small class="form-text text-muted">Mínimo 2 caracteres para buscar</small>
                    </div>
                    <div id="resultados_busqueda"></div>
                </form>
                
                <?php else: ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> 
                    Cliente seleccionado correctamente
                </div>
                
                <div class="card border-left-success">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Nombre:</strong> <?php echo htmlspecialchars($cliente_seleccionado['nombre'] . ' ' . $cliente_seleccionado['apellido']); ?></p>
                                <p class="mb-1"><strong>DNI:</strong> <?php echo htmlspecialchars($cliente_seleccionado['dni']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Teléfono:</strong> <?php echo htmlspecialchars($cliente_seleccionado['telefono'] ?: 'No registrado'); ?></p>
                                <p class="mb-1"><strong>Ciudad:</strong> <?php echo htmlspecialchars($cliente_seleccionado['ciudad'] ?: 'No registrada'); ?></p>
                            </div>
                        </div>
                        <div class="mt-2">
                            <a href="registrar_credito.php" class="btn btn-sm btn-warning">
                                <i class="fas fa-redo"></i> Cambiar Cliente
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Paso 2: Datos del Crédito -->
        <?php if ($cliente_seleccionado): ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-success text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-credit-card"></i> Paso 2: Datos del Crédito
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="" id="formCredito">
                    <input type="hidden" name="id_cliente" value="<?php echo $cliente_seleccionado['id_cliente']; ?>">
                    <input type="hidden" name="registrar_credito" value="1">
                    
                    <div class="form-group">
                        <label for="descripcion">Descripción del Crédito <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               id="descripcion" 
                               name="descripcion" 
                               placeholder="Ej: Crédito personal, Financiación de producto..."
                               minlength="5"
                               maxlength="255"
                               required>
                        <small class="form-text text-muted">Entre 5 y 255 caracteres</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="monto_total">Monto Total <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" 
                                           class="form-control" 
                                           id="monto_total" 
                                           name="monto_total" 
                                           placeholder="0.00" 
                                           step="0.01"
                                           min="100"
                                           max="10000000"
                                           required>
                                </div>
                                <small class="form-text text-muted">Mínimo: $100 - Máximo: $10,000,000</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cantidad_cuotas">Cantidad de Cuotas <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control" 
                                       id="cantidad_cuotas" 
                                       name="cantidad_cuotas" 
                                       placeholder="12" 
                                       min="1"
                                       max="120"
                                       required>
                                <small class="form-text text-muted">Entre 1 y 120 cuotas</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="interes_anual">Interés Anual (%)</label>
                        <div class="input-group">
                            <input type="number" 
                                   class="form-control" 
                                   id="interes_anual" 
                                   name="interes_anual" 
                                   placeholder="0.00" 
                                   step="0.01"
                                   min="0"
                                   max="100"
                                   value="0">
                            <div class="input-group-append">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <small class="form-text text-muted">Dejar en 0 para crédito sin interés. Máximo 100%</small>
                    </div>

                    <hr>

                    <!-- Simulador -->
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <h6 class="font-weight-bold text-primary mb-3">
                                <i class="fas fa-calculator"></i> Simulador de Cuota
                            </h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <p class="mb-2"><strong>Cuota Mensual:</strong></p>
                                    <h4 class="text-success" id="cuota_estimada">$0.00</h4>
                                </div>
                                <div class="col-md-4">
                                    <p class="mb-2"><strong>Total a Pagar:</strong></p>
                                    <h4 class="text-info" id="total_pagar">$0.00</h4>
                                </div>
                                <div class="col-md-4">
                                    <p class="mb-2"><strong>Total Intereses:</strong></p>
                                    <h4 class="text-warning" id="total_intereses">$0.00</h4>
                                </div>
                            </div>
                            <small class="text-muted">* Los valores se actualizan automáticamente</small>
                        </div>
                    </div>

                    <hr>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="confirmar_datos" required>
                        <label class="form-check-label" for="confirmar_datos">
                            <strong>Confirmo que los datos ingresados son correctos</strong>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-success btn-icon-split btn-lg">
                        <span class="icon text-white-50">
                            <i class="fas fa-check"></i>
                        </span>
                        <span class="text">Registrar Crédito</span>
                    </button>
                    <a href="ver_creditos.php" class="btn btn-secondary btn-icon-split btn-lg">
                        <span class="icon text-white-50">
                            <i class="fas fa-times"></i>
                        </span>
                        <span class="text">Cancelar</span>
                    </a>
                </form>
            </div>
        </div>
        <?php endif; ?>
        
    </div>
    
    <!-- Sidebar -->
    <div class="col-lg-4">
        <div class="card shadow mb-4 border-left-primary">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-info-circle"></i> Validaciones
                </h6>
            </div>
            <div class="card-body">
                <h6 class="font-weight-bold mb-2">Cliente:</h6>
                <ul class="small pl-3 mb-3">
                    <li>Debe estar en estado <strong>ACTIVO</strong></li>
                    <li>Debe existir en el sistema</li>
                </ul>
                
                <h6 class="font-weight-bold mb-2">Monto:</h6>
                <ul class="small pl-3 mb-3">
                    <li>Mínimo: $100</li>
                    <li>Máximo: $10,000,000</li>
                    <li>Debe ser un número positivo</li>
                </ul>
                
                <h6 class="font-weight-bold mb-2">Cuotas:</h6>
                <ul class="small pl-3 mb-3">
                    <li>Mínimo: 1 cuota</li>
                    <li>Máximo: 120 cuotas (10 años)</li>
                </ul>
                
                <h6 class="font-weight-bold mb-2">Interés:</h6>
                <ul class="small pl-3 mb-0">
                    <li>Puede ser 0% (sin interés)</li>
                    <li>Máximo: 100% anual</li>
                    <li>Se calcula cuota francesa</li>
                </ul>
            </div>
        </div>

        <div class="card shadow mb-4 border-left-info">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-info">
                    <i class="fas fa-lightbulb"></i> Ejemplo
                </h6>
            </div>
            <div class="card-body small">
                <p class="mb-2"><strong>Monto:</strong> $100,000</p>
                <p class="mb-2"><strong>Cuotas:</strong> 12</p>
                <p class="mb-3"><strong>Interés:</strong> 10% anual</p>
                <hr>
                <p class="mb-2"><strong>Cuota mensual:</strong> $8,791.59</p>
                <p class="mb-2"><strong>Total a pagar:</strong> $105,499.08</p>
                <p class="mb-0"><strong>Intereses:</strong> $5,499.08</p>
            </div>
        </div>
    </div>
</div>

<?php
$extra_js = '
<script>
$(document).ready(function() {
    // Búsqueda AJAX de clientes
    $("#buscar_cliente").on("keyup", function() {
        var busqueda = $(this).val();
        
        if (busqueda.length >= 2) {
            $.ajax({
                url: "buscar_cliente_ajax.php",
                method: "GET",
                data: { q: busqueda },
                success: function(data) {
                    $("#resultados_busqueda").html(data);
                },
                error: function() {
                    $("#resultados_busqueda").html("<div class=\'alert alert-danger\'>Error al buscar clientes</div>");
                }
            });
        } else {
            $("#resultados_busqueda").html("");
        }
    });
    
    // Validar monto
    $("#monto_total").on("input", function() {
        var monto = parseFloat($(this).val());
        if (monto < 100) {
            $(this).addClass("is-invalid");
        } else if (monto > 10000000) {
            $(this).addClass("is-invalid");
        } else {
            $(this).removeClass("is-invalid").addClass("is-valid");
        }
        calcularCuota();
    });
    
    // Validar cuotas
    $("#cantidad_cuotas").on("input", function() {
        var cuotas = parseInt($(this).val());
        if (cuotas < 1 || cuotas > 120) {
            $(this).addClass("is-invalid");
        } else {
            $(this).removeClass("is-invalid").addClass("is-valid");
        }
        calcularCuota();
    });
    
    // Validar interés
    $("#interes_anual").on("input", function() {
        var interes = parseFloat($(this).val());
        if (interes < 0 || interes > 100) {
            $(this).addClass("is-invalid");
        } else {
            $(this).removeClass("is-invalid").addClass("is-valid");
        }
        calcularCuota();
    });
    
    // Calcular cuota
    function calcularCuota() {
        var monto = parseFloat($("#monto_total").val()) || 0;
        var cuotas = parseInt($("#cantidad_cuotas").val()) || 1;
        var interes = parseFloat($("#interes_anual").val()) || 0;
        
        var cuotaMensual = 0;
        var totalPagar = 0;
        var totalIntereses = 0;
        
        if (monto > 0 && cuotas > 0) {
            if (interes > 0) {
                var interesMensual = (interes / 12) / 100;
                cuotaMensual = monto * (interesMensual * Math.pow(1 + interesMensual, cuotas)) / (Math.pow(1 + interesMensual, cuotas) - 1);
                totalPagar = cuotaMensual * cuotas;
                totalIntereses = totalPagar - monto;
            } else {
                cuotaMensual = monto / cuotas;
                totalPagar = monto;
                totalIntereses = 0;
            }
        }
        
        // Formatear números con separador de miles
        $("#cuota_estimada").text("$" + cuotaMensual.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, "$&,"));
        $("#total_pagar").text("$" + totalPagar.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, "$&,"));
        $("#total_intereses").text("$" + totalIntereses.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, "$&,"));
    }
    
    // Vincular eventos
    $("#monto_total, #cantidad_cuotas, #interes_anual").on("input change", calcularCuota);
    
    // Validar formulario
    $("#formCredito").on("submit", function(e) {
        if (!$("#confirmar_datos").is(":checked")) {
            e.preventDefault();
            alert("Debe confirmar que los datos son correctos");
            return false;
        }
        
        var monto = parseFloat($("#monto_total").val());
        var cuotas = parseInt($("#cantidad_cuotas").val());
        
        if (monto < 100 || monto > 10000000) {
            e.preventDefault();
            alert("El monto debe estar entre $100 y $10,000,000");
            return false;
        }
        
        if (cuotas < 1 || cuotas > 120) {
            e.preventDefault();
            alert("Las cuotas deben estar entre 1 y 120");
            return false;
        }
        
        if (!confirm("¿Confirma el registro del crédito por $" + monto.toFixed(2) + " en " + cuotas + " cuotas?")) {
            e.preventDefault();
            return false;
        }
    });
    
    // Calcular al cargar
    calcularCuota();
});
</script>
';

include '../includes/footer.php';
?>
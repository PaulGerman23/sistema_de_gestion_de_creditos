<?php
include '../conexion.php';

// Variables para el template
$base_url = '../';
$page_title = 'Registrar Crédito';
$active_page = 'creditos';
$active_subpage = 'registrar_credito';

$mensaje = '';
$tipo_mensaje = '';

if ($_POST) {
    $id_cliente = $_POST['id_cliente'];
    $monto_total = $_POST['monto_total'];
    $cantidad_cuotas = $_POST['cantidad_cuotas'];
    $interes_anual = $_POST['interes_anual'] ?? 0;
    $descripcion = $_POST['descripcion'];

    // Calcular cuota mensual con interés
    if ($interes_anual > 0) {
        $interes_mensual = $interes_anual / 12 / 100;
        $cuota_mensual = $monto_total * ($interes_mensual * pow(1 + $interes_mensual, $cantidad_cuotas)) / (pow(1 + $interes_mensual, $cantidad_cuotas) - 1);
    } else {
        $cuota_mensual = $monto_total / $cantidad_cuotas;
    }
    
    $fecha_inicio = date('Y-m-d');
    $fecha_vencimiento = date('Y-m-d', strtotime("+{$cantidad_cuotas} months"));

    $stmt = $conn->prepare("INSERT INTO creditos (id_cliente, monto_total, cantidad_cuotas, cuota_mensual, interes_anual, fecha_inicio, fecha_vencimiento, descripcion, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'activo')");
    $stmt->bind_param("ididdsss", $id_cliente, $monto_total, $cantidad_cuotas, $cuota_mensual, $interes_anual, $fecha_inicio, $fecha_vencimiento, $descripcion);

    if ($stmt->execute()) {
        $id_credito = $conn->insert_id;

        // Generar cuotas
        for ($i = 1; $i <= $cantidad_cuotas; $i++) {
            $fecha_cuota = date('Y-m-d', strtotime("+{$i} months", strtotime($fecha_inicio)));
            $stmt2 = $conn->prepare("INSERT INTO cuotas (id_credito, numero_cuota, monto_cuota, fecha_vencimiento, estado) VALUES (?, ?, ?, ?, 'pendiente')");
            $stmt2->bind_param("iids", $id_credito, $i, $cuota_mensual, $fecha_cuota);
            $stmt2->execute();
        }

        $mensaje = "¡Crédito y cuotas registrados correctamente! ID del crédito: $id_credito";
        $tipo_mensaje = "success";
        $_POST = array();
    } else {
        $mensaje = "Error al registrar el crédito: " . $stmt->error;
        $tipo_mensaje = "danger";
    }
}

// Listar clientes activos para seleccionar
$clientes = $conn->query("SELECT id_cliente, nombre, apellido, dni FROM clientes WHERE estado = 'activo' ORDER BY nombre, apellido");

include '../includes/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Registrar Nuevo Crédito</h1>
    <a href="ver_creditos.php" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver al Listado
    </a>
</div>

<!-- Mensaje de respuesta -->
<?php if ($mensaje): ?>
<div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
    <strong><?php echo $tipo_mensaje == 'success' ? '¡Éxito!' : '¡Error!'; ?></strong> <?php echo $mensaje; ?>
    <?php if ($tipo_mensaje == 'success' && isset($id_credito)): ?>
        <br><a href="../cuotas/generar_plan_pago.php?id_credito=<?php echo $id_credito; ?>" class="alert-link">Ver plan de pago generado</a>
    <?php endif; ?>
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
                <h6 class="m-0 font-weight-bold text-primary">Datos del Crédito</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="" id="formCredito">
                    
                    <!-- Selección de Cliente -->
                    <div class="form-group">
                        <label for="id_cliente">Cliente <span class="text-danger">*</span></label>
                        <select class="form-control" id="id_cliente" name="id_cliente" required>
                            <option value="">Seleccionar cliente...</option>
                            <?php while ($c = $clientes->fetch_assoc()): ?>
                                <option value="<?php echo $c['id_cliente']; ?>" 
                                        <?php echo (isset($_POST['id_cliente']) && $_POST['id_cliente'] == $c['id_cliente']) ? 'selected' : ''; ?>>
                                    <?php echo $c['nombre'] . ' ' . $c['apellido'] . ' - DNI: ' . $c['dni']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <small class="form-text text-muted">Solo se muestran clientes con estado "Activo"</small>
                    </div>

                    <hr>

                    <!-- Descripción -->
                    <div class="form-group">
                        <label for="descripcion">Descripción del Crédito <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               id="descripcion" 
                               name="descripcion" 
                               placeholder="Ej: Crédito personal, Financiación de producto, etc."
                               value="<?php echo isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : ''; ?>"
                               required>
                    </div>

                    <!-- Monto Total y Cantidad de Cuotas -->
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
                                           min="0"
                                           value="<?php echo isset($_POST['monto_total']) ? $_POST['monto_total'] : ''; ?>"
                                           required>
                                </div>
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
                                       value="<?php echo isset($_POST['cantidad_cuotas']) ? $_POST['cantidad_cuotas'] : ''; ?>"
                                       required>
                                <small class="form-text text-muted">Máximo 120 cuotas (10 años)</small>
                            </div>
                        </div>
                    </div>

                    <!-- Interés Anual -->
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
                                   value="<?php echo isset($_POST['interes_anual']) ? $_POST['interes_anual'] : '0'; ?>">
                            <div class="input-group-append">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <small class="form-text text-muted">Dejar en 0 para crédito sin interés</small>
                    </div>

                    <hr>

                    <!-- Simulador de Cuota -->
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <h6 class="font-weight-bold text-primary mb-3">
                                <i class="fas fa-calculator"></i> Simulador de Cuota
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-2"><strong>Cuota Mensual Estimada:</strong></p>
                                    <h4 class="text-success" id="cuota_estimada">$0.00</h4>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-2"><strong>Total a Pagar:</strong></p>
                                    <h4 class="text-info" id="total_pagar">$0.00</h4>
                                </div>
                            </div>
                            <small class="text-muted">* Los valores se actualizan automáticamente al cambiar los datos</small>
                        </div>
                    </div>

                    <hr>

                    <!-- Botones -->
                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary btn-icon-split">
                            <span class="icon text-white-50">
                                <i class="fas fa-check"></i>
                            </span>
                            <span class="text">Registrar Crédito</span>
                        </button>
                        <a href="ver_creditos.php" class="btn btn-secondary btn-icon-split">
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
        <!-- Información del Crédito -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Información</h6>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Campos obligatorios (*)</strong></p>
                <ul class="pl-3 mb-3">
                    <li>Cliente</li>
                    <li>Descripción</li>
                    <li>Monto total</li>
                    <li>Cantidad de cuotas</li>
                </ul>
                
                <hr>
                
                <p class="mb-2"><strong>Cálculo de Cuotas:</strong></p>
                <p class="small text-muted mb-3">
                    Si especifica un interés anual, se aplicará la fórmula de cuota francesa. 
                    Si es 0%, se divide el monto en partes iguales.
                </p>
                
                <hr>
                
                <p class="mb-2"><strong>Generación Automática:</strong></p>
                <p class="small text-muted mb-0">
                    Al registrar el crédito, se generarán automáticamente todas las cuotas con 
                    sus fechas de vencimiento correspondientes (una por mes).
                </p>
            </div>
        </div>

        <!-- Ejemplo de Interés -->
        <div class="card shadow mb-4 border-left-info">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-info">
                    <i class="fas fa-info-circle"></i> Ejemplo
                </h6>
            </div>
            <div class="card-body">
                <p class="small mb-2"><strong>Monto:</strong> $100,000</p>
                <p class="small mb-2"><strong>Cuotas:</strong> 12</p>
                <p class="small mb-3"><strong>Interés:</strong> 10% anual</p>
                <hr>
                <p class="small mb-2"><strong>Cuota mensual:</strong> $8,791.59</p>
                <p class="small mb-0"><strong>Total a pagar:</strong> $105,499.08</p>
            </div>
        </div>
    </div>
</div>

<?php
$extra_js = '
<script>
    // Función para calcular la cuota estimada
    function calcularCuota() {
        var monto = parseFloat($("#monto_total").val()) || 0;
        var cuotas = parseInt($("#cantidad_cuotas").val()) || 1;
        var interes = parseFloat($("#interes_anual").val()) || 0;
        
        var cuotaMensual = 0;
        var totalPagar = 0;
        
        if (interes > 0) {
            var interesMensual = (interes / 12) / 100;
            cuotaMensual = monto * (interesMensual * Math.pow(1 + interesMensual, cuotas)) / (Math.pow(1 + interesMensual, cuotas) - 1);
            totalPagar = cuotaMensual * cuotas;
        } else {
            cuotaMensual = monto / cuotas;
            totalPagar = monto;
        }
        
        $("#cuota_estimada").text("$" + cuotaMensual.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, "$&,"));
        $("#total_pagar").text("$" + totalPagar.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, "$&,"));
    }
    
    // Calcular al cambiar cualquier campo
    $(document).ready(function() {
        $("#monto_total, #cantidad_cuotas, #interes_anual").on("input change", calcularCuota);
        calcularCuota(); // Calcular inicial
    });
</script>
';

include '../includes/footer.php';
?>
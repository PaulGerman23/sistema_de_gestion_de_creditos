<?php
include '../conexion.php';

// Variables para el template
$base_url = '../';
$page_title = 'Registrar Pago de Cuota';
$active_page = 'cuotas';
$active_subpage = 'pagar_cuota';

$mensaje = '';
$tipo_mensaje = '';

$id_cuota_param = $_GET['id_cuota'] ?? 0;
$id_cliente_param = $_GET['id_cliente'] ?? 0;

// Procesar el pago
if ($_POST) {
    $id_cuota = $_POST['id_cuota'];
    $monto_pagado = $_POST['monto_pagado'];
    $metodo_pago = $_POST['metodo_pago'];
    $observaciones = $_POST['observaciones'] ?? '';

    // Obtener información de la cuota
    $stmt = $conn->prepare("SELECT monto_cuota, id_credito FROM cuotas WHERE id_cuota = ?");
    $stmt->bind_param("i", $id_cuota);
    $stmt->execute();
    $cuota_info = $stmt->get_result()->fetch_assoc();

    if ($cuota_info) {
        // Registrar el pago
        $stmt = $conn->prepare("INSERT INTO pagos (id_cuota, monto_pagado, fecha_pago, metodo_pago, observaciones) VALUES (?, ?, NOW(), ?, ?)");
        $stmt->bind_param("idss", $id_cuota, $monto_pagado, $metodo_pago, $observaciones);
        
        if ($stmt->execute()) {
            // Actualizar estado de la cuota
            $stmt2 = $conn->prepare("UPDATE cuotas SET estado = 'pagada', fecha_pago = NOW() WHERE id_cuota = ?");
            $stmt2->bind_param("i", $id_cuota);
            $stmt2->execute();

            // Obtener el ID del crédito para actualizar su estado
            $id_credito = $cuota_info['id_credito'];

            // Actualizar estado del crédito
            include '../creditos/actualizar_estado_credito.php';
            actualizarEstadoCredito($id_credito, $conn);

            $mensaje = "¡Pago registrado exitosamente! El estado del crédito ha sido actualizado.";
            $tipo_mensaje = "success";
            
            // Limpiar el formulario
            $id_cuota_param = 0;
        } else {
            $mensaje = "Error al registrar el pago: " . $stmt->error;
            $tipo_mensaje = "danger";
        }
    } else {
        $mensaje = "La cuota seleccionada no existe.";
        $tipo_mensaje = "danger";
    }
}

// Obtener cuotas pendientes o vencidas
if ($id_cliente_param) {
    // Cuotas de un cliente específico
    $cuotas_query = "SELECT cu.id_cuota, cu.numero_cuota, cu.monto_cuota, cu.fecha_vencimiento, 
                            cr.id_credito, cr.descripcion, c.nombre, c.apellido
                     FROM cuotas cu
                     JOIN creditos cr ON cu.id_credito = cr.id_credito
                     JOIN clientes c ON cr.id_cliente = c.id_cliente
                     WHERE cr.id_cliente = ? AND cu.estado IN ('pendiente', 'vencida')
                     ORDER BY cu.fecha_vencimiento ASC";
    $stmt = $conn->prepare($cuotas_query);
    $stmt->bind_param("i", $id_cliente_param);
} else {
    // Todas las cuotas pendientes
    $cuotas_query = "SELECT cu.id_cuota, cu.numero_cuota, cu.monto_cuota, cu.fecha_vencimiento, cu.estado,
                            cr.id_credito, cr.descripcion, c.id_cliente, c.nombre, c.apellido
                     FROM cuotas cu
                     JOIN creditos cr ON cu.id_credito = cr.id_credito
                     JOIN clientes c ON cr.id_cliente = c.id_cliente
                     WHERE cu.estado IN ('pendiente', 'vencida')
                     ORDER BY cu.fecha_vencimiento ASC";
    $stmt = $conn->prepare($cuotas_query);
}

$stmt->execute();
$cuotas = $stmt->get_result();

// Si hay una cuota específica seleccionada, obtener sus datos
$cuota_seleccionada = null;
if ($id_cuota_param) {
    $stmt = $conn->prepare("SELECT cu.*, cr.descripcion as descripcion_credito, c.nombre, c.apellido, c.dni
                            FROM cuotas cu
                            JOIN creditos cr ON cu.id_credito = cr.id_credito
                            JOIN clientes c ON cr.id_cliente = c.id_cliente
                            WHERE cu.id_cuota = ?");
    $stmt->bind_param("i", $id_cuota_param);
    $stmt->execute();
    $cuota_seleccionada = $stmt->get_result()->fetch_assoc();
}

include '../includes/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Registrar Pago de Cuota</h1>
    <a href="ver_cuotas_cliente.php" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm"></i> Volver
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

<div class="row">
    <!-- Formulario de Pago -->
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Datos del Pago</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="" id="formPago">
                    
                    <!-- Selección de Cuota -->
                    <div class="form-group">
                        <label for="id_cuota">Seleccionar Cuota a Pagar <span class="text-danger">*</span></label>
                        <select class="form-control" id="id_cuota" name="id_cuota" required onchange="actualizarMonto()">
                            <option value="">-- Seleccione una cuota --</option>
                            <?php while ($c = $cuotas->fetch_assoc()): 
                                $fecha_venc = new DateTime($c['fecha_vencimiento']);
                                $hoy = new DateTime();
                                $vencida = ($fecha_venc < $hoy && $c['estado'] != 'pagada');
                                $class_estado = $vencida ? 'text-danger' : '';
                            ?>
                                <option value="<?php echo $c['id_cuota']; ?>" 
                                        data-monto="<?php echo $c['monto_cuota']; ?>"
                                        data-cliente="<?php echo $c['nombre'] . ' ' . $c['apellido']; ?>"
                                        data-credito="<?php echo $c['descripcion']; ?>"
                                        <?php echo ($id_cuota_param == $c['id_cuota']) ? 'selected' : ''; ?>>
                                    <?php echo $c['nombre'] . ' ' . $c['apellido']; ?> - 
                                    <?php echo $c['descripcion']; ?> - 
                                    Cuota <?php echo $c['numero_cuota']; ?> - 
                                    $<?php echo number_format($c['monto_cuota'], 2); ?> - 
                                    Vence: <?php echo date('d/m/Y', strtotime($c['fecha_vencimiento'])); ?>
                                    <?php echo $vencida ? ' (VENCIDA)' : ''; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <small class="form-text text-muted">Solo se muestran cuotas pendientes o vencidas</small>
                    </div>

                    <!-- Información de la cuota seleccionada -->
                    <div id="info_cuota" class="alert alert-info" style="display: none;">
                        <h6 class="font-weight-bold"><i class="fas fa-info-circle"></i> Información de la Cuota</h6>
                        <p class="mb-1"><strong>Cliente:</strong> <span id="info_cliente"></span></p>
                        <p class="mb-1"><strong>Crédito:</strong> <span id="info_credito"></span></p>
                        <p class="mb-0"><strong>Monto:</strong> $<span id="info_monto"></span></p>
                    </div>

                    <hr>

                    <!-- Monto del Pago -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="monto_pagado">Monto a Pagar <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" 
                                           class="form-control" 
                                           id="monto_pagado" 
                                           name="monto_pagado" 
                                           placeholder="0.00" 
                                           step="0.01"
                                           min="0"
                                           required>
                                </div>
                                <small class="form-text text-muted">Ingrese el monto exacto de la cuota</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="metodo_pago">Método de Pago <span class="text-danger">*</span></label>
                                <select class="form-control" id="metodo_pago" name="metodo_pago" required>
                                    <option value="efectivo">Efectivo</option>
                                    <option value="transferencia">Transferencia Bancaria</option>
                                    <option value="tarjeta">Tarjeta de Crédito/Débito</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div class="form-group">
                        <label for="observaciones">Observaciones</label>
                        <textarea class="form-control" 
                                  id="observaciones" 
                                  name="observaciones" 
                                  rows="3" 
                                  placeholder="Ingrese cualquier observación sobre el pago (opcional)"></textarea>
                        <small class="form-text text-muted">Ej: Número de transferencia, recibo, etc.</small>
                    </div>

                    <hr>

                    <!-- Confirmación -->
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <h6 class="font-weight-bold text-primary mb-3">
                                <i class="fas fa-check-circle"></i> Confirmación del Pago
                            </h6>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="confirmar_pago" required>
                                <label class="form-check-label" for="confirmar_pago">
                                    Confirmo que he recibido el pago y los datos son correctos
                                </label>
                            </div>
                            <p class="small text-muted mb-0">
                                <i class="fas fa-exclamation-triangle text-warning"></i> 
                                Al confirmar, la cuota será marcada como "pagada" y se actualizará el estado del crédito automáticamente.
                            </p>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-success btn-icon-split btn-lg">
                            <span class="icon text-white-50">
                                <i class="fas fa-check"></i>
                            </span>
                            <span class="text">Registrar Pago</span>
                        </button>
                        <a href="ver_cuotas_cliente.php" class="btn btn-secondary btn-icon-split btn-lg">
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
        <!-- Información -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-info-circle"></i> Información
                </h6>
            </div>
            <div class="card-body">
                <h6 class="font-weight-bold mb-2">Pasos para registrar un pago:</h6>
                <ol class="pl-3 mb-3 small">
                    <li>Seleccione la cuota a pagar</li>
                    <li>Verifique el monto</li>
                    <li>Seleccione el método de pago</li>
                    <li>Agregue observaciones si es necesario</li>
                    <li>Confirme y registre el pago</li>
                </ol>
                
                <hr>
                
                <h6 class="font-weight-bold mb-2">Importante:</h6>
                <ul class="pl-3 mb-0 small">
                    <li>Una vez registrado, el pago no puede ser eliminado</li>
                    <li>La cuota se marcará como pagada inmediatamente</li>
                    <li>El estado del crédito se actualizará automáticamente</li>
                </ul>
            </div>
        </div>

        <!-- Cuotas Vencidas -->
        <?php 
        $cuotas_vencidas_count = $conn->query("SELECT COUNT(*) as total FROM cuotas WHERE estado = 'vencida'")->fetch_assoc()['total'];
        if ($cuotas_vencidas_count > 0):
        ?>
        <div class="card shadow mb-4 border-left-danger">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-danger">
                    <i class="fas fa-exclamation-triangle"></i> Cuotas Vencidas
                </h6>
            </div>
            <div class="card-body">
                <p class="mb-2">Actualmente hay:</p>
                <h3 class="text-danger mb-3"><?php echo $cuotas_vencidas_count; ?> cuota(s) vencida(s)</h3>
                <p class="small text-muted mb-0">
                    Se recomienda priorizar el pago de las cuotas vencidas para evitar intereses moratorios adicionales.
                </p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Métodos de Pago -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-info">
                    <i class="fas fa-credit-card"></i> Métodos de Pago
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <i class="fas fa-money-bill-wave text-success"></i>
                    <strong>Efectivo:</strong> Pago en el momento
                </div>
                <div class="mb-2">
                    <i class="fas fa-exchange-alt text-primary"></i>
                    <strong>Transferencia:</strong> Requiere comprobante
                </div>
                <div class="mb-2">
                    <i class="fas fa-credit-card text-info"></i>
                    <strong>Tarjeta:</strong> Débito o crédito
                </div>
                <div class="mb-2">
                    <i class="fas fa-money-check text-warning"></i>
                    <strong>Cheque:</strong> Verificar validez
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación (opcional) -->
<div class="modal fade" id="modalConfirmacion" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle"></i> Confirmar Pago
                </h5>
                <button class="close text-white" type="button" data-dismiss="modal">
                    <span>×</span>
                </button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de registrar este pago?</p>
                <div id="resumen_pago"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                <button class="btn btn-success" type="button" onclick="$('#formPago').submit();">Confirmar Pago</button>
            </div>
        </div>
    </div>
</div>

<?php
$extra_js = '
<script>
    function actualizarMonto() {
        var select = $("#id_cuota");
        var option = select.find(":selected");
        
        if (option.val()) {
            var monto = option.data("monto");
            var cliente = option.data("cliente");
            var credito = option.data("credito");
            
            $("#monto_pagado").val(monto);
            $("#info_monto").text(parseFloat(monto).toFixed(2));
            $("#info_cliente").text(cliente);
            $("#info_credito").text(credito);
            $("#info_cuota").slideDown();
        } else {
            $("#monto_pagado").val("");
            $("#info_cuota").slideUp();
        }
    }
    
    $(document).ready(function() {
        // Actualizar monto al cargar si hay cuota preseleccionada
        actualizarMonto();
        
        // Validación del formulario
        $("#formPago").on("submit", function(e) {
            var cuota_selected = $("#id_cuota").val();
            var monto = parseFloat($("#monto_pagado").val());
            var monto_cuota = parseFloat($("#id_cuota option:selected").data("monto"));
            
            if (!cuota_selected) {
                e.preventDefault();
                alert("Por favor seleccione una cuota");
                return false;
            }
            
            if (monto !== monto_cuota) {
                if (!confirm("El monto ingresado no coincide con el monto de la cuota. ¿Desea continuar?")) {
                    e.preventDefault();
                    return false;
                }
            }
            
            if (!$("#confirmar_pago").is(":checked")) {
                e.preventDefault();
                alert("Debe confirmar que ha recibido el pago");
                return false;
            }
        });
    });
</script>
';

include '../includes/footer.php';
?>
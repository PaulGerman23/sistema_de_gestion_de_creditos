<?php
include '../conexion.php';

// Variables para el template
$base_url = '../';
$page_title = 'Registrar Pago de Cuota';
$active_page = 'cuotas';
$active_subpage = 'pagar_cuota';

$mensaje = '';
$tipo_mensaje = '';

// Procesar el pago
if ($_POST && isset($_POST['registrar_pago'])) {
    $id_cliente = $_POST['id_cliente'];
    $cantidad_cuotas_pagar = $_POST['cantidad_cuotas_pagar'];
    $monto_pagado = $_POST['monto_pagado'];
    $metodo_pago = $_POST['metodo_pago'];
    $observaciones = $_POST['observaciones'] ?? '';
    
    // Obtener las primeras X cuotas pendientes del cliente en orden
    $stmt = $conn->prepare("SELECT cu.id_cuota, cu.monto_cuota, cu.id_credito
                            FROM cuotas cu
                            JOIN creditos cr ON cu.id_credito = cr.id_credito
                            WHERE cr.id_cliente = ? AND cu.estado IN ('pendiente', 'vencida')
                            ORDER BY cu.numero_cuota ASC
                            LIMIT ?");
    $stmt->bind_param("ii", $id_cliente, $cantidad_cuotas_pagar);
    $stmt->execute();
    $cuotas_pagar = $stmt->get_result();
    
    $cuotas_pagadas = 0;
    $creditos_afectados = [];
    
    while ($cuota = $cuotas_pagar->fetch_assoc()) {
        // Registrar el pago
        $stmt_pago = $conn->prepare("INSERT INTO pagos (id_cuota, monto_pagado, fecha_pago, metodo_pago, observaciones) VALUES (?, ?, NOW(), ?, ?)");
        $stmt_pago->bind_param("idss", $cuota['id_cuota'], $cuota['monto_cuota'], $metodo_pago, $observaciones);
        $stmt_pago->execute();
        
        // Actualizar estado de la cuota
        $stmt_upd = $conn->prepare("UPDATE cuotas SET estado = 'pagada', fecha_pago = NOW() WHERE id_cuota = ?");
        $stmt_upd->bind_param("i", $cuota['id_cuota']);
        $stmt_upd->execute();
        
        $cuotas_pagadas++;
        
        if (!in_array($cuota['id_credito'], $creditos_afectados)) {
            $creditos_afectados[] = $cuota['id_credito'];
        }
    }
    
    // Actualizar estado de los créditos afectados
    include '../creditos/actualizar_estado_credito.php';
    foreach ($creditos_afectados as $id_credito) {
        actualizarEstadoCredito($id_credito, $conn);
    }
    
    $mensaje = "¡Se registraron $cuotas_pagadas pago(s) exitosamente por un total de $" . number_format($monto_pagado, 2) . "!";
    $tipo_mensaje = "success";
}

// Cliente seleccionado
$cliente_seleccionado = null;
$cuotas_disponibles = null;
$creditos_cliente = null;

if (isset($_GET['id_cliente'])) {
    $id_cliente_param = $_GET['id_cliente'];
    
    // Obtener info del cliente
    $stmt = $conn->prepare("SELECT * FROM clientes WHERE id_cliente = ?");
    $stmt->bind_param("i", $id_cliente_param);
    $stmt->execute();
    $cliente_seleccionado = $stmt->get_result()->fetch_assoc();
    
    if ($cliente_seleccionado) {
        // Obtener cuotas pendientes ordenadas
        $stmt_cuotas = $conn->prepare("SELECT cu.*, cr.descripcion as descripcion_credito, cr.id_credito
                                       FROM cuotas cu
                                       JOIN creditos cr ON cu.id_credito = cr.id_credito
                                       WHERE cr.id_cliente = ? AND cu.estado IN ('pendiente', 'vencida')
                                       ORDER BY cu.numero_cuota ASC");
        $stmt_cuotas->bind_param("i", $id_cliente_param);
        $stmt_cuotas->execute();
        $cuotas_disponibles = $stmt_cuotas->get_result();
        
        // Obtener créditos del cliente con cuotas pendientes
        $stmt_creditos = $conn->prepare("SELECT DISTINCT cr.id_credito, cr.descripcion,
                                         (SELECT COUNT(*) FROM cuotas WHERE id_credito = cr.id_credito AND estado IN ('pendiente', 'vencida')) as cuotas_pendientes,
                                         (SELECT SUM(monto_cuota) FROM cuotas WHERE id_credito = cr.id_credito AND estado IN ('pendiente', 'vencida')) as monto_pendiente
                                         FROM creditos cr
                                         WHERE cr.id_cliente = ? AND cr.estado != 'pagado'
                                         HAVING cuotas_pendientes > 0");
        $stmt_creditos->bind_param("i", $id_cliente_param);
        $stmt_creditos->execute();
        $creditos_cliente = $stmt_creditos->get_result();
    }
}

include '../includes/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Registrar Pago de Cuota(s)</h1>
    <a href="ver_cuotas_cliente.php" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm"></i> Volver
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

<div class="row">
    <!-- Formulario Principal -->
    <div class="col-lg-8">
        
        <!-- Paso 1: Buscar y Seleccionar Cliente -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-primary text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-user"></i> Paso 1: Seleccionar Cliente
                </h6>
            </div>
            <div class="card-body">
                <?php if (!$cliente_seleccionado): ?>
                <!-- Búsqueda de Cliente -->
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    Busque y seleccione el cliente que realizará el pago
                </div>
                
                <form method="GET" action="">
                    <div class="form-group">
                        <label for="buscar_cliente">Buscar Cliente:</label>
                        <input type="text" 
                               class="form-control" 
                               id="buscar_cliente" 
                               placeholder="Ingrese nombre, apellido o DNI..."
                               autocomplete="off">
                    </div>
                    <div id="resultados_busqueda"></div>
                </form>
                
                <?php else: ?>
                <!-- Cliente Seleccionado -->
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> 
                    Cliente seleccionado correctamente
                </div>
                
                <div class="card border-left-success">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Nombre:</strong> <?php echo $cliente_seleccionado['nombre'] . ' ' . $cliente_seleccionado['apellido']; ?></p>
                                <p class="mb-1"><strong>DNI:</strong> <?php echo $cliente_seleccionado['dni']; ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Teléfono:</strong> <?php echo $cliente_seleccionado['telefono'] ?: 'No registrado'; ?></p>
                                <p class="mb-1"><strong>Estado:</strong> 
                                    <span class="badge badge-<?php echo $cliente_seleccionado['estado'] == 'activo' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($cliente_seleccionado['estado']); ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="mt-2">
                            <a href="pagar_cuota.php" class="btn btn-sm btn-warning">
                                <i class="fas fa-redo"></i> Cambiar Cliente
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Paso 2: Resumen de Créditos -->
        <?php if ($cliente_seleccionado && $creditos_cliente && $creditos_cliente->num_rows > 0): ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-info text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-credit-card"></i> Créditos con Cuotas Pendientes
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th>Crédito</th>
                                <th class="text-center">Cuotas Pendientes</th>
                                <th class="text-right">Monto Pendiente</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $creditos_cliente->data_seek(0);
                            while ($credito = $creditos_cliente->fetch_assoc()): 
                            ?>
                            <tr>
                                <td>
                                    <?php echo $credito['descripcion']; ?>
                                    <br><small class="text-muted">ID: <?php echo $credito['id_credito']; ?></small>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-warning"><?php echo $credito['cuotas_pendientes']; ?></span>
                                </td>
                                <td class="text-right">
                                    <strong>$<?php echo number_format($credito['monto_pendiente'], 2); ?></strong>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Paso 3: Datos del Pago -->
        <?php if ($cliente_seleccionado && $cuotas_disponibles && $cuotas_disponibles->num_rows > 0): ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-success text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-dollar-sign"></i> Paso 2: Registrar Pago
                </h6>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Importante:</strong> Las cuotas se pagan en orden ascendente. Se pagarán las primeras cuotas pendientes.
                </div>
                
                <form method="POST" action="" id="formPago">
                    <input type="hidden" name="id_cliente" value="<?php echo $cliente_seleccionado['id_cliente']; ?>">
                    <input type="hidden" name="registrar_pago" value="1">
                    
                    <!-- Cantidad de Cuotas a Pagar -->
                    <div class="form-group">
                        <label for="cantidad_cuotas_pagar">Cantidad de Cuotas a Pagar <span class="text-danger">*</span></label>
                        <select class="form-control" id="cantidad_cuotas_pagar" name="cantidad_cuotas_pagar" required onchange="calcularMonto()">
                            <?php
                            $total_cuotas_disponibles = $cuotas_disponibles->num_rows;
                            for ($i = 1; $i <= $total_cuotas_disponibles; $i++):
                            ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?> cuota(s)</option>
                            <?php endfor; ?>
                        </select>
                        <small class="form-text text-muted">
                            Total de cuotas pendientes: <?php echo $total_cuotas_disponibles; ?>
                        </small>
                    </div>
                    
                    <!-- Preview de Cuotas a Pagar -->
                    <div class="card bg-light mb-3" id="preview_cuotas">
                        <div class="card-body">
                            <h6 class="font-weight-bold mb-2">Cuotas que se pagarán:</h6>
                            <div id="lista_cuotas"></div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <strong>Total a Pagar:</strong>
                                <h5 class="text-success mb-0" id="monto_total_preview">$0.00</h5>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Monto del Pago -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="monto_pagado">Monto Total a Pagar <span class="text-danger">*</span></label>
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
                                           readonly
                                           required>
                                </div>
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
                                  rows="2" 
                                  placeholder="Información adicional del pago (opcional)"></textarea>
                    </div>

                    <hr>

                    <!-- Confirmación -->
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="confirmar_pago" required>
                        <label class="form-check-label" for="confirmar_pago">
                            Confirmo que he recibido el pago y los datos son correctos
                        </label>
                    </div>

                    <!-- Botones -->
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
                </form>
            </div>
        </div>
        
        <?php elseif ($cliente_seleccionado && (!$cuotas_disponibles || $cuotas_disponibles->num_rows == 0)): ?>
        <div class="card shadow mb-4">
            <div class="card-body text-center py-5">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <h4 class="text-success">¡No hay cuotas pendientes!</h4>
                <p class="text-muted">Este cliente no tiene cuotas pendientes de pago.</p>
                <a href="../creditos/ver_creditos.php" class="btn btn-primary mt-3">
                    <i class="fas fa-credit-card"></i> Ver Créditos
                </a>
            </div>
        </div>
        <?php endif; ?>
        
    </div>
    
    <!-- Sidebar -->
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-info-circle"></i> Información
                </h6>
            </div>
            <div class="card-body">
                <h6 class="font-weight-bold mb-2">¿Cómo funciona?</h6>
                <ol class="pl-3 mb-3 small">
                    <li>Busque y seleccione el cliente</li>
                    <li>Elija cuántas cuotas pagará</li>
                    <li>Se pagarán las primeras cuotas pendientes EN ORDEN</li>
                    <li>El monto se calcula automáticamente</li>
                    <li>Seleccione el método de pago</li>
                    <li>Confirme y registre</li>
                </ol>
                
                <hr>
                
                <h6 class="font-weight-bold mb-2">Importante:</h6>
                <ul class="pl-3 mb-0 small">
                    <li>Las cuotas se pagan en <strong>orden ascendente</strong></li>
                    <li>No se puede saltar cuotas</li>
                    <li>Se pagan las primeras pendientes automáticamente</li>
                    <li>El estado del crédito se actualiza automáticamente</li>
                </ul>
            </div>
        </div>

        <?php if ($cliente_seleccionado && $cuotas_disponibles && $cuotas_disponibles->num_rows > 0): ?>
        <div class="card shadow mb-4 border-left-warning">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-warning">
                    <i class="fas fa-list"></i> Próximas Cuotas
                </h6>
            </div>
            <div class="card-body">
                <?php
                $cuotas_disponibles->data_seek(0);
                $counter = 1;
                while ($cuota = $cuotas_disponibles->fetch_assoc() && $counter <= 5): 
                ?>
                <div class="mb-2">
                    <strong>#<?php echo $cuota['numero_cuota']; ?></strong> - 
                    $<?php echo number_format($cuota['monto_cuota'], 2); ?>
                    <br><small class="text-muted"><?php echo $cuota['descripcion_credito']; ?></small>
                    <br><small class="text-muted">Vence: <?php echo date('d/m/Y', strtotime($cuota['fecha_vencimiento'])); ?></small>
                </div>
                <?php 
                $counter++;
                endwhile; 
                ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Preparar datos de cuotas para JavaScript
$cuotas_array = [];
if ($cuotas_disponibles) {
    $cuotas_disponibles->data_seek(0);
    while ($cuota = $cuotas_disponibles->fetch_assoc()) {
        $cuotas_array[] = [
            'numero' => $cuota['numero_cuota'],
            'monto' => $cuota['monto_cuota'],
            'credito' => $cuota['descripcion_credito'],
            'vencimiento' => date('d/m/Y', strtotime($cuota['fecha_vencimiento']))
        ];
    }
}
$cuotas_json = json_encode($cuotas_array);

$extra_js = '
<script>
    // Datos de cuotas
    const cuotas = ' . $cuotas_json . ';
    
    // Búsqueda de clientes con AJAX
    $("#buscar_cliente").on("keyup", function() {
        var busqueda = $(this).val();
        if (busqueda.length >= 2) {
            $.ajax({
                url: "buscar_cliente_ajax.php",
                method: "GET",
                data: { q: busqueda, accion: "pagar" },
                success: function(data) {
                    $("#resultados_busqueda").html(data);
                }
            });
        } else {
            $("#resultados_busqueda").html("");
        }
    });
    
    // Formatear número con separador de miles
    function formatearNumero(num) {
        return num.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, "$&,");
    }

    // Calcular monto y mostrar preview
    function calcularMonto() {
        var cantidad = parseInt($("#cantidad_cuotas_pagar").val()) || 0;
        var total = 0;
        var html = "<ul class=\"list-unstyled mb-0\">";
        
        for (var i = 0; i < cantidad && i < cuotas.length; i++) {
            total += parseFloat(cuotas[i].monto);
            html += "<li class=\"mb-1\">";
            html += "<strong>Cuota #" + cuotas[i].numero + ":</strong> $" + formatearNumero(parseFloat(cuotas[i].monto));
            html += "<br><small class=\"text-muted\">" + cuotas[i].credito + " - Vence: " + cuotas[i].vencimiento + "</small>";
            html += "</li>";
        }
        
        html += "</ul>";
        
        $("#lista_cuotas").html(html);
        $("#monto_pagado").val(total.toFixed(2));
        $("#monto_total_preview").text("$" + formatearNumero(total));
    }
    
    $(document).ready(function() {
        if (cuotas.length > 0) {
            calcularMonto();
        }
        
        // Validación del formulario
        $("#formPago").on("submit", function(e) {
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
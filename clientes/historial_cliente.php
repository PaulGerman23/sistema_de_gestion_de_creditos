<?php
include '../conexion.php';

// Variables para el template
$base_url = '../';
$page_title = 'Historial del Cliente';
$active_page = 'clientes';
$active_subpage = 'listar_clientes';

$id_cliente = $_GET['id'] ?? 0;

if (!is_numeric($id_cliente) || $id_cliente <= 0) {
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

include '../includes/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Historial del Cliente</h1>
    <a href="listar_clientes.php" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm"></i> Volver al Listado
    </a>
</div>

<div class="row">
    <!-- Contenido principal -->
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    Historial de Créditos - <?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']); ?>
                </h6>
            </div>
            <div class="card-body">
                <?php
                // Obtener créditos del cliente
                $stmt_creditos = $conn->prepare("SELECT * FROM creditos WHERE id_cliente = ?");
                $stmt_creditos->bind_param("i", $id_cliente);
                $stmt_creditos->execute();
                $creditos = $stmt_creditos->get_result();

                if ($creditos->num_rows > 0):
                    while ($credito = $creditos->fetch_assoc()):
                ?>
                    <div class="card mb-3 shadow-sm">
                        <div class="card-body">
                            <h5 class="text-primary">
                                <i class="fas fa-coins"></i> <?php echo htmlspecialchars($credito['descripcion']); ?>
                            </h5>
                            <p><strong>Monto total:</strong> $<?php echo number_format($credito['monto_total'],2); ?></p>
                            <?php if (!empty($credito['fecha_otorgamiento']) && strtotime($credito['fecha_otorgamiento']) !== false): ?>
                                <p><strong>Fecha de otorgamiento:</strong> <?php echo date('d/m/Y', strtotime($credito['fecha_otorgamiento'])); ?></p>
                            <?php else: ?>
                                <p><strong>Fecha de otorgamiento:</strong> No registrada</p>
                            <?php endif; ?>


                            <?php
                            // Obtener cuotas
                            $stmt_cuotas = $conn->prepare("SELECT * FROM cuotas WHERE id_credito = ?");
                            $stmt_cuotas->bind_param("i", $credito['id_credito']);
                            $stmt_cuotas->execute();
                            $cuotas = $stmt_cuotas->get_result();

                            if ($cuotas->num_rows > 0):
                            ?>
                                <ul class="list-group list-group-flush">
                                    <?php while ($cuota = $cuotas->fetch_assoc()):
                                        $estado_raw = strtolower($cuota['estado']);
                                        $badge_class = match($estado_raw) {
                                            'pagada' => 'success',
                                            'pendiente' => 'warning',
                                            'vencida' => 'danger',
                                            default => 'secondary'
                                        };
                                        $estado = ucfirst($estado_raw);  
                                    ?>
                                                                     
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>Cuota #<?php echo $cuota['numero_cuota']; ?></strong><br>
                                                $<?php echo number_format($cuota['monto_cuota'],2); ?><br>
                                                <small class="text-muted">Vence: <?php echo date('d/m/Y', strtotime($cuota['fecha_vencimiento'])); ?></small>
                                            </div>
                                            <span class="badge badge-<?php 
                                                echo $cuota['estado'] == 'pagada' ? 'success' : 
                                                    ($cuota['estado'] == 'pendiente' ? 'warning' : 
                                                    ($cuota['estado'] == 'vencida' ? 'danger' : 'secondary')); 
                                            ?>">
                                                <?php echo ucfirst($cuota['estado']); ?>
                                            </span>

                                        </li>
                                    <?php endwhile; ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-muted"><em>No hay cuotas registradas para este crédito.</em></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php
                    endwhile;
                else:
                    echo "<div class='alert alert-info'>No se encontraron créditos para este cliente.</div>";
                endif;
                ?>
            </div>
        </div>
    </div>

    <!-- Sidebar con información del cliente y acciones -->
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Información del Cliente</h6>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Nombre:</strong></p>
                <p class="mb-3"><?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']); ?></p>

                <p class="mb-2"><strong>DNI:</strong></p>
                <p class="mb-3"><?php echo htmlspecialchars($cliente['dni']); ?></p>

                <p class="mb-2"><strong>Teléfono:</strong></p>
                <p class="mb-3"><?php echo htmlspecialchars($cliente['telefono'] ?? 'Sin teléfono'); ?></p>

                <p class="mb-2"><strong>Email:</strong></p>
                <p class="mb-3"><?php echo htmlspecialchars($cliente['email'] ?? 'Sin email'); ?></p>

                <p class="mb-2"><strong>Ciudad:</strong></p>
                <p class="mb-3"><?php echo htmlspecialchars($cliente['ciudad'] ?? 'Sin ciudad'); ?></p>

                <p class="mb-2"><strong>Estado:</strong></p>
                <p class="mb-3">
                    <span class="badge badge-<?php echo $cliente['estado'] == 'activo' ? 'success' : ($cliente['estado'] == 'moroso' ? 'danger' : 'secondary'); ?>">
                        <?php echo ucfirst($cliente['estado']); ?>
                    </span>
                </p>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-info">Acciones Rápidas</h6>
            </div>
            <div class="card-body">
                <a href="editar_cliente.php?id=<?php echo $id_cliente; ?>" class="btn btn-primary btn-sm btn-block">
                    <i class="fas fa-edit"></i> Editar Cliente
                </a>
                <a href="../cuotas/ver_cuotas_cliente.php?id_cliente=<?php echo $id_cliente; ?>" class="btn btn-warning btn-sm btn-block">
                    <i class="fas fa-calendar-alt"></i> Ver Cuotas
                </a>
                <a href="eliminar_cliente.php?id=<?php echo $id_cliente; ?>" class="btn btn-danger btn-sm btn-block" onclick="return confirm('¿Está seguro de eliminar este cliente?');">
                    <i class="fas fa-trash"></i> Eliminar Cliente
                </a>
            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
include '../includes/footer.php';
?>

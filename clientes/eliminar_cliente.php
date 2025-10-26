<?php
include '../conexion.php';

// Variables para el template
$base_url = '../';
$page_title = 'Eliminar Cliente';
$active_page = 'clientes';
$active_subpage = 'listar_clientes';

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

// Verificar si tiene créditos asociados
$creditos = $conn->query("SELECT COUNT(*) as total FROM creditos WHERE id_cliente = $id_cliente")->fetch_assoc()['total'];

$mensaje = '';
$tipo_mensaje = '';

// Procesar eliminación
if ($_POST && isset($_POST['confirmar_eliminacion'])) {
    // Eliminar en cascada: primero pagos, luego cuotas, luego créditos, finalmente cliente
    
    // 1. Eliminar pagos de las cuotas del cliente
    $conn->query("DELETE p FROM pagos p
                  JOIN cuotas cu ON p.id_cuota = cu.id_cuota
                  JOIN creditos cr ON cu.id_credito = cr.id_credito
                  WHERE cr.id_cliente = $id_cliente");
    
    // 2. Eliminar moras del cliente
    $conn->query("DELETE FROM moras WHERE id_credito IN 
                  (SELECT id_credito FROM creditos WHERE id_cliente = $id_cliente)");
    
    // 3. Eliminar cuotas del cliente
    $conn->query("DELETE cu FROM cuotas cu
                  JOIN creditos cr ON cu.id_credito = cr.id_credito
                  WHERE cr.id_cliente = $id_cliente");
    
    // 4. Eliminar créditos del cliente
    $conn->query("DELETE FROM creditos WHERE id_cliente = $id_cliente");
    
    // 5. Eliminar cliente
    $stmt = $conn->prepare("DELETE FROM clientes WHERE id_cliente = ?");
    $stmt->bind_param("i", $id_cliente);
    
    if ($stmt->execute()) {
        header("Location: listar_clientes.php?eliminado=success");
        exit;
    } else {
        $mensaje = "Error al eliminar el cliente: " . $stmt->error;
        $tipo_mensaje = "danger";
    }
}

include '../includes/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Eliminar Cliente</h1>
    <a href="listar_clientes.php" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm"></i> Volver al Listado
    </a>
</div>

<!-- Mensaje de error -->
<?php if ($mensaje): ?>
<div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
    <strong>¡Error!</strong> <?php echo $mensaje; ?>
    <button type="button" class="close" data-dismiss="alert">
        <span>&times;</span>
    </button>
</div>
<?php endif; ?>

<!-- Confirmación de Eliminación -->
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4 border-left-danger">
            <div class="card-header py-3 bg-danger text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-exclamation-triangle"></i> Confirmar Eliminación
                </h6>
            </div>
            <div class="card-body">
                <div class="alert alert-danger" role="alert">
                    <h5 class="alert-heading">
                        <i class="fas fa-exclamation-triangle"></i> ¡ADVERTENCIA!
                    </h5>
                    <p class="mb-0">
                        Esta acción es <strong>IRREVERSIBLE</strong> y eliminará permanentemente:
                    </p>
                    <ul class="mt-2 mb-0">
                        <li>Los datos del cliente</li>
                        <li>Todos sus créditos (<?php echo $creditos; ?>)</li>
                        <li>Todas las cuotas asociadas</li>
                        <li>Todo el historial de pagos</li>
                        <li>Todas las moras aplicadas</li>
                    </ul>
                </div>
                
                <hr>
                
                <h5 class="mb-3">Datos del Cliente a Eliminar:</h5>
                
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <td class="font-weight-bold" width="30%">ID Cliente:</td>
                                <td><?php echo $cliente['id_cliente']; ?></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Nombre Completo:</td>
                                <td><?php echo $cliente['nombre'] . ' ' . $cliente['apellido']; ?></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">DNI:</td>
                                <td><?php echo $cliente['dni']; ?></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Teléfono:</td>
                                <td><?php echo $cliente['telefono'] ?: 'No registrado'; ?></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Email:</td>
                                <td><?php echo $cliente['email'] ?: 'No registrado'; ?></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Ciudad:</td>
                                <td><?php echo $cliente['ciudad'] ?: 'No registrada'; ?></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Estado:</td>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo $cliente['estado'] == 'activo' ? 'success' : 
                                            ($cliente['estado'] == 'moroso' ? 'danger' : 'warning'); 
                                    ?>">
                                        <?php echo ucfirst($cliente['estado']); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Fecha de Registro:</td>
                                <td><?php echo date('d/m/Y', strtotime($cliente['fecha_registro'])); ?></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Créditos Asociados:</td>
                                <td>
                                    <span class="badge badge-<?php echo $creditos > 0 ? 'warning' : 'secondary'; ?>">
                                        <?php echo $creditos; ?> crédito(s)
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <hr>
                
                <form method="POST" action="" id="formEliminar">
                    <input type="hidden" name="confirmar_eliminacion" value="1">
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="confirmar" required>
                        <label class="form-check-label" for="confirmar">
                            <strong>Confirmo que deseo eliminar permanentemente este cliente y toda su información asociada</strong>
                        </label>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i> 
                        Esta acción no se puede deshacer. Asegúrese de haber respaldado cualquier información importante antes de continuar.
                    </div>
                    
                    <button type="submit" class="btn btn-danger btn-icon-split btn-lg" id="btnEliminar">
                        <span class="icon text-white-50">
                            <i class="fas fa-trash"></i>
                        </span>
                        <span class="text">Eliminar Cliente Permanentemente</span>
                    </button>
                    
                    <a href="listar_clientes.php" class="btn btn-secondary btn-icon-split btn-lg">
                        <span class="icon text-white-50">
                            <i class="fas fa-times"></i>
                        </span>
                        <span class="text">Cancelar</span>
                    </a>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Sidebar con información -->
    <div class="col-lg-4">
        <div class="card shadow mb-4 border-left-warning">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-warning">
                    <i class="fas fa-exclamation-circle"></i> Consideraciones
                </h6>
            </div>
            <div class="card-body">
                <h6 class="font-weight-bold mb-2">Antes de eliminar:</h6>
                <ul class="small pl-3 mb-3">
                    <li>Verifique que no hay pagos pendientes</li>
                    <li>Exporte los reportes necesarios</li>
                    <li>Considere inactivar en lugar de eliminar</li>
                    <li>Comunique al cliente si es necesario</li>
                </ul>
                
                <hr>
                
                <h6 class="font-weight-bold mb-2">Alternativa:</h6>
                <p class="small mb-3">
                    En lugar de eliminar, puede cambiar el estado del cliente a "Inactivo". 
                    Esto preserva el historial sin afectar las operaciones actuales.
                </p>
                
                <a href="editar_cliente.php?id=<?php echo $id_cliente; ?>" class="btn btn-warning btn-sm btn-block">
                    <i class="fas fa-edit"></i> Editar Cliente
                </a>
            </div>
        </div>
        
        <?php if ($creditos > 0): ?>
        <div class="card shadow mb-4 border-left-info">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-info">
                    <i class="fas fa-info-circle"></i> Créditos Asociados
                </h6>
            </div>
            <div class="card-body">
                <p class="small mb-2">
                    Este cliente tiene <strong><?php echo $creditos; ?> crédito(s)</strong> registrado(s).
                </p>
                <p class="small mb-0">
                    Al eliminar el cliente, también se eliminarán todos sus créditos, cuotas y pagos asociados.
                </p>
                <hr>
                <a href="../cuotas/ver_cuotas_cliente.php?id_cliente=<?php echo $id_cliente; ?>" class="btn btn-info btn-sm btn-block">
                    <i class="fas fa-eye"></i> Ver Créditos
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
$extra_js = '
<script>
$(document).ready(function() {
    $("#formEliminar").on("submit", function(e) {
        if (!$("#confirmar").is(":checked")) {
            e.preventDefault();
            alert("Debe confirmar que desea eliminar el cliente");
            return false;
        }
        
        if (!confirm("¿Está COMPLETAMENTE SEGURO de eliminar este cliente?\n\nEsta acción NO se puede deshacer.")) {
            e.preventDefault();
            return false;
        }
    });
});
</script>
';

include '../includes/footer.php';
?>
<?php
include '../conexion.php';

$id_pago = $_GET['id'] ?? 0;

if (!$id_pago) {
    die("ID de pago no especificado.");
}

// Obtener información del pago
$stmt = $conn->prepare("SELECT p.*, cu.numero_cuota, cu.monto_cuota, cu.fecha_vencimiento,
                               cr.id_credito, cr.descripcion as descripcion_credito, cr.monto_total,
                               cl.id_cliente, cl.nombre, cl.apellido, cl.dni, cl.direccion, cl.ciudad, cl.telefono
                        FROM pagos p
                        JOIN cuotas cu ON p.id_cuota = cu.id_cuota
                        JOIN creditos cr ON cu.id_credito = cr.id_credito
                        JOIN clientes cl ON cr.id_cliente = cl.id_cliente
                        WHERE p.id_pago = ?");
$stmt->bind_param("i", $id_pago);
$stmt->execute();
$pago = $stmt->get_result()->fetch_assoc();

if (!$pago) {
    die("Pago no encontrado.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Comprobante de Pago #<?php echo $id_pago; ?></title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            padding: 20px;
            background-color: #f8f9fa;
        }
        
        .comprobante {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border: 2px solid #4e73df;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }
        
        .header p {
            margin: 5px 0 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        
        .comprobante-numero {
            background: rgba(255, 255, 255, 0.2);
            display: inline-block;
            padding: 10px 20px;
            border-radius: 5px;
            margin-top: 15px;
            font-size: 18px;
            font-weight: bold;
        }
        
        .content {
            padding: 30px;
        }
        
        .seccion {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e3e6f0;
        }
        
        .seccion:last-child {
            border-bottom: none;
        }
        
        .seccion h3 {
            color: #4e73df;
            font-size: 18px;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #4e73df;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .info-label {
            font-weight: bold;
            color: #5a5c69;
        }
        
        .info-value {
            color: #3a3b45;
        }
        
        .monto-destacado {
            background: #1cc88a;
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
        }
        
        .monto-destacado .label {
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .monto-destacado .monto {
            font-size: 36px;
            font-weight: bold;
        }
        
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-top: 2px solid #e3e6f0;
        }
        
        .firma {
            margin-top: 50px;
            text-align: center;
        }
        
        .firma-linea {
            width: 300px;
            border-top: 2px solid #000;
            margin: 0 auto 10px auto;
        }
        
        .sello {
            position: absolute;
            top: 100px;
            right: 50px;
            width: 150px;
            height: 150px;
            border: 3px solid #e74a3b;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transform: rotate(-15deg);
            opacity: 0.3;
            font-weight: bold;
            font-size: 20px;
            color: #e74a3b;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .no-print {
                display: none !important;
            }
            
            .comprobante {
                border: none;
                max-width: 100%;
            }
            
            .sello {
                opacity: 0.2;
            }
        }
        
        .btn-imprimir {
            background: #4e73df;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin: 20px;
        }
        
        .btn-imprimir:hover {
            background: #224abe;
        }
        
        .qr-code {
            text-align: center;
            margin: 20px 0;
        }
        
        .qr-code img {
            width: 120px;
            height: 120px;
        }
    </style>
</head>
<body>
    
    <div class="no-print" style="text-align: center;">
        <button class="btn-imprimir" onclick="window.print()">
            <i class="fas fa-print"></i> Imprimir Comprobante
        </button>
        <button class="btn-imprimir" onclick="window.close()" style="background: #858796;">
            Cerrar
        </button>
    </div>
    
    <div class="comprobante">
        <div class="sello">PAGADO</div>
        
        <div class="header">
            <h1>COMPROBANTE DE PAGO</h1>
            <p>Sistema de Gestión de Créditos</p>
            <div class="comprobante-numero">
                N° <?php echo str_pad($id_pago, 8, '0', STR_PAD_LEFT); ?>
            </div>
        </div>
        
        <div class="content">
            <!-- Información del Cliente -->
            <div class="seccion">
                <h3>Datos del Cliente</h3>
                <div class="info-row">
                    <span class="info-label">Cliente:</span>
                    <span class="info-value"><?php echo $pago['nombre'] . ' ' . $pago['apellido']; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">DNI:</span>
                    <span class="info-value"><?php echo $pago['dni']; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Dirección:</span>
                    <span class="info-value"><?php echo $pago['direccion'] ?: 'No registrada'; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Ciudad:</span>
                    <span class="info-value"><?php echo $pago['ciudad'] ?: 'No registrada'; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Teléfono:</span>
                    <span class="info-value"><?php echo $pago['telefono'] ?: 'No registrado'; ?></span>
                </div>
            </div>
            
            <!-- Información del Crédito -->
            <div class="seccion">
                <h3>Datos del Crédito</h3>
                <div class="info-row">
                    <span class="info-label">ID Crédito:</span>
                    <span class="info-value">#<?php echo $pago['id_credito']; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Descripción:</span>
                    <span class="info-value"><?php echo $pago['descripcion_credito']; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Número de Cuota:</span>
                    <span class="info-value"><?php echo $pago['numero_cuota']; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Fecha de Vencimiento:</span>
                    <span class="info-value"><?php echo date('d/m/Y', strtotime($pago['fecha_vencimiento'])); ?></span>
                </div>
            </div>
            
            <!-- Información del Pago -->
            <div class="seccion">
                <h3>Datos del Pago</h3>
                <div class="info-row">
                    <span class="info-label">Fecha de Pago:</span>
                    <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($pago['fecha_pago'])); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Método de Pago:</span>
                    <span class="info-value"><?php echo ucfirst($pago['metodo_pago']); ?></span>
                </div>
                <?php if ($pago['observaciones']): ?>
                <div class="info-row">
                    <span class="info-label">Observaciones:</span>
                    <span class="info-value"><?php echo $pago['observaciones']; ?></span>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Monto Destacado -->
            <div class="monto-destacado">
                <div class="label">MONTO PAGADO</div>
                <div class="monto">$<?php echo number_format($pago['monto_pagado'], 2); ?></div>
            </div>
            
            <!-- Firma -->
            <div class="firma">
                <div class="firma-linea"></div>
                <p><strong>Firma y Aclaración del Receptor</strong></p>
            </div>
        </div>
        
        <div class="footer">
            <p style="margin: 0; color: #858796; font-size: 12px;">
                Este comprobante es válido como constancia de pago.<br>
                Fecha de emisión: <?php echo date('d/m/Y H:i'); ?><br>
                Sistema de Gestión de Créditos - Todos los derechos reservados
            </p>
        </div>
    </div>
    
    <script>
        // Auto-imprimir al cargar (opcional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
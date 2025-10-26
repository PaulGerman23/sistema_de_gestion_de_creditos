-- =====================================================
-- DATOS DE PRUEBA COMPLETOS PARA SISTEMA DE CRÉDITOS
-- =====================================================
-- Este script genera datos que cubren TODOS los escenarios:
-- - Alertas de vencimiento (7, 15, 30 días)
-- - Créditos morosos (leve, moderado, grave)
-- - Créditos pagados completamente
-- - Cuotas en todos los estados
-- - Moras aplicadas
-- - Diferentes métodos de pago
-- =====================================================

-- Limpiar datos existentes (CUIDADO: Esto borra todo)
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE moras;
TRUNCATE TABLE pagos;
TRUNCATE TABLE cuotas;
TRUNCATE TABLE creditos;
TRUNCATE TABLE clientes;
SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- 1. INSERTAR CLIENTES (15 clientes con diferentes estados)
-- =====================================================

INSERT INTO clientes (nombre, apellido, dni, telefono, direccion, ciudad, email, estado, fecha_registro) VALUES
-- Clientes ACTIVOS (con créditos al día)
('Juan', 'Pérez', '20123456', '3814001001', 'San Martín 123', 'Salta', 'juan.perez@email.com', 'activo', DATE_SUB(CURDATE(), INTERVAL 180 DAY)),
('María', 'González', '27234567', '3814002002', 'Belgrano 456', 'Salta', 'maria.gonzalez@email.com', 'activo', DATE_SUB(CURDATE(), INTERVAL 150 DAY)),
('Carlos', 'Rodríguez', '33345678', '3814003003', 'Rivadavia 789', 'Salta', 'carlos.rodriguez@email.com', 'activo', DATE_SUB(CURDATE(), INTERVAL 120 DAY)),
('Ana', 'Martínez', '29456789', '3814004004', 'Mitre 321', 'Salta', 'ana.martinez@email.com', 'activo', DATE_SUB(CURDATE(), INTERVAL 90 DAY)),
('Luis', 'Fernández', '31567890', '3814005005', 'España 654', 'Salta', 'luis.fernandez@email.com', 'activo', DATE_SUB(CURDATE(), INTERVAL 60 DAY)),

-- Clientes MOROSOS (con cuotas vencidas)
('Pedro', 'Sánchez', '28678901', '3814006006', 'Alvarado 987', 'Salta', 'pedro.sanchez@email.com', 'moroso', DATE_SUB(CURDATE(), INTERVAL 200 DAY)),
('Laura', 'López', '35789012', '3814007007', 'Caseros 147', 'Salta', 'laura.lopez@email.com', 'moroso', DATE_SUB(CURDATE(), INTERVAL 170 DAY)),
('Diego', 'Torres', '32890123', '3814008008', 'Balcarce 258', 'Salta', 'diego.torres@email.com', 'moroso', DATE_SUB(CURDATE(), INTERVAL 140 DAY)),

-- Clientes con créditos PAGADOS (exitosos)
('Sofía', 'Ramírez', '30901234', '3814009009', 'Córdoba 369', 'Salta', 'sofia.ramirez@email.com', 'activo', DATE_SUB(CURDATE(), INTERVAL 250 DAY)),
('Miguel', 'Díaz', '26012345', '3814010010', 'Tucumán 741', 'Salta', 'miguel.diaz@email.com', 'activo', DATE_SUB(CURDATE(), INTERVAL 220 DAY)),

-- Clientes con créditos próximos a vencer
('Valentina', 'Romero', '34123456', '3814011011', 'Jujuy 852', 'Salta', 'valentina.romero@email.com', 'activo', DATE_SUB(CURDATE(), INTERVAL 30 DAY)),
('Javier', 'Ruiz', '29234567', '3814012012', 'Mendoza 963', 'Salta', 'javier.ruiz@email.com', 'activo', DATE_SUB(CURDATE(), INTERVAL 45 DAY)),

-- Clientes INACTIVOS (sin actividad reciente)
('Carolina', 'Silva', '31345678', '3814013013', 'La Rioja 159', 'Salta', 'carolina.silva@email.com', 'inactivo', DATE_SUB(CURDATE(), INTERVAL 300 DAY)),
('Roberto', 'Castro', '28456789', '3814014014', 'Catamarca 357', 'Salta', 'roberto.castro@email.com', 'inactivo', DATE_SUB(CURDATE(), INTERVAL 280 DAY)),

-- Cliente nuevo (recién registrado)
('Lucía', 'Morales', '33567890', '3814015015', 'Santiago 486', 'Salta', 'lucia.morales@email.com', 'activo', CURDATE());

-- =====================================================
-- 2. INSERTAR CRÉDITOS (Diferentes escenarios)
-- =====================================================

-- CRÉDITO 1: Activo, pagando normalmente (vence en 6 meses)
INSERT INTO creditos (id_cliente, monto_total, cantidad_cuotas, cuota_mensual, interes_anual, fecha_inicio, fecha_vencimiento, descripcion, estado, fecha_creacion) VALUES
(1, 50000.00, 12, 4500.00, 10.00, DATE_SUB(CURDATE(), INTERVAL 6 MONTH), DATE_ADD(CURDATE(), INTERVAL 6 MONTH), 'Crédito para electrodomésticos', 'activo', DATE_SUB(CURDATE(), INTERVAL 6 MONTH));

-- CRÉDITO 2: Próximo a vencer (7 días) - ALERTA CRÍTICA
INSERT INTO creditos (id_cliente, monto_total, cantidad_cuotas, cuota_mensual, interes_anual, fecha_inicio, fecha_vencimiento, descripcion, estado, fecha_creacion) VALUES
(11, 15000.00, 3, 5100.00, 5.00, DATE_SUB(CURDATE(), INTERVAL 83 DAY), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'Crédito personal urgente', 'activo', DATE_SUB(CURDATE(), INTERVAL 83 DAY));

-- CRÉDITO 3: Próximo a vencer (15 días) - ALERTA MEDIA
INSERT INTO creditos (id_cliente, monto_total, cantidad_cuotas, cuota_mensual, interes_anual, fecha_inicio, fecha_vencimiento, descripcion, estado, fecha_creacion) VALUES
(12, 30000.00, 6, 5200.00, 8.00, DATE_SUB(CURDATE(), INTERVAL 165 DAY), DATE_ADD(CURDATE(), INTERVAL 15 DAY), 'Crédito para muebles', 'activo', DATE_SUB(CURDATE(), INTERVAL 165 DAY));

-- CRÉDITO 4: Próximo a vencer (30 días) - ALERTA BAJA
INSERT INTO creditos (id_cliente, monto_total, cantidad_cuotas, cuota_mensual, interes_anual, fecha_inicio, fecha_vencimiento, descripcion, estado, fecha_creacion) VALUES
(2, 80000.00, 24, 3666.67, 12.00, DATE_SUB(CURDATE(), INTERVAL 23 MONTH), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'Crédito para vehículo', 'activo', DATE_SUB(CURDATE(), INTERVAL 23 MONTH));

-- CRÉDITO 5: MOROSO LEVE (15 días de atraso)
INSERT INTO creditos (id_cliente, monto_total, cantidad_cuotas, cuota_mensual, interes_anual, fecha_inicio, fecha_vencimiento, descripcion, estado, fecha_creacion) VALUES
(6, 25000.00, 10, 2600.00, 9.00, DATE_SUB(CURDATE(), INTERVAL 5 MONTH), DATE_ADD(CURDATE(), INTERVAL 5 MONTH), 'Crédito para herramientas', 'moroso', DATE_SUB(CURDATE(), INTERVAL 5 MONTH));

-- CRÉDITO 6: MOROSO MODERADO (45 días de atraso)
INSERT INTO creditos (id_cliente, monto_total, cantidad_cuotas, cuota_mensual, interes_anual, fecha_inicio, fecha_vencimiento, descripcion, estado, fecha_creacion) VALUES
(7, 40000.00, 12, 3500.00, 11.00, DATE_SUB(CURDATE(), INTERVAL 8 MONTH), DATE_ADD(CURDATE(), INTERVAL 4 MONTH), 'Crédito para construcción', 'moroso', DATE_SUB(CURDATE(), INTERVAL 8 MONTH));

-- CRÉDITO 7: MOROSO GRAVE (90 días de atraso)
INSERT INTO creditos (id_cliente, monto_total, cantidad_cuotas, cuota_mensual, interes_anual, fecha_inicio, fecha_vencimiento, descripcion, estado, fecha_creacion) VALUES
(8, 60000.00, 18, 3600.00, 13.00, DATE_SUB(CURDATE(), INTERVAL 12 MONTH), DATE_ADD(CURDATE(), INTERVAL 6 MONTH), 'Crédito comercial', 'moroso', DATE_SUB(CURDATE(), INTERVAL 12 MONTH));

-- CRÉDITO 8: PAGADO COMPLETAMENTE (Exitoso)
INSERT INTO creditos (id_cliente, monto_total, cantidad_cuotas, cuota_mensual, interes_anual, fecha_inicio, fecha_vencimiento, descripcion, estado, fecha_creacion) VALUES
(9, 20000.00, 6, 3400.00, 7.00, DATE_SUB(CURDATE(), INTERVAL 8 MONTH), DATE_SUB(CURDATE(), INTERVAL 2 MONTH), 'Crédito para viaje', 'pagado', DATE_SUB(CURDATE(), INTERVAL 8 MONTH));

-- CRÉDITO 9: PAGADO COMPLETAMENTE (Exitoso)
INSERT INTO creditos (id_cliente, monto_total, cantidad_cuotas, cuota_mensual, interes_anual, fecha_inicio, fecha_vencimiento, descripcion, estado, fecha_creacion) VALUES
(10, 35000.00, 10, 3650.00, 8.00, DATE_SUB(CURDATE(), INTERVAL 12 MONTH), DATE_SUB(CURDATE(), INTERVAL 2 MONTH), 'Crédito para remodelación', 'pagado', DATE_SUB(CURDATE(), INTERVAL 12 MONTH));

-- CRÉDITO 10: Activo, con algunas cuotas pagas
INSERT INTO creditos (id_cliente, monto_total, cantidad_cuotas, cuota_mensual, interes_anual, fecha_inicio, fecha_vencimiento, descripcion, estado, fecha_creacion) VALUES
(3, 45000.00, 15, 3150.00, 9.00, DATE_SUB(CURDATE(), INTERVAL 9 MONTH), DATE_ADD(CURDATE(), INTERVAL 6 MONTH), 'Crédito para educación', 'activo', DATE_SUB(CURDATE(), INTERVAL 9 MONTH));

-- CRÉDITO 11: Activo, nuevo (1 mes)
INSERT INTO creditos (id_cliente, monto_total, cantidad_cuotas, cuota_mensual, interes_anual, fecha_inicio, fecha_vencimiento, descripcion, estado, fecha_creacion) VALUES
(4, 20000.00, 8, 2600.00, 6.00, DATE_SUB(CURDATE(), INTERVAL 1 MONTH), DATE_ADD(CURDATE(), INTERVAL 7 MONTH), 'Crédito para tecnología', 'activo', DATE_SUB(CURDATE(), INTERVAL 1 MONTH));

-- CRÉDITO 12: Activo, recién iniciado
INSERT INTO creditos (id_cliente, monto_total, cantidad_cuotas, cuota_mensual, interes_anual, fecha_inicio, fecha_vencimiento, descripcion, estado, fecha_creacion) VALUES
(5, 18000.00, 6, 3100.00, 5.00, DATE_SUB(CURDATE(), INTERVAL 15 DAY), DATE_ADD(CURDATE(), INTERVAL 165 DAY), 'Crédito para ropa', 'activo', DATE_SUB(CURDATE(), INTERVAL 15 DAY));

-- CRÉDITO 13: Cliente nuevo (recién otorgado)
INSERT INTO creditos (id_cliente, monto_total, cantidad_cuotas, cuota_mensual, interes_anual, fecha_inicio, fecha_vencimiento, descripcion, estado, fecha_creacion) VALUES
(15, 12000.00, 4, 3100.00, 4.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 4 MONTH), 'Crédito para decoración', 'activo', CURDATE());

-- =====================================================
-- 3. GENERAR CUOTAS PARA CADA CRÉDITO
-- =====================================================

-- CUOTAS CRÉDITO 1 (12 cuotas, 6 pagadas, 6 pendientes)
INSERT INTO cuotas (id_credito, numero_cuota, monto_cuota, fecha_vencimiento, estado, fecha_pago) VALUES
(1, 1, 4500.00, DATE_SUB(CURDATE(), INTERVAL 5 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 5 MONTH)),
(1, 2, 4500.00, DATE_SUB(CURDATE(), INTERVAL 4 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 4 MONTH)),
(1, 3, 4500.00, DATE_SUB(CURDATE(), INTERVAL 3 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 3 MONTH)),
(1, 4, 4500.00, DATE_SUB(CURDATE(), INTERVAL 2 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 2 MONTH)),
(1, 5, 4500.00, DATE_SUB(CURDATE(), INTERVAL 1 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 1 MONTH)),
(1, 6, 4500.00, CURDATE(), 'pagada', CURDATE()),
(1, 7, 4500.00, DATE_ADD(CURDATE(), INTERVAL 1 MONTH), 'pendiente', NULL),
(1, 8, 4500.00, DATE_ADD(CURDATE(), INTERVAL 2 MONTH), 'pendiente', NULL),
(1, 9, 4500.00, DATE_ADD(CURDATE(), INTERVAL 3 MONTH), 'pendiente', NULL),
(1, 10, 4500.00, DATE_ADD(CURDATE(), INTERVAL 4 MONTH), 'pendiente', NULL),
(1, 11, 4500.00, DATE_ADD(CURDATE(), INTERVAL 5 MONTH), 'pendiente', NULL),
(1, 12, 4500.00, DATE_ADD(CURDATE(), INTERVAL 6 MONTH), 'pendiente', NULL);

-- CUOTAS CRÉDITO 2 (3 cuotas, 2 pagadas, 1 vence en 7 días) - ALERTA CRÍTICA
INSERT INTO cuotas (id_credito, numero_cuota, monto_cuota, fecha_vencimiento, estado, fecha_pago) VALUES
(2, 1, 5100.00, DATE_SUB(CURDATE(), INTERVAL 53 DAY), 'pagada', DATE_SUB(CURDATE(), INTERVAL 53 DAY)),
(2, 2, 5100.00, DATE_SUB(CURDATE(), INTERVAL 23 DAY), 'pagada', DATE_SUB(CURDATE(), INTERVAL 23 DAY)),
(2, 3, 5100.00, DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'pendiente', NULL);

-- CUOTAS CRÉDITO 3 (6 cuotas, 5 pagadas, 1 vence en 15 días) - ALERTA MEDIA
INSERT INTO cuotas (id_credito, numero_cuota, monto_cuota, fecha_vencimiento, estado, fecha_pago) VALUES
(3, 1, 5200.00, DATE_SUB(CURDATE(), INTERVAL 135 DAY), 'pagada', DATE_SUB(CURDATE(), INTERVAL 135 DAY)),
(3, 2, 5200.00, DATE_SUB(CURDATE(), INTERVAL 105 DAY), 'pagada', DATE_SUB(CURDATE(), INTERVAL 105 DAY)),
(3, 3, 5200.00, DATE_SUB(CURDATE(), INTERVAL 75 DAY), 'pagada', DATE_SUB(CURDATE(), INTERVAL 75 DAY)),
(3, 4, 5200.00, DATE_SUB(CURDATE(), INTERVAL 45 DAY), 'pagada', DATE_SUB(CURDATE(), INTERVAL 45 DAY)),
(3, 5, 5200.00, DATE_SUB(CURDATE(), INTERVAL 15 DAY), 'pagada', DATE_SUB(CURDATE(), INTERVAL 15 DAY)),
(3, 6, 5200.00, DATE_ADD(CURDATE(), INTERVAL 15 DAY), 'pendiente', NULL);

-- CUOTAS CRÉDITO 4 (24 cuotas, 23 pagadas, 1 vence en 30 días) - ALERTA BAJA
INSERT INTO cuotas (id_credito, numero_cuota, monto_cuota, fecha_vencimiento, estado, fecha_pago) VALUES
(4, 1, 3666.67, DATE_SUB(CURDATE(), INTERVAL 22 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 22 MONTH)),
(4, 2, 3666.67, DATE_SUB(CURDATE(), INTERVAL 21 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 21 MONTH)),
(4, 3, 3666.67, DATE_SUB(CURDATE(), INTERVAL 20 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 20 MONTH)),
(4, 4, 3666.67, DATE_SUB(CURDATE(), INTERVAL 19 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 19 MONTH)),
(4, 5, 3666.67, DATE_SUB(CURDATE(), INTERVAL 18 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 18 MONTH)),
(4, 6, 3666.67, DATE_SUB(CURDATE(), INTERVAL 17 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 17 MONTH)),
(4, 7, 3666.67, DATE_SUB(CURDATE(), INTERVAL 16 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 16 MONTH)),
(4, 8, 3666.67, DATE_SUB(CURDATE(), INTERVAL 15 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 15 MONTH)),
(4, 9, 3666.67, DATE_SUB(CURDATE(), INTERVAL 14 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 14 MONTH)),
(4, 10, 3666.67, DATE_SUB(CURDATE(), INTERVAL 13 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 13 MONTH)),
(4, 11, 3666.67, DATE_SUB(CURDATE(), INTERVAL 12 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 12 MONTH)),
(4, 12, 3666.67, DATE_SUB(CURDATE(), INTERVAL 11 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 11 MONTH)),
(4, 13, 3666.67, DATE_SUB(CURDATE(), INTERVAL 10 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 10 MONTH)),
(4, 14, 3666.67, DATE_SUB(CURDATE(), INTERVAL 9 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 9 MONTH)),
(4, 15, 3666.67, DATE_SUB(CURDATE(), INTERVAL 8 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 8 MONTH)),
(4, 16, 3666.67, DATE_SUB(CURDATE(), INTERVAL 7 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 7 MONTH)),
(4, 17, 3666.67, DATE_SUB(CURDATE(), INTERVAL 6 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 6 MONTH)),
(4, 18, 3666.67, DATE_SUB(CURDATE(), INTERVAL 5 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 5 MONTH)),
(4, 19, 3666.67, DATE_SUB(CURDATE(), INTERVAL 4 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 4 MONTH)),
(4, 20, 3666.67, DATE_SUB(CURDATE(), INTERVAL 3 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 3 MONTH)),
(4, 21, 3666.67, DATE_SUB(CURDATE(), INTERVAL 2 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 2 MONTH)),
(4, 22, 3666.67, DATE_SUB(CURDATE(), INTERVAL 1 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 1 MONTH)),
(4, 23, 3666.67, CURDATE(), 'pagada', CURDATE()),
(4, 24, 3666.67, DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'pendiente', NULL);

-- CUOTAS CRÉDITO 5 (10 cuotas, 4 pagadas, 1 vencida hace 15 días, 5 pendientes) - MOROSO LEVE
INSERT INTO cuotas (id_credito, numero_cuota, monto_cuota, fecha_vencimiento, estado, fecha_pago) VALUES
(5, 1, 2600.00, DATE_SUB(CURDATE(), INTERVAL 4 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 4 MONTH)),
(5, 2, 2600.00, DATE_SUB(CURDATE(), INTERVAL 3 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 3 MONTH)),
(5, 3, 2600.00, DATE_SUB(CURDATE(), INTERVAL 2 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 2 MONTH)),
(5, 4, 2600.00, DATE_SUB(CURDATE(), INTERVAL 1 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 1 MONTH)),
(5, 5, 2600.00, DATE_SUB(CURDATE(), INTERVAL 15 DAY), 'vencida', NULL),
(5, 6, 2600.00, DATE_ADD(CURDATE(), INTERVAL 15 DAY), 'pendiente', NULL),
(5, 7, 2600.00, DATE_ADD(CURDATE(), INTERVAL 1 MONTH) + INTERVAL 15 DAY, 'pendiente', NULL),
(5, 8, 2600.00, DATE_ADD(CURDATE(), INTERVAL 2 MONTH) + INTERVAL 15 DAY, 'pendiente', NULL),
(5, 9, 2600.00, DATE_ADD(CURDATE(), INTERVAL 3 MONTH) + INTERVAL 15 DAY, 'pendiente', NULL),
(5, 10, 2600.00, DATE_ADD(CURDATE(), INTERVAL 4 MONTH) + INTERVAL 15 DAY, 'pendiente', NULL);

-- CUOTAS CRÉDITO 6 (12 cuotas, 6 pagadas, 2 vencidas hace 45 días, 4 pendientes) - MOROSO MODERADO
INSERT INTO cuotas (id_credito, numero_cuota, monto_cuota, fecha_vencimiento, estado, fecha_pago) VALUES
(6, 1, 3500.00, DATE_SUB(CURDATE(), INTERVAL 7 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 7 MONTH)),
(6, 2, 3500.00, DATE_SUB(CURDATE(), INTERVAL 6 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 6 MONTH)),
(6, 3, 3500.00, DATE_SUB(CURDATE(), INTERVAL 5 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 5 MONTH)),
(6, 4, 3500.00, DATE_SUB(CURDATE(), INTERVAL 4 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 4 MONTH)),
(6, 5, 3500.00, DATE_SUB(CURDATE(), INTERVAL 3 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 3 MONTH)),
(6, 6, 3500.00, DATE_SUB(CURDATE(), INTERVAL 2 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 2 MONTH)),
(6, 7, 3500.00, DATE_SUB(CURDATE(), INTERVAL 45 DAY), 'vencida', NULL),
(6, 8, 3500.00, DATE_SUB(CURDATE(), INTERVAL 15 DAY), 'vencida', NULL),
(6, 9, 3500.00, DATE_ADD(CURDATE(), INTERVAL 15 DAY), 'pendiente', NULL),
(6, 10, 3500.00, DATE_ADD(CURDATE(), INTERVAL 1 MONTH) + INTERVAL 15 DAY, 'pendiente', NULL),
(6, 11, 3500.00, DATE_ADD(CURDATE(), INTERVAL 2 MONTH) + INTERVAL 15 DAY, 'pendiente', NULL),
(6, 12, 3500.00, DATE_ADD(CURDATE(), INTERVAL 3 MONTH) + INTERVAL 15 DAY, 'pendiente', NULL);

-- CUOTAS CRÉDITO 7 (18 cuotas, 8 pagadas, 3 vencidas hace 90, 60 y 30 días, 7 pendientes) - MOROSO GRAVE
INSERT INTO cuotas (id_credito, numero_cuota, monto_cuota, fecha_vencimiento, estado, fecha_pago) VALUES
(7, 1, 3600.00, DATE_SUB(CURDATE(), INTERVAL 11 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 11 MONTH)),
(7, 2, 3600.00, DATE_SUB(CURDATE(), INTERVAL 10 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 10 MONTH)),
(7, 3, 3600.00, DATE_SUB(CURDATE(), INTERVAL 9 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 9 MONTH)),
(7, 4, 3600.00, DATE_SUB(CURDATE(), INTERVAL 8 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 8 MONTH)),
(7, 5, 3600.00, DATE_SUB(CURDATE(), INTERVAL 7 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 7 MONTH)),
(7, 6, 3600.00, DATE_SUB(CURDATE(), INTERVAL 6 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 6 MONTH)),
(7, 7, 3600.00, DATE_SUB(CURDATE(), INTERVAL 5 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 5 MONTH)),
(7, 8, 3600.00, DATE_SUB(CURDATE(), INTERVAL 4 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 4 MONTH)),
(7, 9, 3600.00, DATE_SUB(CURDATE(), INTERVAL 90 DAY), 'vencida', NULL),
(7, 10, 3600.00, DATE_SUB(CURDATE(), INTERVAL 60 DAY), 'vencida', NULL),
(7, 11, 3600.00, DATE_SUB(CURDATE(), INTERVAL 30 DAY), 'vencida', NULL),
(7, 12, 3600.00, CURDATE(), 'pendiente', NULL),
(7, 13, 3600.00, DATE_ADD(CURDATE(), INTERVAL 1 MONTH), 'pendiente', NULL),
(7, 14, 3600.00, DATE_ADD(CURDATE(), INTERVAL 2 MONTH), 'pendiente', NULL),
(7, 15, 3600.00, DATE_ADD(CURDATE(), INTERVAL 3 MONTH), 'pendiente', NULL),
(7, 16, 3600.00, DATE_ADD(CURDATE(), INTERVAL 4 MONTH), 'pendiente', NULL),
(7, 17, 3600.00, DATE_ADD(CURDATE(), INTERVAL 5 MONTH), 'pendiente', NULL),
(7, 18, 3600.00, DATE_ADD(CURDATE(), INTERVAL 6 MONTH), 'pendiente', NULL);

-- CUOTAS CRÉDITO 8 (6 cuotas, TODAS PAGADAS) - EXITOSO
INSERT INTO cuotas (id_credito, numero_cuota, monto_cuota, fecha_vencimiento, estado, fecha_pago) VALUES
(8, 1, 3400.00, DATE_SUB(CURDATE(), INTERVAL 7 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 7 MONTH)),
(8, 2, 3400.00, DATE_SUB(CURDATE(), INTERVAL 6 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 6 MONTH)),
(8, 3, 3400.00, DATE_SUB(CURDATE(), INTERVAL 5 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 5 MONTH)),
(8, 4, 3400.00, DATE_SUB(CURDATE(), INTERVAL 4 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 4 MONTH)),
(8, 5, 3400.00, DATE_SUB(CURDATE(), INTERVAL 3 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 3 MONTH)),
(8, 6, 3400.00, DATE_SUB(CURDATE(), INTERVAL 2 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 2 MONTH));

-- CUOTAS CRÉDITO 9 (10 cuotas, TODAS PAGADAS) - EXITOSO
INSERT INTO cuotas (id_credito, numero_cuota, monto_cuota, fecha_vencimiento, estado, fecha_pago) VALUES
(9, 1, 3650.00, DATE_SUB(CURDATE(), INTERVAL 11 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 11 MONTH)),
(9, 2, 3650.00, DATE_SUB(CURDATE(), INTERVAL 10 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 10 MONTH)),
(9, 3, 3650.00, DATE_SUB(CURDATE(), INTERVAL 9 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 9 MONTH)),
(9, 4, 3650.00, DATE_SUB(CURDATE(), INTERVAL 8 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 8 MONTH)),
(9, 5, 3650.00, DATE_SUB(CURDATE(), INTERVAL 7 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 7 MONTH)),
(9, 6, 3650.00, DATE_SUB(CURDATE(), INTERVAL 6 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 6 MONTH)),
(9, 7, 3650.00, DATE_SUB(CURDATE(), INTERVAL 5 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 5 MONTH)),
(9, 8, 3650.00, DATE_SUB(CURDATE(), INTERVAL 4 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 4 MONTH)),
(9, 9, 3650.00, DATE_SUB(CURDATE(), INTERVAL 3 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 3 MONTH)),
(9, 10, 3650.00, DATE_SUB(CURDATE(), INTERVAL 2 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 2 MONTH));

-- CUOTAS CRÉDITO 10 (15 cuotas, 9 pagadas, 6 pendientes)
INSERT INTO cuotas (id_credito, numero_cuota, monto_cuota, fecha_vencimiento, estado, fecha_pago) VALUES
(10, 1, 3150.00, DATE_SUB(CURDATE(), INTERVAL 8 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 8 MONTH)),
(10, 2, 3150.00, DATE_SUB(CURDATE(), INTERVAL 7 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 7 MONTH)),
(10, 3, 3150.00, DATE_SUB(CURDATE(), INTERVAL 6 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 6 MONTH)),
(10, 4, 3150.00, DATE_SUB(CURDATE(), INTERVAL 5 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 5 MONTH)),
(10, 5, 3150.00, DATE_SUB(CURDATE(), INTERVAL 4 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 4 MONTH)),
(10, 6, 3150.00, DATE_SUB(CURDATE(), INTERVAL 3 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 3 MONTH)),
(10, 7, 3150.00, DATE_SUB(CURDATE(), INTERVAL 2 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 2 MONTH)),
(10, 8, 3150.00, DATE_SUB(CURDATE(), INTERVAL 1 MONTH), 'pagada', DATE_SUB(CURDATE(), INTERVAL 1 MONTH)),
(10, 9, 3150.00, CURDATE(), 'pagada', CURDATE()),
(10, 10, 3150.00, DATE_ADD(CURDATE(), INTERVAL 1 MONTH), 'pendiente', NULL),
(10, 11, 3150.00, DATE_ADD(CURDATE(), INTERVAL 2 MONTH), 'pendiente', NULL),
(10, 12, 3150.00, DATE_ADD(CURDATE(), INTERVAL 3 MONTH), 'pendiente', NULL),
(10, 13, 3150.00, DATE_ADD(CURDATE(), INTERVAL 4 MONTH), 'pendiente', NULL),
(10, 14, 3150.00, DATE_ADD(CURDATE(), INTERVAL 5 MONTH), 'pendiente', NULL),
(10, 15, 3150.00, DATE_ADD(CURDATE(), INTERVAL 6 MONTH), 'pendiente', NULL);

-- CUOTAS CRÉDITO 11 (8 cuotas, 1 pagada, 7 pendientes)
INSERT INTO cuotas (id_credito, numero_cuota, monto_cuota, fecha_vencimiento, estado, fecha_pago) VALUES
(11, 1, 2600.00, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'pagada', DATE_SUB(CURDATE(), INTERVAL 5 DAY)),
(11, 2, 2600.00, DATE_ADD(CURDATE(), INTERVAL 25 DAY), 'pendiente', NULL),
(11, 3, 2600.00, DATE_ADD(CURDATE(), INTERVAL 1 MONTH) + INTERVAL 25 DAY, 'pendiente', NULL),
(11, 4, 2600.00, DATE_ADD(CURDATE(), INTERVAL 2 MONTH) + INTERVAL 25 DAY, 'pendiente', NULL),
(11, 5, 2600.00, DATE_ADD(CURDATE(), INTERVAL 3 MONTH) + INTERVAL 25 DAY, 'pendiente', NULL),
(11, 6, 2600.00, DATE_ADD(CURDATE(), INTERVAL 4 MONTH) + INTERVAL 25 DAY, 'pendiente', NULL),
(11, 7, 2600.00, DATE_ADD(CURDATE(), INTERVAL 5 MONTH) + INTERVAL 25 DAY, 'pendiente', NULL),
(11, 8, 2600.00, DATE_ADD(CURDATE(), INTERVAL 6 MONTH) + INTERVAL 25 DAY, 'pendiente', NULL);

-- CUOTAS CRÉDITO 12 (6 cuotas, todas pendientes)
INSERT INTO cuotas (id_credito, numero_cuota, monto_cuota, fecha_vencimiento, estado, fecha_pago) VALUES
(12, 1, 3100.00, DATE_ADD(CURDATE(), INTERVAL 15 DAY), 'pendiente', NULL),
(12, 2, 3100.00, DATE_ADD(CURDATE(), INTERVAL 1 MONTH) + INTERVAL 15 DAY, 'pendiente', NULL),
(12, 3, 3100.00, DATE_ADD(CURDATE(), INTERVAL 2 MONTH) + INTERVAL 15 DAY, 'pendiente', NULL),
(12, 4, 3100.00, DATE_ADD(CURDATE(), INTERVAL 3 MONTH) + INTERVAL 15 DAY, 'pendiente', NULL),
(12, 5, 3100.00, DATE_ADD(CURDATE(), INTERVAL 4 MONTH) + INTERVAL 15 DAY, 'pendiente', NULL),
(12, 6, 3100.00, DATE_ADD(CURDATE(), INTERVAL 5 MONTH) + INTERVAL 15 DAY, 'pendiente', NULL);

-- CUOTAS CRÉDITO 13 (4 cuotas, todas pendientes - recién iniciado)
INSERT INTO cuotas (id_credito, numero_cuota, monto_cuota, fecha_vencimiento, estado, fecha_pago) VALUES
(13, 1, 3100.00, DATE_ADD(CURDATE(), INTERVAL 1 MONTH), 'pendiente', NULL),
(13, 2, 3100.00, DATE_ADD(CURDATE(), INTERVAL 2 MONTH), 'pendiente', NULL),
(13, 3, 3100.00, DATE_ADD(CURDATE(), INTERVAL 3 MONTH), 'pendiente', NULL),
(13, 4, 3100.00, DATE_ADD(CURDATE(), INTERVAL 4 MONTH), 'pendiente', NULL);

-- =====================================================
-- 4. REGISTRAR PAGOS (Con diferentes métodos)
-- =====================================================

-- Pagos de CRÉDITO 1 (Variedad de métodos)
INSERT INTO pagos (id_cuota, monto_pagado, fecha_pago, metodo_pago, observaciones) VALUES
(1, 4500.00, DATE_SUB(CURDATE(), INTERVAL 5 MONTH), 'efectivo', 'Pago puntual en oficina'),
(2, 4500.00, DATE_SUB(CURDATE(), INTERVAL 4 MONTH), 'transferencia', 'Transferencia banco Macro'),
(3, 4500.00, DATE_SUB(CURDATE(), INTERVAL 3 MONTH), 'tarjeta', 'Pago con tarjeta de débito'),
(4, 4500.00, DATE_SUB(CURDATE(), INTERVAL 2 MONTH), 'efectivo', NULL),
(5, 4500.00, DATE_SUB(CURDATE(), INTERVAL 1 MONTH), 'transferencia', 'Depósito bancario'),
(6, 4500.00, CURDATE(), 'cheque', 'Cheque banco Galicia N° 12345');

-- Pagos de CRÉDITO 2
INSERT INTO pagos (id_cuota, monto_pagado, fecha_pago, metodo_pago, observaciones) VALUES
(13, 5100.00, DATE_SUB(CURDATE(), INTERVAL 53 DAY), 'efectivo', 'Pago en efectivo'),
(14, 5100.00, DATE_SUB(CURDATE(), INTERVAL 23 DAY), 'transferencia', NULL);

-- Pagos de CRÉDITO 3
INSERT INTO pagos (id_cuota, monto_pagado, fecha_pago, metodo_pago, observaciones) VALUES
(16, 5200.00, DATE_SUB(CURDATE(), INTERVAL 135 DAY), 'efectivo', NULL),
(17, 5200.00, DATE_SUB(CURDATE(), INTERVAL 105 DAY), 'efectivo', NULL),
(18, 5200.00, DATE_SUB(CURDATE(), INTERVAL 75 DAY), 'transferencia', NULL),
(19, 5200.00, DATE_SUB(CURDATE(), INTERVAL 45 DAY), 'tarjeta', NULL),
(20, 5200.00, DATE_SUB(CURDATE(), INTERVAL 15 DAY), 'efectivo', NULL);

-- Pagos de CRÉDITO 4 (23 pagos en efectivo y transferencia alternados)
INSERT INTO pagos (id_cuota, monto_pagado, fecha_pago, metodo_pago, observaciones) VALUES
(21, 3666.67, DATE_SUB(CURDATE(), INTERVAL 22 MONTH), 'efectivo', NULL),
(22, 3666.67, DATE_SUB(CURDATE(), INTERVAL 21 MONTH), 'transferencia', NULL),
(23, 3666.67, DATE_SUB(CURDATE(), INTERVAL 20 MONTH), 'efectivo', NULL),
(24, 3666.67, DATE_SUB(CURDATE(), INTERVAL 19 MONTH), 'transferencia', NULL),
(25, 3666.67, DATE_SUB(CURDATE(), INTERVAL 18 MONTH), 'efectivo', NULL),
(26, 3666.67, DATE_SUB(CURDATE(), INTERVAL 17 MONTH), 'transferencia', NULL),
(27, 3666.67, DATE_SUB(CURDATE(), INTERVAL 16 MONTH), 'efectivo', NULL),
(28, 3666.67, DATE_SUB(CURDATE(), INTERVAL 15 MONTH), 'transferencia', NULL),
(29, 3666.67, DATE_SUB(CURDATE(), INTERVAL 14 MONTH), 'efectivo', NULL),
(30, 3666.67, DATE_SUB(CURDATE(), INTERVAL 13 MONTH), 'transferencia', NULL),
(31, 3666.67, DATE_SUB(CURDATE(), INTERVAL 12 MONTH), 'efectivo', NULL),
(32, 3666.67, DATE_SUB(CURDATE(), INTERVAL 11 MONTH), 'transferencia', NULL),
(33, 3666.67, DATE_SUB(CURDATE(), INTERVAL 10 MONTH), 'efectivo', NULL),
(34, 3666.67, DATE_SUB(CURDATE(), INTERVAL 9 MONTH), 'transferencia', NULL),
(35, 3666.67, DATE_SUB(CURDATE(), INTERVAL 8 MONTH), 'efectivo', NULL),
(36, 3666.67, DATE_SUB(CURDATE(), INTERVAL 7 MONTH), 'transferencia', NULL),
(37, 3666.67, DATE_SUB(CURDATE(), INTERVAL 6 MONTH), 'efectivo', NULL),
(38, 3666.67, DATE_SUB(CURDATE(), INTERVAL 5 MONTH), 'transferencia', NULL),
(39, 3666.67, DATE_SUB(CURDATE(), INTERVAL 4 MONTH), 'efectivo', NULL),
(40, 3666.67, DATE_SUB(CURDATE(), INTERVAL 3 MONTH), 'transferencia', NULL),
(41, 3666.67, DATE_SUB(CURDATE(), INTERVAL 2 MONTH), 'efectivo', NULL),
(42, 3666.67, DATE_SUB(CURDATE(), INTERVAL 1 MONTH), 'transferencia', NULL),
(43, 3666.67, CURDATE(), 'efectivo', 'Penúltima cuota pagada puntualmente');

-- Pagos de CRÉDITO 5 (Moroso leve)
INSERT INTO pagos (id_cuota, monto_pagado, fecha_pago, metodo_pago, observaciones) VALUES
(45, 2600.00, DATE_SUB(CURDATE(), INTERVAL 4 MONTH), 'efectivo', NULL),
(46, 2600.00, DATE_SUB(CURDATE(), INTERVAL 3 MONTH), 'efectivo', NULL),
(47, 2600.00, DATE_SUB(CURDATE(), INTERVAL 2 MONTH), 'transferencia', NULL),
(48, 2600.00, DATE_SUB(CURDATE(), INTERVAL 1 MONTH), 'efectivo', NULL);

-- Pagos de CRÉDITO 6 (Moroso moderado)
INSERT INTO pagos (id_cuota, monto_pagado, fecha_pago, metodo_pago, observaciones) VALUES
(55, 3500.00, DATE_SUB(CURDATE(), INTERVAL 7 MONTH), 'efectivo', NULL),
(56, 3500.00, DATE_SUB(CURDATE(), INTERVAL 6 MONTH), 'transferencia', NULL),
(57, 3500.00, DATE_SUB(CURDATE(), INTERVAL 5 MONTH), 'efectivo', NULL),
(58, 3500.00, DATE_SUB(CURDATE(), INTERVAL 4 MONTH), 'transferencia', NULL),
(59, 3500.00, DATE_SUB(CURDATE(), INTERVAL 3 MONTH), 'efectivo', NULL),
(60, 3500.00, DATE_SUB(CURDATE(), INTERVAL 2 MONTH), 'transferencia', NULL);

-- Pagos de CRÉDITO 7 (Moroso grave)
INSERT INTO pagos (id_cuota, monto_pagado, fecha
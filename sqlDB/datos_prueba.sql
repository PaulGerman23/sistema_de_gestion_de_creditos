USE almacen;

-- Insertar clientes de prueba
INSERT INTO clientes (nombre, apellido, dni, telefono, direccion, ciudad, email, estado, fecha_registro) VALUES
('Juan', 'Pérez', '12345678', '1122334455', 'Calle Falsa 123', 'Buenos Aires', 'juan.perez@email.com', 'activo', '2024-01-10'),
('María', 'Gómez', '87654321', '1133445566', 'Av. Siempre Viva 456', 'Córdoba', 'maria.gomez@email.com', 'activo', '2024-01-15'),
('Carlos', 'López', '11223344', '1144556677', 'Calle 42', 'Rosario', 'carlos.lopez@email.com', 'moroso', '2024-02-01'),
('Ana', 'Martínez', '55667788', '1155667788', 'Bv. Mitre 789', 'Mendoza', 'ana.martinez@email.com', 'activo', '2024-02-10'),
('Luis', 'Rodríguez', '99887766', '1166778899', 'Calle 13', 'La Plata', 'luis.rodriguez@email.com', 'inactivo', '2024-03-05');

-- Insertar créditos de prueba
INSERT INTO creditos (id_cliente, monto_total, cantidad_cuotas, cuota_mensual, interes_anual, fecha_inicio, fecha_vencimiento, estado, descripcion) VALUES
(1, 10000.00, 10, 1000.00, 12.00, '2024-01-10', '2024-11-10', 'activo', 'Crédito para electrodomésticos'),
(2, 5000.00, 5, 1000.00, 8.50, '2024-02-01', '2024-07-01', 'activo', 'Crédito para ropa'),
(3, 15000.00, 12, 1250.00, 15.00, '2024-02-01', '2025-02-01', 'moroso', 'Crédito para herramientas'),
(4, 3000.00, 3, 1000.00, 5.00, '2024-03-01', '2024-06-01', 'pagado', 'Crédito para libros'),
(5, 7500.00, 6, 1250.00, 10.00, '2024-03-01', '2024-09-01', 'vencido', 'Crédito para muebles');

-- Insertar cuotas generadas para cada crédito
INSERT INTO cuotas (id_credito, numero_cuota, monto_cuota, fecha_vencimiento, estado) VALUES
-- Crédito 1 (activo)
(1, 1, 1000.00, '2024-02-10', 'pagada'),
(1, 2, 1000.00, '2024-03-10', 'pagada'),
(1, 3, 1000.00, '2024-04-10', 'pendiente'),
(1, 4, 1000.00, '2024-05-10', 'pendiente'),
(1, 5, 1000.00, '2024-06-10', 'pendiente'),
(1, 6, 1000.00, '2024-07-10', 'pendiente'),
(1, 7, 1000.00, '2024-08-10', 'pendiente'),
(1, 8, 1000.00, '2024-09-10', 'pendiente'),
(1, 9, 1000.00, '2024-10-10', 'pendiente'),
(1, 10, 1000.00, '2024-11-10', 'pendiente'),

-- Crédito 2 (activo)
(2, 1, 1000.00, '2024-03-01', 'pagada'),
(2, 2, 1000.00, '2024-04-01', 'pagada'),
(2, 3, 1000.00, '2024-05-01', 'pendiente'),
(2, 4, 1000.00, '2024-06-01', 'pendiente'),
(2, 5, 1000.00, '2024-07-01', 'pendiente'),

-- Crédito 3 (moroso)
(3, 1, 1250.00, '2024-03-01', 'vencida'),
(3, 2, 1250.00, '2024-04-01', 'vencida'),
(3, 3, 1250.00, '2024-05-01', 'vencida'),
(3, 4, 1250.00, '2024-06-01', 'vencida'),
(3, 5, 1250.00, '2024-07-01', 'vencida'),
(3, 6, 1250.00, '2024-08-01', 'vencida'),
(3, 7, 1250.00, '2024-09-01', 'vencida'),
(3, 8, 1250.00, '2024-10-01', 'vencida'),
(3, 9, 1250.00, '2024-11-01', 'vencida'),
(3, 10, 1250.00, '2024-12-01', 'vencida'),
(3, 11, 1250.00, '2025-01-01', 'vencida'),
(3, 12, 1250.00, '2025-02-01', 'vencida'),

-- Crédito 4 (pagado)
(4, 1, 1000.00, '2024-04-01', 'pagada'),
(4, 2, 1000.00, '2024-05-01', 'pagada'),
(4, 3, 1000.00, '2024-06-01', 'pagada'),

-- Crédito 5 (vencido)
(5, 1, 1250.00, '2024-04-01', 'pagada'),
(5, 2, 1250.00, '2024-05-01', 'vencida'),
(5, 3, 1250.00, '2024-06-01', 'vencida'),
(5, 4, 1250.00, '2024-07-01', 'vencida'),
(5, 5, 1250.00, '2024-08-01', 'vencida'),
(5, 6, 1250.00, '2024-09-01', 'vencida');

-- Insertar pagos de prueba
INSERT INTO pagos (id_cuota, monto_pagado, fecha_pago, metodo_pago) VALUES
-- Pagos del crédito 1
(1, 1000.00, '2024-02-10', 'efectivo'),
(2, 1000.00, '2024-03-10', 'transferencia'),

-- Pagos del crédito 2
(11, 1000.00, '2024-03-01', 'efectivo'),
(12, 1000.00, '2024-04-01', 'transferencia'),

-- Pagos del crédito 4 (completamente pagado)
(16, 1000.00, '2024-04-01', 'efectivo'),
(17, 1000.00, '2024-05-01', 'efectivo'),
(18, 1000.00, '2024-06-01', 'transferencia'),

-- Pagos del crédito 5
(21, 1250.00, '2024-04-01', 'efectivo');

-- Insertar moras de prueba
INSERT INTO moras (id_credito, monto_mora, fecha_aplicacion) VALUES
(3, 62.50, '2024-03-05'),
(3, 62.50, '2024-04-05'),
(3, 62.50, '2024-05-05'),
(5, 62.50, '2024-05-05'),
(5, 62.50, '2024-06-05');

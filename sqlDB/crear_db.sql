CREATE DATABASE IF NOT EXISTS almacen;
USE almacen;

-- EJECUTAR EL SIGUIENTE SQL:
-- Tabla de clientes
CREATE TABLE clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    dni VARCHAR(20) UNIQUE NOT NULL,
    telefono VARCHAR(20),
    direccion VARCHAR(255),
    ciudad VARCHAR(100),
    email VARCHAR(100),
    estado ENUM('activo', 'inactivo', 'moroso') DEFAULT 'activo',
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de créditos
CREATE TABLE creditos (
    id_credito INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    monto_total DECIMAL(10,2) NOT NULL,
    cantidad_cuotas INT NOT NULL,
    cuota_mensual DECIMAL(10,2) NOT NULL,
    interes_anual DECIMAL(5,2) DEFAULT 0,
    fecha_inicio DATE NOT NULL,
    fecha_vencimiento DATE NOT NULL,
    descripcion TEXT,
    estado ENUM('activo', 'pagado', 'moroso') DEFAULT 'activo',
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE
);

-- Tabla de cuotas
CREATE TABLE cuotas (
    id_cuota INT AUTO_INCREMENT PRIMARY KEY,
    id_credito INT NOT NULL,
    numero_cuota INT NOT NULL,
    monto_cuota DECIMAL(10,2) NOT NULL,
    fecha_vencimiento DATE NOT NULL,
    fecha_pago DATETIME,
    estado ENUM('pendiente', 'pagada', 'vencida') DEFAULT 'pendiente',
    FOREIGN KEY (id_credito) REFERENCES creditos(id_credito) ON DELETE CASCADE
);

-- Tabla de pagos
CREATE TABLE pagos (
    id_pago INT AUTO_INCREMENT PRIMARY KEY,
    id_cuota INT NOT NULL,
    monto_pagado DECIMAL(10,2) NOT NULL,
    fecha_pago DATETIME DEFAULT CURRENT_TIMESTAMP,
    metodo_pago ENUM('efectivo', 'transferencia', 'tarjeta', 'cheque', 'otro') DEFAULT 'efectivo',
    observaciones TEXT,
    FOREIGN KEY (id_cuota) REFERENCES cuotas(id_cuota) ON DELETE CASCADE
);

-- Tabla de moras
CREATE TABLE moras (
    id_mora INT AUTO_INCREMENT PRIMARY KEY,
    id_credito INT NOT NULL,
    id_cuota INT NULL,
    monto_mora DECIMAL(10,2) NOT NULL,
    fecha_aplicacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    dias_vencidos INT DEFAULT 0,
    pagada BOOLEAN DEFAULT FALSE,
    fecha_pago DATETIME NULL,
    observaciones TEXT NULL,
    FOREIGN KEY (id_credito) REFERENCES creditos(id_credito) ON DELETE CASCADE,
    FOREIGN KEY (id_cuota) REFERENCES cuotas(id_cuota) ON DELETE SET NULL
);

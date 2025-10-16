CREATE DATABASE IF NOT EXISTS almacen;
USE almacen;

CREATE TABLE clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100),
    dni VARCHAR(20) UNIQUE NOT NULL,
    telefono VARCHAR(15),
    direccion VARCHAR(255),
    ciudad VARCHAR(50),
    email VARCHAR(100),
    estado ENUM('activo', 'inactivo', 'moroso') DEFAULT 'activo',
    fecha_registro DATE
);

CREATE TABLE creditos (
    id_credito INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    monto_total DECIMAL(10,2) NOT NULL,
    cantidad_cuotas INT NOT NULL,
    cuota_mensual DECIMAL(10,2),
    interes_anual DECIMAL(5,2),
    fecha_inicio DATE,
    fecha_vencimiento DATE,
    estado ENUM('activo', 'vencido', 'pagado', 'moroso') DEFAULT 'activo',
    descripcion TEXT,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
);

CREATE TABLE cuotas (
    id_cuota INT AUTO_INCREMENT PRIMARY KEY,
    id_credito INT NOT NULL,
    numero_cuota INT NOT NULL,
    monto_cuota DECIMAL(10,2),
    fecha_vencimiento DATE,
    estado ENUM('pendiente', 'pagada', 'vencida') DEFAULT 'pendiente',
    fecha_pago DATE NULL,
    FOREIGN KEY (id_credito) REFERENCES creditos(id_credito)
);

CREATE TABLE pagos (
    id_pago INT AUTO_INCREMENT PRIMARY KEY,
    id_cuota INT NOT NULL,
    monto_pagado DECIMAL(10,2),
    fecha_pago DATE,
    metodo_pago VARCHAR(50),
    FOREIGN KEY (id_cuota) REFERENCES cuotas(id_cuota)
);

CREATE TABLE moras (
    id_mora INT AUTO_INCREMENT PRIMARY KEY,
    id_credito INT NOT NULL,
    monto_mora DECIMAL(10,2),
    fecha_aplicacion DATE,
    FOREIGN KEY (id_credito) REFERENCES creditos(id_credito)
);

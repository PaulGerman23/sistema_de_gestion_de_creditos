# 📋 RESUMEN COMPLETO DEL PROYECTO
## Sistema de Gestión de Clientes y Créditos con Bootstrap Template SB Admin 2

---

## 🎯 Objetivo General
Integración completa del template SB Admin 2 de Bootstrap con un sistema de gestión de créditos: https://github.com/PaulGerman23/clientes_creditos funcional, implementando todas las secciones del sistema original con una interfaz moderna, profesional y responsive.

---

## 📦 ARCHIVOS CREADOS Y COMPLETADOS

### **ESTRUCTURA PRINCIPAL**

```
proyecto/
├── index.php                          ✅ Dashboard principal
├── conexion.php                       ✅ Configuración BD (EXISTENTE)
├── includes/
│   ├── header.php                     ✅ Layout header reutilizable
│   ├── footer.php                     ✅ Layout footer reutilizable
│   └── verificar_login.php            ✅ Verificación de sesión
│
├── vendor/                            📥 DEL TEMPLATE
│   ├── bootstrap/
│   ├── jquery/
│   ├── fontawesome-free/
│   └── datatables/
│
├── css/                               📥 DEL TEMPLATE
│   └── sb-admin-2.min.css
│
├── js/                                📥 DEL TEMPLATE
│   └── sb-admin-2.min.js
│
├── img/                               📥 DEL TEMPLATE
│   └── undraw_profile.svg (y otros)
│
├── clientes/
│   ├── listar_clientes.php            ✅ Listado con DataTables
│   ├── registrar_cliente.php          ✅ Formulario con validación
│   ├── editar_cliente.php             ✅ Formulario de edición
│   ├── eliminar_cliente.php           ✅ Eliminación de cliente
│   ├── buscar_cliente.php             ✅ Búsqueda avanzada
│   └── historial_cliente.php          ⚠️ Incompleto (mejorar)
│
├── creditos/
│   ├── ver_creditos.php               ✅ Listado con estadísticas
│   ├── registrar_credito.php          ✅ Formulario + simulador
│   ├── detalle_credito.php            ✅ Detalle completo del crédito
│   ├── actualizar_estado_credito.php  ✅ Actualización de estados
│   └── calcular_cuotas.php            ✅ Cálculo de cuotas
│
├── cuotas/
│   ├── ver_cuotas_cliente.php         ✅ Visualización por cliente
│   ├── pagar_cuota.php                ✅ Registro de pagos
│   ├── generar_plan_pago.php          ✅ Plan de pagos profesional
│   └── exportar_cuotas.php            ⚠️ Por implementar
│
├── pagos/
│   ├── historial_pagos.php            ✅ Historial completo
│   ├── comprobante_pago.php           ✅ Comprobante imprimible
│   └── registrar_pago.php             ⚠️ Deprecado (usar pagar_cuota.php)
│
├── alertas/
│   └── alertas_vencimientos.php       ✅ Sistema de alertas
│
├── moras/
│   └── aplicar_mora.php               ✅ Sistema de moras automáticas
│
└── database/
    └── moras_table.sql                ✅ Script SQL para moras
```

---

## 🚀 PASOS DE INSTALACIÓN

### **PASO 1: Descargar el Template y El sistema Base**
```bash
#Clonar desde GitHub en Sistema Base
git clone ttps://github.com/PaulGerman23/clientes_creditos

# Opción A: Clonar desde GitHub
git clone https://github.com/startbootstrap/startbootstrap-sb-admin-2.git

# Opción B: Descargar como ZIP
# Ir a https://github.com/startbootstrap/startbootstrap-sb-admin-2
# Descargar y extraer el archivo
#
```

### **PASO 2: Estructura de Carpetas**
```
tu_proyecto_xampp/htdocs/
└── sistema_creditos/
    ├── vendor/        (Copiar del template)
    ├── css/           (Copiar del template)
    ├── js/            (Copiar del template)
    ├── img/           (Copiar del template)
    ├── scss/          (Copiar del template - opcional)
    └── [resto de archivos]
```

### **PASO 3: Crear Base de Datos**
### Ejecutar los sql a continuacion o copiar y pegar el contenido de los archivos sql de la carpeta sqlDB en phpMyAdmin
```sql
CREATE DATABASE IF NOT EXISTS almacen;
USE almacen;

-- EJECUTAR EL SIGUIENTE SQL:
```

**Copiar y ejecutar en phpMyAdmin:**
```sql
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

-- Datos de prueba
INSERT INTO clientes (nombre, apellido, dni, telefono, ciudad, email, estado) VALUES
('Juan', 'Pérez', '12345678', '3814567890', 'Salta', 'juan@example.com', 'activo'),
('María', 'González', '87654321', '3815678901', 'Salta', 'maria@example.com', 'activo'),
('Carlos', 'Rodríguez', '11223344', '3816789012', 'Jujuy', 'carlos@example.com', 'activo');
```

### **PASO 4: Configurar conexión.php**
```php
<?php
$host = "localhost";
$user = "root";        // Usuario por defecto XAMPP
$pass = "";            // Sin contraseña por defecto
$db = "almacen";       // Nombre BD

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>
```

### **PASO 5: Configurar URL Base**
En **includes/header.php** y **includes/footer.php**, la variable `$base_url` se ajusta automáticamente según la carpeta:

```php
// En archivos dentro de carpetas:
$base_url = '../';

// En index.php (raíz):
$base_url = './';  // O no declarar y usar rutas relativas
```

---

## 📋 SECCIONES IMPLEMENTADAS

### **1. DASHBOARD (index.php)**
**Características:**
- ✅ 4 tarjetas con estadísticas en tiempo real
- ✅ Tabla de créditos recientes
- ✅ Alertas de vencimiento próximo
- ✅ Sidebar navegable
- ✅ Topbar con notificaciones

**Elementos:**
```
- Total de clientes
- Créditos activos
- Créditos morosos (con contador)
- Monto total en créditos
- Últimos 5 créditos registrados
- Próximas 5 cuotas a vencer
```

---

### **2. GESTIÓN DE CLIENTES**

#### **2.1 Listar Clientes (clientes/listar_clientes.php)**
- ✅ Tabla con DataTables (búsqueda, ordenamiento, paginación)
- ✅ Información completa del cliente
- ✅ Badges de estado
- ✅ Botones de acción (editar, historial, eliminar)
- ✅ 25 registros por página

#### **2.2 Registrar Cliente (clientes/registrar_cliente.php)**
- ✅ Formulario con validación
- ✅ Validación de DNI duplicado
- ✅ Campos: nombre, apellido, DNI, teléfono, dirección, ciudad, email
- ✅ Estado automático: 'activo'
- ✅ Mensajes de éxito/error
- ✅ Sidebar informativo

#### **2.3 Editar Cliente (clientes/editar_cliente.php)**
- ✅ Carga datos actuales
- ✅ Formulario pre-llenado
- ✅ Cambio de estado (activo, inactivo, moroso)
- ✅ Actualización en BD
- ✅ Validaciones

#### **2.4 Buscar Cliente (clientes/buscar_cliente.php)**
- ✅ Búsqueda múltiple (nombre, DNI, teléfono, email)
- ✅ Filtro por ciudad
- ✅ Filtro por estado
- ✅ Estadísticas de resultados
- ✅ Búsquedas rápidas (botones)
- ✅ Vista inicial informativa

#### **2.5 Eliminar Cliente (clientes/eliminar_cliente.php)**
- ✅ Confirmación antes de eliminar
- ✅ Eliminación en cascada de créditos y cuotas
- ✅ Mensaje de confirmación

---

### **3. GESTIÓN DE CRÉDITOS**

#### **3.1 Ver Créditos (creditos/ver_creditos.php)**
- ✅ 4 tarjetas de estadísticas
  - Total de créditos
  - Créditos activos
  - Créditos morosos
  - Monto total activo
- ✅ Tabla con todos los créditos
- ✅ Barra de progreso visual por crédito
- ✅ Badges de estado (activo, moroso, pagado)
- ✅ Cuotas pagadas vs total
- ✅ DataTables integradas
- ✅ Botones de acción:
  - Ver plan de pago
  - Ver detalle completo
  - Actualizar estado

#### **3.2 Registrar Crédito (creditos/registrar_credito.php)**
- ✅ Formulario completo con:
  - Selección de cliente activo
  - Monto total
  - Cantidad de cuotas (1-120)
  - Interés anual (0-100%)
  - Descripción
- ✅ **SIMULADOR EN TIEMPO REAL** de cuota
- ✅ Cálculo de cuota francesa (con interés) o simple
- ✅ Generación automática de todas las cuotas
- ✅ Cálculo automático de fechas de vencimiento
- ✅ Vista previa de totales
- ✅ Sidebar con información

#### **3.3 Detalle del Crédito (creditos/detalle_credito.php)**
- ✅ Información completa del cliente
- ✅ 4 tarjetas de métricas:
  - Monto total
  - Total pagado
  - Saldo pendiente
  - Cuota mensual
- ✅ 2 barras de progreso:
  - Por monto
  - Por cuotas
- ✅ Tabla detallada de cuotas
- ✅ Estados visuales (pagada, pendiente, vencida)
- ✅ Cálculo de días restantes/vencidos
- ✅ Botón directo para pagar
- ✅ Botones de acción (ver plan, imprimir)

#### **3.4 Plan de Pago (cuotas/generar_plan_pago.php)**
- ✅ Encabezado profesional
- ✅ 4 tarjetas resumen (capital, intereses, cuota, total)
- ✅ Tabla completa de cuotas
- ✅ Código de colores por estado
- ✅ Totales calculados automáticamente
- ✅ Optimizado para impresión
- ✅ Botones: imprimir, volver, exportar PDF
- ✅ Leyenda explicativa

#### **3.5 Actualizar Estados (creditos/actualizar_estado_credito.php)**
- ✅ Actualización individual o masiva
- ✅ Lógica automática de estados:
  - **ACTIVO**: Cuotas pendientes sin vencidas
  - **MOROSO**: Al menos una cuota vencida
  - **PAGADO**: Todas las cuotas pagadas
- ✅ Marca automáticamente cuotas vencidas
- ✅ Lista de créditos que requieren atención
- ✅ Estadísticas del sistema
- ✅ Acciones rápidas

---

### **4. GESTIÓN DE CUOTAS Y PAGOS**

#### **4.1 Ver Cuotas por Cliente (cuotas/ver_cuotas_cliente.php)**
- ✅ Selector de cliente con todos los que tienen créditos
- ✅ 4 tarjetas de estadísticas:
  - Total de cuotas
  - Pagadas (con monto)
  - Pendientes (con monto)
  - Vencidas
- ✅ Barra de progreso de pago
- ✅ Filtro por estado (todos, pendientes, pagadas, vencidas)
- ✅ Tabla con DataTables
- ✅ Información de cliente destacada
- ✅ Botones: pagar, ver detalle
- ✅ Opciones de exportación

#### **4.2 Pagar Cuota (cuotas/pagar_cuota.php)**
- ✅ Selector inteligente de cuotas pendientes/vencidas
- ✅ Auto-completado de monto
- ✅ Información dinámica de la cuota
- ✅ Múltiples métodos de pago:
  - Efectivo
  - Transferencia bancaria
  - Tarjeta de crédito/débito
  - Cheque
  - Otro
- ✅ Campo de observaciones
- ✅ Validación JavaScript completa
- ✅ Confirmación obligatoria
- ✅ Actualización automática de estados
- ✅ Sidebar informativo con pasos y alertas

#### **4.3 Historial de Pagos (pagos/historial_pagos.php)**
- ✅ Filtros avanzados:
  - Rango de fechas
  - Método de pago
  - Búsqueda por cliente
- ✅ 4 tarjetas de estadísticas:
  - Total recaudado
  - Cantidad de pagos
  - Monto en efectivo (%)
  - Monto en transferencias (%)
- ✅ Tabla completa con DataTables
- ✅ Badges de colores por método
- ✅ Observaciones con tooltips
- ✅ Botones de acción:
  - Ver crédito
  - Ver comprobante
- ✅ Distribución por método con barras de progreso
- ✅ Top 5 clientes que más pagaron con ranking

#### **4.4 Comprobante de Pago (pagos/comprobante_pago.php)**
- ✅ Diseño profesional y elegante
- ✅ Header con gradiente azul
- ✅ Sello de "PAGADO" con marca de agua
- ✅ 3 secciones: Cliente, Crédito, Pago
- ✅ Monto destacado en card verde
- ✅ Espacio para firma
- ✅ CSS optimizado para impresión
- ✅ Botones: imprimir, cerrar
- ✅ Número de comprobante formateado

---

### **5. SISTEMA DE ALERTAS**

#### **5.1 Alertas de Vencimiento (alertas/alertas_vencimientos.php)**
- ✅ 4 tarjetas de estadísticas:
  - Créditos por vencer
  - Cuotas por vencer (con monto)
  - Cuotas vencidas (con monto)
  - Total en riesgo
- ✅ **4 TABS ORGANIZADAS:**
  1. **Cuotas Vencidas** (Prioridad Alta)
  2. **Cuotas por Vencer** (Próximos 3-30 días)
  3. **Créditos por Vencer** (Completos)
  4. **Clientes Críticos** (Top 5 morosos)

- ✅ Código de colores por prioridad:
  - 🔴 Rojo: > 30 días vencida
  - 🟡 Amarillo: 15-30 días vencida
  - 🔵 Azul: Normal / Por vencer

- ✅ Botones de contacto directo:
  - Llamada telefónica (tel:)
  - Email (mailto:)
  - WhatsApp (manual)

- ✅ Acciones rápidas:
  - Cobrar cuota
  - Ver detalle crédito
  - Ver cuotas cliente

- ✅ Auto-refresh cada 5 minutos
- ✅ Información contextual
- ✅ Recomendaciones de gestión
- ✅ Vista optimizada para impresión

#### **5.2 Aplicar Moras (moras/aplicar_mora.php)**
- ✅ 4 Estadísticas clave:
  - Cuotas pendientes para aplicar mora
  - Moras pendientes de pago
  - Moras aplicadas hoy
  - Total histórico

- ✅ Proceso automático:
  - Identifica cuotas vencidas
  - Calcula porcentaje (5% configurable)
  - Aplica mora automáticamente
  - Actualiza estado de cuota
  - Actualiza estado de crédito

- ✅ Validaciones:
  - No duplica moras
  - Verifica vencimiento
  - Registra fecha/hora
  - Guarda días vencidos

- ✅ Resultados en tiempo real
- ✅ Tabla de moras aplicadas
- ✅ Historial con DataTables
- ✅ Información educativa
- ✅ Ejemplo de cálculo

---

## 🎨 CARACTERÍSTICAS DE DISEÑO

### **Template Utilizado: SB Admin 2**
- ✅ Bootstrap 4
- ✅ Font Awesome Icons
- ✅ Responsive Design
- ✅ Sidebar colapsable
- ✅ Topbar con notificaciones
- ✅ DataTables integradas
- ✅ Tema azul profesional

### **Componentes Principales**

#### **Layout Base (includes/header.php + includes/footer.php)**
```php
// Variables para configurar cada página:
$base_url = '../';              // Ruta a raíz
$page_title = 'Título Página';  // Título pestaña
$active_page = 'clientes';      // Sección activa
$active_subpage = 'listar';     // Subsección activa
$extra_css = '';                // CSS adicional
$extra_js = '';                 // JS adicional
```

#### **Sidebar Dinámico**
- Menu colapsable por secciones
- Indicadores de página activa
- Submenus organizados
- Acceso rápido a principales funciones

#### **Topbar Inteligente**
- Busqueda global (opcional)
- Notificaciones (contador de créditos morosos)
- Información de usuario
- Dropdown con opciones

#### **Tarjetas de Estadísticas**
- Border-left coloreado
- Icono Font Awesome
- Valor destacado (h5)
- Subtítulo informativo
- Responsive (4 columnas desktop, 1 móvil)

#### **DataTables**
- Búsqueda integrada
- Ordenamiento por columnas
- Paginación (25 registros por defecto)
- Idioma español
- Exportación de datos

#### **Badges de Estado**
- Verde (success): Activo, Pagado
- Rojo (danger): Moroso, Vencida
- Amarillo (warning): Pendiente, Inactivo
- Azul (info): Información general

---

## 🔧 FUNCIONALIDADES TÉCNICAS

### **Cálculos Implementados**

#### **1. Cuota Francesa (Con Interés)**
```javascript
// JavaScript en el navegador (tiempo real)
cuota = monto * (i * (1 + i)^n) / ((1 + i)^n - 1)
donde:
  i = interés mensual = (interes_anual / 12) / 100
  n = cantidad de cuotas
```

#### **2. Cuota Simple (Sin Interés)**
```javascript
cuota = monto / cantidad_cuotas
```

#### **3. Progreso del Crédito**
```javascript
por_monto = (total_pagado / monto_total) * 100
por_cuotas = (cuotas_pagadas / total_cuotas) * 100
```

#### **4. Días Restantes/Vencidos**
```sql
dias_restantes = DATEDIFF(fecha_vencimiento, CURDATE())
dias_vencidos = DATEDIFF(CURDATE(), fecha_vencimiento)
```

### **Consultas SQL Optimizadas**

#### **Prepared Statements (Seguridad)**
```php
$stmt = $conn->prepare("SELECT * FROM clientes WHERE id_cliente = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
```

#### **JOINs Múltiples**
```sql
SELECT * FROM pagos p
JOIN cuotas cu ON p.id_cuota = cu.id_cuota
JOIN creditos cr ON cu.id_credito = cr.id_credito
JOIN clientes cl ON cr.id_cliente = cl.id_cliente
```

#### **Subconsultas para Estadísticas**
```sql
SELECT COUNT(*) as cuotas_pagadas FROM cuotas 
WHERE id_credito = cr.id_credito AND estado = 'pagada'
```

#### **GROUP BY para Reportes**
```sql
SELECT metodo_pago, SUM(monto_pagado) as total, COUNT(*) as cantidad
FROM pagos
GROUP BY metodo_pago
ORDER BY total DESC
```

---

## 📱 RESPONSIVE DESIGN

### **Mobile (< 768px)**
- ✅ Columnas apiladas verticalmente
- ✅ Tablas con scroll horizontal
- ✅ Botones de tamaño completo
- ✅ Sidebar ocultable
- ✅ Menú hamburguesa

### **Tablet (768px - 1199px)**
- ✅ 2 columnas en grids
- ✅ Tarjetas en 2 columnas
- ✅ Navegación condensada
- ✅ Formularios en 2 columnas

### **Desktop (1200px+)**
- ✅ 4 columnas completas
- ✅ Sidebar visible
- ✅ Tablas anchas
- ✅ Dashboard completo

---

## 🔐 SEGURIDAD IMPLEMENTADA

### **Backend**
- ✅ **Prepared Statements**: Previene SQL Injection
- ✅ **htmlspecialchars()**: Previene XSS
- ✅ **Validación de datos**: Verificación en servidor
- ✅ **Control de tipos**: bind_param tipificado
- ✅ **Validación de existencia**: Verificar registros antes de operar

### **Frontend**
- ✅ **Confirmación de acciones**: Borrados, moras, pagos
- ✅ **Validación HTML5**: required, type, min, max
- ✅ **JavaScript**: Validaciones adicionales
- ✅ **Mensajes de error**: Feedback claro al usuario

### **Base de Datos**
- ✅ **Claves foráneas**: Referencial integrity
- ✅ **Eliminación en cascada**: Mantiene consistencia
- ✅ **Índices**: Optimiza búsquedas
- ✅ **Tipos de datos**: Correcta tipificación

---

## 🎯 FLUJOS DE TRABAJO PRINCIPALES

### **Flujo 1: Registrar un Crédito**
```
1. Ir a Créditos > Registrar Crédito
2. Seleccionar cliente activo
3. Ingresar monto y plazo
4. Ver simulación de cuota en tiempo real
5. Confirmar y generar
6. Sistema crea automáticamente todas las cuotas
7. Redirige a plan de pago generado
```

### **Flujo 2: Registrar un Pago**
```
1. Ir a Cuotas > Registrar Pago
2. Seleccionar cuota pendiente/vencida
3. Se auto-completa el monto
4. Seleccionar método de pago
5. Agregar observaciones (opcional)
6. Confirmar recibimiento del pago
7. Sistema actualiza cuota a "pagada"
8. Actualiza estado del crédito automáticamente
```

### **Flujo 3: Gestionar Alertas**
```
1. Revisar Dashboard (alertas en topbar)
2. Ir a Alertas > Alertas de Vencimiento
3. Ver 4 tabs organizadas por prioridad
4. Contactar clientes (tel/email)
5. Registrar pagos o aplicar moras
6. Historial automáticamente actualizado
```

### **Flujo 4: Aplicar Moras Automáticas**
```
1. Ir a Alertas > Aplicar Moras
2. Revisar cuotas pendientes
3. Confirmar acción
4. Sistema aplica 5% a todas las cuotas vencidas
5. Ver resultados en tabla
6. Consultar historial de moras
```

---

## 📊 ESTADÍSTICAS Y MÉTRICAS

### **Dashboard**
- Total de clientes
- Créditos activos
- Créditos morosos
- Monto total en créditos
- Últimos créditos
- Próximas cuotas

### **Créditos**
- Total, activos, morosos, pagados
- Monto total activo
- Progreso por crédito
- Cuotas pagadas vs total

### **Cuotas**
- Total, pagadas, pendientes, vencidas
- Monto pagado vs pendiente
- Progreso del cliente
- Días restantes/vencidos

### **Pagos**
- Total recaudado
- Cantidad de transacciones
- Distribución por método
- Top clientes pagadores
- Porcentajes

### **Alertas**
- Créditos por vencer
- Cuotas por vencer
- Cuotas vencidas
- Total en riesgo
- Clientes críticos

---

## 🚀 MEJORAS IMPLEMENTADAS

### Comparativa: Sistema Original vs Con Template

| Aspecto | Antes | Ahora |
|--------|-------|-------|
| **Interfaz** | HTML básico | Bootstrap 4 profesional |
| **Sidebar** | Lista simple | Menú colapsable dinámico |
| **Tablas** | HTML estándar | DataTables con búsqueda |
| **Formularios** | Campos simples | Validación + feedback |
| **Estadísticas** | Ninguna | 8+ métricas por página |
| **Alertas** | Lista simple | 4 tabs con prioridades |
| **Moras** | Manual | Aplicación automática |
| **Reportes** | Ninguno | Imprimibles y exportables |
| **Responsive** | No | Completamente adaptable |
| **Diseño** | Básico | Profesional moderno |

---

## 📚 ARCHIVOS CLAVE A ENTENDER

### **1. includes/header.php**
- Layout reutilizable
- Sidebar dinámico
- Topbar con notificaciones
- Variables de configuración

### **2. includes/footer.

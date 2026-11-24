-- ========================================================================
-- PROYECTO: El Grano Dorado (Foco Administrativo)
-- MOTOR: MySQL (8.0+)
-- ========================================================================

-- 1. CREACIÓN DE LA BASE DE DATOS
CREATE DATABASE IF NOT EXISTS ElGranoDorado
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE ElGranoDorado;

-- ========================================================================
-- 2. CREACIÓN DE TABLAS Y RESTRICCIONES (CONSTRAINTS)
-- ========================================================================

CREATE TABLE Categorias (
    IdCategoria INT AUTO_INCREMENT PRIMARY KEY,
    Nombre VARCHAR(50) NOT NULL,
    Descripcion VARCHAR(150) NULL
);

CREATE TABLE Productos (
    IdProducto INT AUTO_INCREMENT PRIMARY KEY,
    IdCategoria INT NOT NULL,
    Nombre VARCHAR(100) NOT NULL,
    Descripcion VARCHAR(255) NULL,
    Precio DECIMAL(10,2) NOT NULL CHECK (Precio >= 0),
    Estado BOOLEAN DEFAULT TRUE,
    CONSTRAINT FK_Productos_Categorias FOREIGN KEY (IdCategoria) REFERENCES Categorias(IdCategoria)
);

CREATE TABLE Roles (
    IdRol INT AUTO_INCREMENT PRIMARY KEY,
    Nombre VARCHAR(50) NOT NULL,
    NivelAcceso INT NOT NULL CHECK (NivelAcceso BETWEEN 1 AND 10)
);

CREATE TABLE Usuarios (
    IdUsuario INT AUTO_INCREMENT PRIMARY KEY,
    IdRol INT NOT NULL,
    Nombres VARCHAR(100) NOT NULL,
    Apellidos VARCHAR(100) NOT NULL,
    Email VARCHAR(100) UNIQUE NOT NULL,
    Activo BOOLEAN DEFAULT TRUE,
    CONSTRAINT FK_Usuarios_Roles FOREIGN KEY (IdRol) REFERENCES Roles(IdRol)
);

CREATE TABLE Pedidos (
    IdPedido INT AUTO_INCREMENT PRIMARY KEY,
    IdUsuario INT NOT NULL,
    FechaPedido DATETIME DEFAULT CURRENT_TIMESTAMP,
    Total DECIMAL(10,2) NOT NULL DEFAULT 0 CHECK (Total >= 0),
    MetodoPago VARCHAR(50) DEFAULT 'Efectivo',
    CONSTRAINT FK_Pedidos_Usuarios FOREIGN KEY (IdUsuario) REFERENCES Usuarios(IdUsuario)
);

CREATE TABLE DetallePedidos (
    IdDetalle INT AUTO_INCREMENT PRIMARY KEY,
    IdPedido INT NOT NULL,
    IdProducto INT NOT NULL,
    Cantidad INT NOT NULL CHECK (Cantidad > 0),
    PrecioUnitario DECIMAL(10,2) NOT NULL CHECK (PrecioUnitario >= 0),
    Subtotal DECIMAL(10,2) GENERATED ALWAYS AS (Cantidad * PrecioUnitario) STORED,
    CONSTRAINT FK_Detalle_Pedidos FOREIGN KEY (IdPedido) REFERENCES Pedidos(IdPedido),
    CONSTRAINT FK_Detalle_Productos FOREIGN KEY (IdProducto) REFERENCES Productos(IdProducto)
);

-- ========================================================================
-- 3. CREACIÓN DE ÍNDICES
-- ========================================================================
CREATE INDEX IX_Productos_Nombre ON Productos(Nombre);
CREATE INDEX IX_Pedidos_Fecha ON Pedidos(FechaPedido);

-- ========================================================================
-- 4. PROCEDIMIENTO ALMACENADO (Ventas por Rango de fechas)
-- ========================================================================
DELIMITER //

CREATE PROCEDURE usp_ObtenerVentasPorRango(
    IN p_FechaInicio DATETIME,
    IN p_FechaFin DATETIME
)
BEGIN
    -- Resumen total del periodo
    SELECT 
        COUNT(IdPedido) AS TotalPedidos,
        SUM(Total) AS IngresosTotales
    FROM Pedidos
    WHERE FechaPedido BETWEEN p_FechaInicio AND p_FechaFin;

    -- Detalle de pedidos del periodo
    SELECT 
        p.IdPedido,
        p.FechaPedido,
        CONCAT(u.Nombres, ' ', u.Apellidos) AS Cajero,
        p.Total,
        p.MetodoPago
    FROM Pedidos p
    INNER JOIN Usuarios u ON p.IdUsuario = u.IdUsuario
    WHERE p.FechaPedido BETWEEN p_FechaInicio AND p_FechaFin
    ORDER BY p.FechaPedido DESC;
END //

DELIMITER ;

-- ========================================================================
-- 5. POBLACIÓN DE DATOS (10 registros exactos por tabla)
-- ========================================================================

INSERT INTO Categorias (Nombre, Descripcion) VALUES
('Cafés Clásicos', 'Bebidas calientes a base de espresso tradicional'),
('Cafés Fríos', 'Bebidas refrescantes con café y hielo'),
('Repostería', 'Tartas, pasteles y dulces artesanales'),
('Panadería', 'Panes, croissants y opciones saladas'),
('Tés e Infusiones', 'Selección de hojas de té y mezclas herbales'),
('Bebidas sin Café', 'Chocolates calientes, batidos y jugos'),
('Sándwiches', 'Opciones saladas para desayuno o almuerzo'),
('Ensaladas', 'Opciones ligeras y saludables'),
('Café en Grano', 'Bolsas de café de especialidad para llevar'),
('Accesorios', 'Tazas, prensas francesas y filtros');

INSERT INTO Productos (IdCategoria, Nombre, Descripcion, Precio) VALUES
(1, 'Espresso Doble', 'Intenso y aromático, grano colombiano.', 3.50),
(1, 'Cappuccino Clásico', 'Espresso, leche vaporizada y espuma sedosa.', 4.50),
(3, 'Tarta de Zanahoria', 'Frosting de queso crema y nueces.', 5.00),
(1, 'Latte Vainilla', 'Café con leche y sirope de vainilla natural.', 4.00),
(2, 'Cold Brew', 'Café extraído en frío por 12 horas.', 4.50),
(3, 'Brownie de Chocolate', 'Doble chocolate con nueces.', 3.00),
(4, 'Croissant de Mantequilla', 'Masa hojaldrada horneada en el local.', 2.50),
(5, 'Té Verde Matcha', 'Matcha orgánico preparado en agua o leche.', 4.00),
(7, 'Sándwich Caprese', 'Mozzarella, tomate, albahaca en pan ciabatta.', 6.50),
(9, 'Bolsa Grano Colombia 250g', 'Tueste medio, notas a caramelo.', 12.00);

INSERT INTO Roles (Nombre, NivelAcceso) VALUES
('Administrador del Sistema', 1),
('Gerente de Tienda', 2),
('Supervisor de Turno', 3),
('Barista Principal', 4),
('Barista Junior', 5),
('Cajero', 6),
('Mesero', 7),
('Atención al Cliente', 8),
('Personal de Mantenimiento', 9),
('Auditor Contable', 10);

INSERT INTO Usuarios (IdRol, Nombres, Apellidos, Email) VALUES
(1, 'Carlos', 'Mendoza', 'cmendoza@elgranodorado.com'),
(2, 'Lucía', 'Ramírez', 'lramirez@elgranodorado.com'),
(4, 'Andrea', 'Gómez', 'agomez@elgranodorado.com'),
(5, 'Diego', 'Vargas', 'dvargas@elgranodorado.com'),
(6, 'Sofía', 'Castro', 'scastro@elgranodorado.com'),
(6, 'Mateo', 'Ríos', 'mrios@elgranodorado.com'),
(7, 'Valentina', 'Pérez', 'vperez@elgranodorado.com'),
(7, 'Sebastián', 'Silva', 'ssilva@elgranodorado.com'),
(9, 'Jorge', 'Luna', 'jluna@elgranodorado.com'),
(10, 'Elena', 'Morales', 'emorales@elgranodorado.com');

INSERT INTO Pedidos (IdUsuario, FechaPedido, Total, MetodoPago) VALUES
(5, '2026-10-01 08:15:00', 8.00, 'Tarjeta'),
(6, '2026-10-01 09:30:00', 5.00, 'Efectivo'),
(5, '2026-10-02 10:45:00', 9.50, 'Tarjeta'),
(5, '2026-10-02 11:20:00', 4.50, 'Yape/Plin'),
(6, '2026-10-03 14:10:00', 12.00, 'Efectivo'),
(6, '2026-10-03 15:50:00', 10.50, 'Tarjeta'),
(5, '2026-10-04 16:30:00', 3.50, 'Efectivo'),
(5, '2026-10-04 17:15:00', 13.50, 'Yape/Plin'),
(6, '2026-10-05 18:00:00', 7.00, 'Tarjeta'),
(6, '2026-10-05 19:20:00', 16.00, 'Tarjeta');

INSERT INTO DetallePedidos (IdPedido, IdProducto, Cantidad, PrecioUnitario) VALUES
(1, 2, 1, 4.50),
(1, 1, 1, 3.50), 
(2, 3, 1, 5.00),
(3, 2, 1, 4.50),
(3, 3, 1, 5.00), 
(4, 5, 1, 4.50),
(5, 10, 1, 12.00),
(6, 9, 1, 6.50),
(6, 4, 1, 4.00), 
(7, 1, 1, 3.50),
(8, 2, 3, 4.50),
(9, 1, 2, 3.50),
(10, 8, 4, 4.00);
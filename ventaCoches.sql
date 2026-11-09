CREATE DATABASE IF NOT EXISTS ventaCoches;
USE ventaCoches;

CREATE TABLE IF NOT EXISTS usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(150) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'trabajador') NOT NULL DEFAULT 'trabajador',
    fecha_alta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS cliente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('particular', 'empresa') NOT NULL DEFAULT 'particular',
    nombre VARCHAR(150) NOT NULL,
    apellidos VARCHAR(150),
    dni VARCHAR(20) NOT NULL UNIQUE,
    telefono VARCHAR(20) NOT NULL,
    email VARCHAR(100) UNIQUE,
    direccion VARCHAR(255),
    fecha_alta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS coche (
    id INT AUTO_INCREMENT PRIMARY KEY,
    matricula VARCHAR(15) NOT NULL UNIQUE,
    marca VARCHAR(100) NOT NULL,
    modelo VARCHAR(100) NOT NULL,
    anio INT NOT NULL,
    kilometros INT NOT NULL DEFAULT 0,
    color VARCHAR(50),
    combustible ENUM('gasolina', 'diesel', 'hibrido', 'electrico') NOT NULL,
    situacion ENUM('disponible', 'reservado', 'vendido') NOT NULL DEFAULT 'disponible',
    precio_venta DECIMAL(10, 2) NOT NULL,
    descripcion TEXT,
    imagen VARCHAR(500),
    fecha_alta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS compra (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_coche INT NOT NULL UNIQUE,
    id_cliente INT NOT NULL,
    id_usuario INT NOT NULL,
    precio DECIMAL(10, 2) NOT NULL,
    metodo_pago ENUM('efectivo', 'tarjeta', 'transferencia') NOT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    tenia_revision TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (id_coche) REFERENCES coche(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_cliente) REFERENCES cliente(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id) ON DELETE RESTRICT
);

-- Reserva: solo se apunta quién quiere el coche, no se paga nada.
-- Después se revisa el coche (tabla revision con id_reserva) y, si el cliente
-- se lo queda, se hace la venta (tabla venta con id_reserva) y la reserva pasa a 'completada'.
-- Si no lo quiere, la reserva pasa a 'cancelada' y el coche vuelve a 'disponible'.
CREATE TABLE IF NOT EXISTS reserva (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_coche INT NOT NULL,
    id_cliente INT NOT NULL,
    id_usuario INT,
    fecha_reserva DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('activa', 'cancelada', 'completada') NOT NULL DEFAULT 'activa',
    fecha_cancelacion DATETIME,
    FOREIGN KEY (id_coche) REFERENCES coche(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_cliente) REFERENCES cliente(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS revision (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_coche INT NOT NULL,
    id_reserva INT,
    id_usuario INT NOT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    resultado ENUM('pendiente', 'apto', 'no_apto') NOT NULL DEFAULT 'pendiente',
    FOREIGN KEY (id_coche) REFERENCES coche(id) ON DELETE CASCADE,
    FOREIGN KEY (id_reserva) REFERENCES reserva(id) ON DELETE SET NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id) ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS venta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_coche INT NOT NULL UNIQUE,
    id_cliente INT NOT NULL,
    id_usuario INT NOT NULL,
    id_reserva INT,
    precio DECIMAL(10, 2) NOT NULL,
    metodo_pago ENUM('efectivo', 'tarjeta', 'transferencia') NOT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_coche) REFERENCES coche(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_cliente) REFERENCES cliente(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_reserva) REFERENCES reserva(id) ON DELETE SET NULL
);


--   usuario:    admin
--   contraseña: admin1234
INSERT IGNORE INTO usuario (username, nombre, apellidos, email, password, rol)
VALUES ('admin', 'Admin', 'Admin', 'admin@ventacoches.com', '$2y$12$8xO/gWUb/lQ1NxTKPMevDuLIAKDEUPwWMwErvq3QNW8aMSvp0ZF66', 'admin');

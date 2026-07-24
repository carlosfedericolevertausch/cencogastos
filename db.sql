CREATE DATABASE IF NOT EXISTS cencogastos;
USE cencogastos;

CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(50) NOT NULL UNIQUE,
    clave_secreta VARCHAR(255) NOT NULL,
    sueldo DECIMAL(10, 2) DEFAULT 0.00,
    es_admin TINYINT(1) DEFAULT 0,
    creado_el TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tipos_gasto (
    id_tipo INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    nombre_tipo VARCHAR(50) NOT NULL,
    color_tipo VARCHAR(20) DEFAULT '#000000',
    creado_el TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS gastos (
    id_gasto INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_tipo INT NOT NULL,
    plata_gastada DECIMAL(10, 2) NOT NULL,
    detalle VARCHAR(255),
    fecha_gasto DATE NOT NULL,
    creado_el TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_tipo) REFERENCES tipos_gasto(id_tipo) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS metas_ahorro (
    id_meta INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    nombre_meta VARCHAR(100) NOT NULL,
    plata_objetivo DECIMAL(10, 2) NOT NULL,
    plata_juntada DECIMAL(10, 2) DEFAULT 0.00,
    fecha_limite DATE,
    creado_el TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

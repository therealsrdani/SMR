USE empresa;

CREATE TABLE IF NOT EXISTS solicitudes_ausencia (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    empleado_id INT UNSIGNED NOT NULL,
    usuario VARCHAR(191) NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    comentarios TEXT NOT NULL,
    creada_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_solicitudes_empleado (empleado_id),
    INDEX idx_solicitudes_creada_en (creada_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

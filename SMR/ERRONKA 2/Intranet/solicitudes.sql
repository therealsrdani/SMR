USE empresa;

CREATE TABLE IF NOT EXISTS solicitudes_ausencia (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    empleado_id INT UNSIGNED NOT NULL,
    usuario VARCHAR(191) NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    comentarios TEXT NOT NULL,
    estado ENUM('enviada', 'en_revision', 'cerrada') NOT NULL DEFAULT 'enviada',
    creada_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_solicitudes_empleado (empleado_id),
    INDEX idx_solicitudes_creada_en (creada_en),
    INDEX idx_solicitudes_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mensajes_solicitud (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    solicitud_id BIGINT UNSIGNED NOT NULL,
    autor_id INT UNSIGNED NOT NULL,
    autor VARCHAR(191) NOT NULL,
    rol VARCHAR(100) NOT NULL,
    mensaje TEXT NOT NULL,
    creada_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_mensajes_solicitud (solicitud_id, creada_en),
    CONSTRAINT fk_mensajes_solicitud
        FOREIGN KEY (solicitud_id) REFERENCES solicitudes_ausencia (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

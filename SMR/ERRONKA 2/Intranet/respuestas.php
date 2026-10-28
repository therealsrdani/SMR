<?php
session_start();

if (($_SESSION['authenticated'] ?? false) !== true) {
    header('Location: ../Web 1.0/index.html', true, 303);
    exit;
}

if (strtolower(trim((string) ($_SESSION['rol'] ?? ''))) !== 'director') {
    http_response_code(403);
    exit('Acceso restringido al rol Director.');
}

require __DIR__ . '/respuestas.html';

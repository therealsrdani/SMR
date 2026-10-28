<?php
session_start();

if (($_SESSION['authenticated'] ?? false) !== true) {
    header('Location: ../Web 1.0/index.html', true, 303);
    exit;
}

require __DIR__ . '/respuestas.html';

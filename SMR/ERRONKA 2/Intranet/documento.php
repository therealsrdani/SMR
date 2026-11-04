<?php
session_start();
require_once __DIR__ . '/permissions.php';
require_once __DIR__ . '/document-storage.php';
require_once __DIR__ . '/document-preview.php';

if (($_SESSION['authenticated'] ?? false) !== true) {
    http_response_code(401);
    exit('Inicia sesión para consultar la documentación.');
}

$role = strtolower(trim((string) ($_SESSION['rol'] ?? '')));
$permissions = permissions_for_role($role);
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$mode = $_GET['modo'] ?? '';
if (!is_int($id) || !in_array($mode, ['preview', 'download'], true)) {
    http_response_code(400);
    exit('Solicitud de documento no válida.');
}
if ($mode === 'download' && !$permissions['download_documents']) {
    http_response_code(403);
    exit('Guest puede previsualizar documentos, pero no descargarlos.');
}

mysqli_report(MYSQLI_REPORT_OFF);
$connection = new mysqli('localhost', 'root', '', 'empresa');
if ($connection->connect_error || !$connection->set_charset('utf8mb4')) {
    http_response_code(500);
    exit('No se pudo consultar el documento.');
}
$statement = $connection->prepare('SELECT nombre_archivo, ruta_archivo, tipo_mime FROM documentos WHERE id = ?');
if (!$statement || !$statement->bind_param('i', $id) || !$statement->execute()) {
    http_response_code(500);
    exit('No se pudo consultar el documento.');
}
$document = $statement->get_result()->fetch_assoc();
if (!$document) {
    http_response_code(404);
    exit('El documento no existe.');
}

$relativePath = (string) $document['ruta_archivo'];
try {
    $path = document_storage_file($relativePath);
} catch (RuntimeException $error) {
    error_log($error->getMessage());
    http_response_code(500);
    exit('No se pudo consultar el almacenamiento del documento.');
}
if ($path === null) {
    http_response_code(500);
    exit('La ruta del documento no es válida.');
}
if (!is_file($path) || !is_readable($path)) {
    http_response_code(404);
    exit('El archivo del documento no está disponible.');
}

if ($mode === 'preview' && in_array($document['tipo_mime'], [
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.oasis.opendocument.text',
    'application/vnd.oasis.opendocument.spreadsheet',
], true)) {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    try {
        $previewText = document_preview_text($path, (string) $document['tipo_mime']);
    } catch (RuntimeException $error) {
        http_response_code(500);
        echo json_encode(['error' => $error->getMessage()]);
        exit;
    }
    if ($role === 'guest') {
        $textLength = function_exists('mb_strlen') ? mb_strlen($previewText, 'UTF-8') : strlen($previewText);
        $halfLength = (int) ceil($textLength / 2);
        $previewText = function_exists('mb_substr')
            ? mb_substr($previewText, 0, $halfLength, 'UTF-8')
            : substr($previewText, 0, $halfLength);
    }
    header('Content-Type: text/plain; charset=utf-8');
    echo $previewText;
    exit;
}

$filename = basename((string) $document['nombre_archivo']);
$disposition = $mode === 'download' ? 'attachment' : 'inline';
$safeFilename = rawurlencode($filename);
header('Content-Type: ' . $document['tipo_mime']);
header('Content-Disposition: ' . $disposition . "; filename*=UTF-8''{$safeFilename}");
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');
header('Content-Length: ' . filesize($path));
readfile($path);

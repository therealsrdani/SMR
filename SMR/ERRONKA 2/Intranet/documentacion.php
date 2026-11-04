<?php
session_start();
require_once __DIR__ . '/permissions.php';
require_once __DIR__ . '/document-storage.php';
require_once __DIR__ . '/document-preview.php';

if (($_SESSION['authenticated'] ?? false) !== true) {
    header('Location: ../Web 1.0/index.html', true, 303);
    exit;
}

$role = strtolower(trim((string) ($_SESSION['rol'] ?? '')));
$permissions = permissions_for_role($role);
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
$errorMessage = '';
$successMessage = '';

function document_escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect_to_documents(): void
{
    header('Location: documentacion.php', true, 303);
    exit;
}

function document_date(string $value): string
{
    $date = new DateTimeImmutable($value);
    $months = [
        'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
        'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre',
    ];

    return $date->format('j') . ' de ' . $months[(int) $date->format('n') - 1] . ' de ' . $date->format('Y');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['csrf-token'] ?? '';
    if (!is_string($submittedToken) || !hash_equals($csrfToken, $submittedToken)) {
        $errorMessage = 'La sesión del formulario ha caducado. Recarga la página e inténtalo de nuevo.';
    } elseif (!$permissions['upload_documents'] && !$permissions['delete_documents']) {
        http_response_code(403);
        $errorMessage = 'Solo Admin puede gestionar documentos.';
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'upload' && $permissions['upload_documents']) {
            $contentType = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? ''));
            if (
                strpos($contentType, 'multipart/form-data') === 0
                && empty($_POST)
                && empty($_FILES)
                && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0
            ) {
                $errorMessage = 'El archivo supera el límite configurado en el servidor.';
            } elseif (
                isset($_POST['titulo']) && !is_string($_POST['titulo'])
                || isset($_POST['descripcion']) && !is_string($_POST['descripcion'])
                || isset($_POST['categoria']) && !is_string($_POST['categoria'])
            ) {
                $errorMessage = 'Los datos del documento no son válidos.';
            } else {
                $title = trim($_POST['titulo'] ?? '');
                $description = trim($_POST['descripcion'] ?? '');
                $category = trim($_POST['categoria'] ?? '');
                $file = $_FILES['archivo'] ?? null;

                if ($title === '' || strlen($title) > 150 || strlen($description) > 5000 || strlen($category) > 100) {
                    $errorMessage = 'Revisa el título, la categoría y la descripción.';
                } elseif (
                    !is_array($file)
                    || !isset($file['error'], $file['tmp_name'], $file['size'], $file['name'])
                    || !is_int($file['error'])
                    || !is_string($file['tmp_name'])
                    || !is_int($file['size'])
                    || !is_string($file['name'])
                ) {
                    $errorMessage = 'Selecciona un archivo PDF, DOCX u ODT.';
                } elseif ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
                    $errorMessage = 'No se pudo recibir el archivo. Comprueba que no supere los 20 MB.';
                } elseif ($file['size'] < 1 || $file['size'] > 20 * 1024 * 1024) {
                    $errorMessage = 'El archivo debe tener contenido y no superar los 20 MB.';
                } else {
                    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $originalName = basename($file['name']);
                    $formats = [
                        'pdf' => 'application/pdf',
                        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'odt' => 'application/vnd.oasis.opendocument.text',
                    ];
                    if ($originalName === '' || strlen($originalName) > 255) {
                        $errorMessage = 'El nombre del archivo no puede superar los 255 caracteres.';
                    } elseif (!isset($formats[$extension])) {
                        $errorMessage = 'Formato no permitido. Usa PDF, DOCX u ODT.';
                    } else {
                        $fileInfo = new finfo(FILEINFO_MIME_TYPE);
                        $detectedMime = $fileInfo->file($file['tmp_name']);
                        if (!is_string($detectedMime)) {
                            $errorMessage = 'No se pudo verificar el formato del archivo.';
                        } elseif ($extension === 'pdf' && $detectedMime !== 'application/pdf') {
                            $errorMessage = 'El contenido del archivo no es un PDF válido.';
                        } elseif ($extension !== 'pdf' && !class_exists(ZipArchive::class)) {
                            $errorMessage = 'El servidor necesita la extensión PHP ZIP para validar archivos DOCX y ODT.';
                        } elseif ($extension !== 'pdf') {
                            $archive = new ZipArchive();
                            $archiveResult = $archive->open($file['tmp_name'], ZipArchive::CHECKCONS);
                            $requiredMember = $extension === 'docx' ? 'word/document.xml' : 'content.xml';
                            $memberStats = $archiveResult === true ? $archive->statName($requiredMember) : false;
                            $isValidArchive = $memberStats !== false
                                && isset($memberStats['size'])
                                && $memberStats['size'] <= 8 * 1024 * 1024;
                            if ($archiveResult === true) {
                                $archive->close();
                            }
                            if (
                                !$isValidArchive
                                || !in_array($detectedMime, ['application/zip', $formats[$extension]], true)
                            ) {
                                $errorMessage = 'El contenido no coincide con un documento DOCX u ODT válido.';
                            }
                        }

                        if ($errorMessage === '') {
                            try {
                                $uploadDirectory = document_storage_directory();
                            } catch (RuntimeException $exception) {
                                error_log($exception->getMessage());
                                $errorMessage = 'No se pudo determinar el almacenamiento privado de documentos.';
                            }
                        }
                        if (
                            $errorMessage === ''
                            && !is_dir($uploadDirectory)
                            && !mkdir($uploadDirectory, 0755, true)
                            && !is_dir($uploadDirectory)
                        ) {
                            $errorMessage = 'No se pudo preparar el almacenamiento de documentos.';
                        }
                        if ($errorMessage === '' && !is_writable($uploadDirectory)) {
                            $errorMessage = 'El servidor no tiene permiso para guardar documentos.';
                        }
                        if ($errorMessage === '') {
                            mysqli_report(MYSQLI_REPORT_OFF);
                            $connection = new mysqli('localhost', 'root', '', 'empresa');
                            if ($connection->connect_error) {
                                $errorMessage = 'No se pudo conectar con la base de datos.';
                            } elseif (!$connection->set_charset('utf8mb4')) {
                                $errorMessage = 'No se pudo configurar la conexión con la base de datos.';
                            } else {
                                $filename = bin2hex(random_bytes(16)) . '.' . $extension;
                                $destination = $uploadDirectory . DIRECTORY_SEPARATOR . $filename;
                                if (!move_uploaded_file($file['tmp_name'], $destination)) {
                                    $errorMessage = 'No se pudo guardar el documento en el servidor.';
                                } else {
                                    $mime = $formats[$extension];
                                    $relativePath = $filename;
                                    $uploader = trim((string) ($_SESSION['nombre'] ?? $_SESSION['username'] ?? 'Admin'));
                                    $statement = $connection->prepare(
                                        'INSERT INTO documentos (titulo, descripcion, categoria, nombre_archivo, ruta_archivo, tipo_mime, subido_por)
                                         VALUES (?, ?, ?, ?, ?, ?, ?)'
                                    );
                                    if (
                                        !$statement
                                        || !$statement->bind_param(
                                            'sssssss',
                                            $title,
                                            $description,
                                            $category,
                                            $originalName,
                                            $relativePath,
                                            $mime,
                                            $uploader
                                        )
                                        || !$statement->execute()
                                    ) {
                                        if (!unlink($destination)) {
                                            error_log('No se pudo limpiar un documento tras fallar su registro en la base de datos.');
                                        }
                                        $errorMessage = 'No se pudo guardar la información del documento.';
                                    } else {
                                        $connection->close();
                                        $_SESSION['document_success'] = 'Documento subido correctamente.';
                                        redirect_to_documents();
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } elseif ($action === 'delete' && $permissions['delete_documents']) {
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($id === false) {
                $errorMessage = 'Identificador de documento no válido.';
            } else {
                mysqli_report(MYSQLI_REPORT_OFF);
                $connection = new mysqli('localhost', 'root', '', 'empresa');
                if ($connection->connect_error) {
                    $errorMessage = 'No se pudo conectar con la base de datos.';
                } elseif (!$connection->set_charset('utf8mb4')) {
                    $errorMessage = 'No se pudo configurar la conexión con la base de datos.';
                } else {
                    $lookup = $connection->prepare('SELECT ruta_archivo FROM documentos WHERE id = ?');
                    if (!$lookup || !$lookup->bind_param('i', $id) || !$lookup->execute()) {
                        $errorMessage = 'No se pudo consultar el documento.';
                    } else {
                        $document = $lookup->get_result()->fetch_assoc();
                        if (!$document) {
                            $errorMessage = 'El documento ya no existe.';
                        } else {
                            $delete = $connection->prepare('DELETE FROM documentos WHERE id = ?');
                            if (!$delete || !$delete->bind_param('i', $id) || !$delete->execute()) {
                                $errorMessage = 'No se pudo eliminar el documento.';
                            } else {
                                $path = document_storage_file((string) $document['ruta_archivo']);
                                if ($path !== null && is_file($path) && !unlink($path)) {
                                    error_log('No se pudo eliminar el archivo de un documento eliminado.');
                                }
                                $connection->close();
                                $_SESSION['document_success'] = 'Documento eliminado.';
                                redirect_to_documents();
                            }
                        }
                    }
                }
            }
        } else {
            http_response_code(403);
            $errorMessage = 'La acción solicitada no está permitida.';
        }
    }
}

if (isset($_SESSION['document_success'])) {
    $successMessage = (string) $_SESSION['document_success'];
    unset($_SESSION['document_success']);
}

mysqli_report(MYSQLI_REPORT_OFF);
$connection = new mysqli('localhost', 'root', '', 'empresa');
$documents = [];
if ($connection->connect_error) {
    http_response_code(500);
    $errorMessage = 'No se pudo conectar con la base de datos.';
} elseif (!$connection->set_charset('utf8mb4')) {
    http_response_code(500);
    $errorMessage = 'No se pudo configurar la conexión con la base de datos.';
} else {
    $statement = $connection->prepare(
        'SELECT id, titulo, descripcion, categoria, nombre_archivo, tipo_mime, subido_por, creado_en
         FROM documentos ORDER BY creado_en DESC, id DESC'
    );
    if (!$statement || !$statement->execute()) {
        http_response_code(500);
        $errorMessage = 'No se pudo cargar la documentación. Comprueba que has ejecutado gestion.sql.';
    } else {
        $documents = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    $connection->close();
}

$previewId = filter_input(INPUT_GET, 'preview', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$previewDocument = null;
$previewText = '';
$previewError = '';
if ($previewId !== null && $previewId !== false) {
    foreach ($documents as $document) {
        if ((int) $document['id'] === $previewId) {
            $previewDocument = $document;
            break;
        }
    }
    if ($previewDocument === null) {
        $previewError = 'El documento no existe o ya no está disponible.';
    } elseif (in_array($previewDocument['tipo_mime'], [
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.oasis.opendocument.text',
    ], true)) {
        try {
            $path = document_storage_file((string) $previewDocument['ruta_archivo']);
            if ($path === null || !is_file($path) || !is_readable($path)) {
                throw new RuntimeException('El archivo del documento no está disponible.');
            }
            $previewText = document_preview_text($path, (string) $previewDocument['tipo_mime']);
            if ($role === 'guest') {
                $previewLength = function_exists('mb_strlen')
                    ? mb_strlen($previewText, 'UTF-8')
                    : strlen($previewText);
                $previewText = function_exists('mb_substr')
                    ? mb_substr($previewText, 0, (int) ceil($previewLength / 2), 'UTF-8')
                    : substr($previewText, 0, (int) ceil($previewLength / 2));
            }
        } catch (RuntimeException $exception) {
            $previewError = $exception->getMessage();
        }
    }
}

$name = trim((string) ($_SESSION['nombre'] ?? $_SESSION['username'] ?? 'Usuario'));
$initials = implode('', array_map(
    static fn (string $part): string => substr($part, 0, 1),
    array_slice(preg_split('/\s+/', $name) ?: [], 0, 2)
));
$roleLabels = [
    'admin' => 'Admin',
    'guest' => 'Guest',
    'recursos humanos' => 'Recursos Humanos',
    'director' => 'Director',
    'director ejecutivo' => 'Director Ejecutivo',
    'gerente' => 'Gerente',
    'empleado' => 'Empleado',
    'cliente' => 'Cliente',
];
$roleLabel = $roleLabels[$role] ?? trim((string) ($_SESSION['rol'] ?? ''));
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <link rel="icon" type="image/png" href="img/logo.png">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#005AFA">
  <title>Documentación | Intranet</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="app-shell">
    <aside class="sidebar">
      <a class="brand" href="index.html" aria-label="Intranet, inicio"><span class="brand-mark">I</span><span>Kalvex<small>ESPACIO DE EQUIPO</small></span></a>
      <p class="nav-caption">MENÚ PRINCIPAL</p>
      <nav class="side-nav" aria-label="Navegación principal">
        <a class="nav-link" href="index.html"><span class="nav-icon">⌂</span>Inicio</a>
        <a class="nav-link" href="solicitudes.html"><span class="nav-icon">＋</span>Solicitudes</a>
        <a class="nav-link" href="mis-solicitudes.html"><span class="nav-icon">☷</span>Mis solicitudes</a>
        <?php if (in_array($role, ['admin', 'director', 'director ejecutivo', 'recursos humanos'], true)): ?>
          <a class="nav-link" href="respuestas.php"><span class="nav-icon">▤</span>Respuestas</a>
        <?php endif; ?>
        <a class="nav-link" href="horarios.html"><span class="nav-icon">▦</span>Horarios</a>
        <a class="nav-link" href="productos.html"><span class="nav-icon">◇</span>Catálogo</a>
        <a class="nav-link active" href="documentacion.php" aria-current="page"><span class="nav-icon">▧</span>Documentación</a>
        <a class="nav-link" href="equipo.html"><span class="nav-icon">♧</span>Equipo</a>
      </nav>
      <div class="sidebar-bottom"><div class="sidebar-help"><span class="help-icon">?</span><span>¿Necesitas ayuda?<small>Contacta con RR. HH.</small></span></div><a class="logout-link" href="logout.php">Cerrar sesión</a></div>
    </aside>
    <main class="main-area">
      <header class="topbar"><div><p class="breadcrumb">INTRANET <span>/</span> DOCUMENTACIÓN</p><p class="topbar-date">Documentos de consulta del equipo</p></div><div class="profile"><span class="status-dot"></span><span class="avatar"><?= document_escape($initials ?: 'U') ?></span><span class="profile-name"><?= document_escape($name) ?><small><?= document_escape($roleLabel) ?></small></span></div></header>
      <div class="page-content">
        <section class="page-intro"><p class="eyebrow">CENTRO DE DOCUMENTACIÓN</p><h1>Documentación</h1><p>Consulta las guías y documentos internos. DOCX y ODT se previsualizan como texto; el visor PDF necesita conexión a Internet. El acceso a descarga depende de tu rol.</p></section>
        <?php if ($successMessage !== ''): ?><p class="form-message"><?= document_escape($successMessage) ?></p><?php endif; ?>
        <?php if ($errorMessage !== ''): ?><p class="form-message" role="alert"><?= document_escape($errorMessage) ?></p><?php endif; ?>
        <section class="documents-toolbar" aria-label="Acciones de documentación">
          <p class="catalog-count"><?= count($documents) ?> <?= count($documents) === 1 ? 'documento' : 'documentos' ?></p>
          <?php if ($permissions['upload_documents']): ?><a class="button button-primary" href="#formulario-documento">Subir documento</a><?php endif; ?>
        </section>
        <?php if ($permissions['upload_documents']): ?>
          <form id="formulario-documento" class="management-form" action="documentacion.php" method="post" enctype="multipart/form-data">
            <h2>Subir documentación</h2>
            <input type="hidden" name="csrf-token" value="<?= document_escape($csrfToken) ?>">
            <input type="hidden" name="action" value="upload">
            <div class="management-fields">
              <label class="field">Título<input name="titulo" maxlength="150" required></label>
              <label class="field">Categoría<input name="categoria" maxlength="100" placeholder="Guías, políticas, procedimientos..."></label>
              <label class="field document-description-field">Descripción<textarea name="descripcion" rows="3" maxlength="5000"></textarea></label>
              <label class="field document-file-field">Archivo<input name="archivo" type="file" accept=".pdf,.docx,.odt,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.oasis.opendocument.text" required><small>PDF, DOCX u ODT; máximo 20 MB.</small></label>
            </div>
            <div class="form-actions"><span></span><button class="button button-primary" type="submit">Guardar documento</button></div>
          </form>
        <?php endif; ?>
        <section class="document-grid" aria-label="Documentos disponibles">
          <?php if ($documents === []): ?><p class="empty-state">No hay documentos disponibles.</p><?php endif; ?>
          <?php foreach ($documents as $document): ?>
            <?php
              $extension = strtoupper(pathinfo((string) $document['nombre_archivo'], PATHINFO_EXTENSION));
            ?>
            <article class="document-card">
              <div class="document-card-heading"><span class="document-file-type" aria-hidden="true"><?= document_escape($extension) ?></span><h2><?= document_escape((string) $document['titulo']) ?></h2></div>
              <p class="document-description"><?= document_escape((string) ($document['descripcion'] ?: 'Sin descripción.')) ?></p>
              <p class="document-metadata"><?= document_escape((string) ($document['categoria'] ?: 'General')) ?> · Subido por <?= document_escape((string) $document['subido_por']) ?> · <?= document_escape(document_date((string) $document['creado_en'])) ?></p>
              <div class="document-actions">
                <a class="button button-secondary" href="documentacion.php?preview=<?= (int) $document['id'] ?>#vista-previa">Previsualizar</a>
                <?php if ($permissions['download_documents']): ?><a class="button button-primary" href="documento.php?modo=download&amp;id=<?= (int) $document['id'] ?>">Descargar</a><?php endif; ?>
                <?php if ($permissions['delete_documents']): ?>
                  <form action="documentacion.php" method="post">
                    <input type="hidden" name="csrf-token" value="<?= document_escape($csrfToken) ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int) $document['id'] ?>">
                    <button class="button button-danger" type="submit">Eliminar</button>
                  </form>
                <?php endif; ?>
              </div>
            </article>
          <?php endforeach; ?>
        </section>
        <footer class="page-footer">Kalvex · Intranet <span>Documentación interna</span></footer>
      </div>
    </main>
  </div>

  <?php if ($previewId !== null && $previewId !== false): ?>
    <section class="document-viewer-overlay" id="vista-previa" aria-label="Previsualización del documento">
      <div class="document-viewer">
        <header class="document-viewer-header">
          <div>
            <h2><?= document_escape((string) ($previewDocument['titulo'] ?? 'Previsualización')) ?></h2>
            <p><?= document_escape((string) ($previewDocument['descripcion'] ?? '')) ?></p>
          </div>
          <a class="button button-secondary" href="documentacion.php">Cerrar</a>
        </header>
        <?php if ($previewDocument === null): ?>
          <p class="document-preview-error"><?= document_escape($previewError) ?></p>
        <?php else: ?>
          <?php if (!$permissions['download_documents']): ?><p class="guest-preview-notice">Como Guest solo puedes consultar una parte de este documento. Para leerlo completo, solicita acceso como empleado.</p><?php endif; ?>
          <div class="document-viewer-content<?= $role === 'guest' && $previewDocument['tipo_mime'] === 'application/pdf' ? ' document-viewer-guest' : '' ?>">
            <?php if ($previewError !== ''): ?>
              <p class="document-preview-error"><?= document_escape($previewError) ?></p>
            <?php elseif ($previewDocument['tipo_mime'] === 'application/pdf'): ?>
              <iframe class="document-pdf-frame<?= $role === 'guest' ? ' guest-pdf-preview' : '' ?>" src="documento.php?modo=preview&amp;id=<?= (int) $previewDocument['id'] ?>" title="Previsualización de <?= document_escape((string) $previewDocument['titulo']) ?>"></iframe>
            <?php else: ?>
              <article class="document-text-preview"><?= document_escape($previewText !== '' ? $previewText : 'Este documento no contiene texto que se pueda previsualizar.') ?></article>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    </section>
  <?php endif; ?>
</body>
</html>

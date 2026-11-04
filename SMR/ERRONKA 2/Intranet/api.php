<?php
session_start();
require_once __DIR__ . '/permissions.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function respond(int $status, array $body): void
{
    http_response_code($status);
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function product_image_path(string $relativePath): ?string
{
    if (!preg_match('/\Auploads\/productos\/[a-f0-9]{32}\.(?:jpg|png|webp|gif)\z/', $relativePath)) {
        return null;
    }

    return __DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
}

function delete_unused_product_image(mysqli $connection, string $relativePath): void
{
    $path = product_image_path($relativePath);
    if ($path === null) {
        return;
    }

    $statement = $connection->prepare('SELECT id FROM productos WHERE imagen_url = ? LIMIT 1');
    if (!$statement || !$statement->bind_param('s', $relativePath) || !$statement->execute()) {
        error_log('No se pudo comprobar si una imagen del catálogo sigue en uso.');
        return;
    }
    if ($statement->get_result()->num_rows > 0) {
        return;
    }
    if (is_file($path) && !unlink($path)) {
        error_log('No se pudo eliminar una imagen del catálogo que ya no está en uso.');
    }
}

if (($_SESSION['authenticated'] ?? false) !== true) {
    respond(401, ['error' => 'Inicia sesión para continuar.']);
}

$role = strtolower(trim((string) ($_SESSION['rol'] ?? '')));
$permissions = permissions_for_role($role);
$resource = $_GET['resource'] ?? '';

if (!in_array($resource, ['productos', 'horarios', 'solicitudes'], true)) {
    respond(400, ['error' => 'Recurso no válido.']);
}

mysqli_report(MYSQLI_REPORT_OFF);
$connection = new mysqli('localhost', 'root', '', 'empresa');

if ($connection->connect_error) {
    respond(500, ['error' => 'No se pudo conectar con la base de datos.']);
}

if (!$connection->set_charset('utf8mb4')) {
    respond(500, ['error' => 'No se pudo configurar la conexión con la base de datos.']);
}

if ($resource === 'solicitudes') {
    $userId = (int) ($_SESSION['user_id'] ?? 0);
    if ($userId < 1) {
        respond(403, ['error' => 'La sesión no contiene un usuario válido.']);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $showAll = ($_GET['scope'] ?? '') === 'all';
        if ($showAll && !$permissions['view_all_requests']) {
            respond(403, ['error' => 'Tu rol no tiene permiso para consultar todas las solicitudes.']);
        }

        $query = $showAll
            ? 'SELECT id, usuario, tipo, fecha_inicio, fecha_fin, comentarios, creada_en, estado
               FROM solicitudes_ausencia ORDER BY creada_en DESC, id DESC'
            : 'SELECT id, usuario, tipo, fecha_inicio, fecha_fin, comentarios, creada_en, estado
               FROM solicitudes_ausencia WHERE empleado_id = ? ORDER BY creada_en DESC, id DESC';
        $statement = $connection->prepare($query);
        if (!$statement || (!$showAll && !$statement->bind_param('i', $userId)) || !$statement->execute()) {
            respond(500, ['error' => 'No se pudieron cargar las solicitudes. Comprueba que has ejecutado solicitudes.sql.']);
        }

        $items = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
        $messagesQuery = $showAll
            ? 'SELECT m.solicitud_id, m.autor, m.rol, m.mensaje, m.creada_en
               FROM mensajes_solicitud m
               INNER JOIN solicitudes_ausencia s ON s.id = m.solicitud_id
               ORDER BY m.creada_en, m.id'
            : 'SELECT m.solicitud_id, m.autor, m.rol, m.mensaje, m.creada_en
               FROM mensajes_solicitud m
               INNER JOIN solicitudes_ausencia s ON s.id = m.solicitud_id
               WHERE s.empleado_id = ? ORDER BY m.creada_en, m.id';
        $messagesStatement = $connection->prepare($messagesQuery);
        if (!$messagesStatement || (!$showAll && !$messagesStatement->bind_param('i', $userId)) || !$messagesStatement->execute()) {
            respond(500, ['error' => 'No se pudieron cargar los mensajes de las solicitudes. Comprueba que has ejecutado solicitudes.sql.']);
        }

        $messagesByRequest = [];
        foreach ($messagesStatement->get_result()->fetch_all(MYSQLI_ASSOC) as $message) {
            $messagesByRequest[$message['solicitud_id']][] = $message;
        }
        foreach ($items as &$item) {
            $item['mensajes'] = $messagesByRequest[$item['id']] ?? [];
        }
        unset($item);

        respond(200, ['items' => $items]);
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Allow: GET, POST');
        respond(405, ['error' => 'Método no permitido.']);
    }

    $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!is_string($csrfToken) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        respond(403, ['error' => 'La sesión del formulario ha caducado. Recarga la página e inténtalo de nuevo.']);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        respond(400, ['error' => 'La petición no contiene datos válidos.']);
    }

    $requestId = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $action = $input['action'] ?? '';
    if ($requestId === false || !is_string($action)) {
        respond(400, ['error' => 'Solicitud o acción no válida.']);
    }

    if ($action === 'status') {
        if (!$permissions['change_request_status']) {
            respond(403, ['error' => 'Tu rol no tiene permiso para cambiar el estado de una solicitud.']);
        }

        $newStatus = $input['estado'] ?? '';
        $allowedTransitions = ['enviada' => 'en_revision', 'en_revision' => 'cerrada'];
        $check = $connection->prepare('SELECT estado FROM solicitudes_ausencia WHERE id = ?');
        if (!$check || !$check->bind_param('i', $requestId) || !$check->execute()) {
            respond(500, ['error' => 'No se pudo comprobar el estado de la solicitud.']);
        }
        $request = $check->get_result()->fetch_assoc();
        if (!$request) {
            respond(404, ['error' => 'La solicitud ya no existe.']);
        }
        if (!is_string($newStatus) || ($allowedTransitions[$request['estado']] ?? null) !== $newStatus) {
            respond(409, ['error' => 'La solicitud ya ha cambiado o esa transición no está permitida.']);
        }

        $update = $connection->prepare('UPDATE solicitudes_ausencia SET estado = ? WHERE id = ? AND estado = ?');
        if (!$update || !$update->bind_param('sis', $newStatus, $requestId, $request['estado']) || !$update->execute()) {
            respond(500, ['error' => 'No se pudo actualizar el estado de la solicitud.']);
        }
        if ($update->affected_rows !== 1) {
            respond(409, ['error' => 'La solicitud ya ha cambiado. Recarga la página e inténtalo de nuevo.']);
        }
        respond(200, ['message' => 'Estado actualizado correctamente.']);
    }

    if ($action !== 'message') {
        respond(400, ['error' => 'Acción no válida.']);
    }
    if (!$permissions['reply_to_own_requests'] && !$permissions['reply_to_others']) {
        respond(403, ['error' => 'Tu rol no tiene permiso para responder a solicitudes.']);
    }

    $messageText = $input['mensaje'] ?? null;
    $trimmedMessage = is_string($messageText) ? trim($messageText) : '';
    $messageLength = preg_match_all('/./us', $trimmedMessage);
    if (!is_string($messageText) || $trimmedMessage === '' || $messageLength === false || $messageLength > 2000) {
        respond(400, ['error' => 'Escribe un mensaje de hasta 2000 caracteres.']);
    }

    if (!$connection->begin_transaction()) {
        respond(500, ['error' => 'No se pudo iniciar el envío del mensaje.']);
    }
    $check = $connection->prepare('SELECT empleado_id, estado FROM solicitudes_ausencia WHERE id = ? FOR UPDATE');
    if (!$check || !$check->bind_param('i', $requestId) || !$check->execute()) {
        respond(500, ['error' => 'No se pudo comprobar la solicitud.']);
    }
    $request = $check->get_result()->fetch_assoc();
    if (!$request) {
        respond(404, ['error' => 'La solicitud ya no existe.']);
    }
    if ($request['estado'] !== 'en_revision') {
        respond(409, ['error' => 'Solo se pueden enviar mensajes mientras la solicitud está en revisión.']);
    }
    if (!$permissions['reply_to_others'] && (int) $request['empleado_id'] !== $userId) {
        respond(403, ['error' => 'No tienes permiso para responder a esta solicitud.']);
    }

    $author = trim((string) ($_SESSION['nombre'] ?? $_SESSION['username'] ?? ''));
    $authorRole = trim((string) ($_SESSION['rol'] ?? ''));
    $insert = $connection->prepare(
        'INSERT INTO mensajes_solicitud (solicitud_id, autor_id, autor, rol, mensaje)
         VALUES (?, ?, ?, ?, ?)'
    );
    $messageText = $trimmedMessage;
    if (!$insert || !$insert->bind_param('iisss', $requestId, $userId, $author, $authorRole, $messageText) || !$insert->execute()) {
        respond(500, ['error' => 'No se pudo guardar el mensaje.']);
    }
    if (!$connection->commit()) {
        respond(500, ['error' => 'No se pudo confirmar el envío del mensaje.']);
    }
    respond(201, ['message' => 'Mensaje enviado.']);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $table = $resource;
    $statement = $connection->prepare("SELECT * FROM {$table} ORDER BY id DESC");

    if (!$statement || !$statement->execute()) {
        respond(500, ['error' => 'No se pudieron cargar los datos. Comprueba que has ejecutado gestion.sql.']);
    }

    $result = $statement->get_result();
    respond(200, ['items' => $result->fetch_all(MYSQLI_ASSOC)]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: GET, POST');
    respond(405, ['error' => 'Método no permitido.']);
}

$csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!is_string($csrfToken) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    respond(403, ['error' => 'La sesión del formulario ha caducado. Recarga la página e inténtalo de nuevo.']);
}

$contentType = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? ''));
$isProductForm = $resource === 'productos' && strpos($contentType, 'multipart/form-data') === 0;
if ($isProductForm && empty($_POST) && empty($_FILES) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    respond(413, ['error' => 'La imagen supera el límite de tamaño permitido por el servidor.']);
}

$input = $isProductForm ? $_POST : json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    respond(400, ['error' => 'La petición no contiene datos válidos.']);
}

$action = $input['action'] ?? '';
$id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$table = $resource;
$permissionByAction = [
    'productos' => [
        'create' => 'create_product',
        'update' => 'update_product',
        'delete' => 'delete_product',
    ],
    'horarios' => [
        'create' => 'create_schedule',
        'update' => 'update_schedule',
        'delete' => 'delete_schedule',
    ],
];
if (!is_string($action) || !isset($permissionByAction[$resource][$action])) {
    respond(400, ['error' => 'Acción no válida.']);
}
if (!$permissions[$permissionByAction[$resource][$action]]) {
    respond(403, ['error' => 'Tu rol no tiene permiso para realizar esta acción.']);
}

$columns = $resource === 'productos'
    ? ['nombre', 'descripcion', 'categoria', 'precio', 'imagen_url', 'disponibilidad']
    : ['equipo', 'lunes_jueves', 'viernes', 'modalidad', 'responsable'];

if ($action === 'delete') {
    if ($id === false) {
        respond(400, ['error' => 'Identificador no válido.']);
    }

    $oldImage = '';
    if ($resource === 'productos') {
        $imageStatement = $connection->prepare('SELECT imagen_url FROM productos WHERE id = ?');
        if (!$imageStatement || !$imageStatement->bind_param('i', $id) || !$imageStatement->execute()) {
            respond(500, ['error' => 'No se pudo consultar la imagen del producto.']);
        }
        $product = $imageStatement->get_result()->fetch_assoc();
        if (!$product) {
            respond(404, ['error' => 'El registro ya no existe.']);
        }
        $oldImage = (string) $product['imagen_url'];
    }

    $statement = $connection->prepare("DELETE FROM {$table} WHERE id = ?");
    if (!$statement || !$statement->bind_param('i', $id) || !$statement->execute()) {
        respond(500, ['error' => 'No se pudo eliminar el registro.']);
    }

    if ($statement->affected_rows === 0) {
        respond(404, ['error' => 'El registro ya no existe.']);
    }

    if ($resource === 'productos' && $oldImage !== '') {
        delete_unused_product_image($connection, $oldImage);
    }

    respond(200, ['message' => 'Registro eliminado.']);
}

if ($action === 'update' && $id === false) {
    respond(400, ['error' => 'Acción o identificador no válido.']);
}

$inputColumns = $resource === 'productos'
    ? ['nombre', 'descripcion', 'categoria', 'precio', 'disponibilidad']
    : $columns;
$values = [];
foreach ($inputColumns as $column) {
    $value = $input[$column] ?? null;
    if (!is_string($value) && !is_int($value) && !is_float($value)) {
        respond(400, ['error' => 'Completa todos los campos correctamente.']);
    }
    $values[$column] = trim((string) $value);
}

if ($resource === 'productos') {
    $price = filter_var($values['precio'], FILTER_VALIDATE_FLOAT);
    if (
        $values['nombre'] === '' || strlen($values['nombre']) > 150
        || strlen($values['descripcion']) > 5000
        || $values['categoria'] === '' || strlen($values['categoria']) > 100
        || $price === false || $price < 0 || $price > 99999999.99
        || $values['disponibilidad'] === '' || strlen($values['disponibilidad']) > 100
    ) {
        respond(400, ['error' => 'Revisa el nombre, categoría, precio, imagen y disponibilidad del producto.']);
    }
    $values['precio'] = number_format((float) $price, 2, '.', '');

    $oldImage = '';
    if ($action === 'update') {
        $imageStatement = $connection->prepare('SELECT imagen_url FROM productos WHERE id = ?');
        if (!$imageStatement || !$imageStatement->bind_param('i', $id) || !$imageStatement->execute()) {
            respond(500, ['error' => 'No se pudo consultar la imagen actual del producto.']);
        }
        $product = $imageStatement->get_result()->fetch_assoc();
        if (!$product) {
            respond(404, ['error' => 'El registro ya no existe.']);
        }
        $oldImage = (string) $product['imagen_url'];
    }

    $imagePath = product_image_path($oldImage) === null ? '' : $oldImage;
    $uploadedFile = $_FILES['imagen'] ?? null;
    if ($uploadedFile !== null) {
        if (
            !is_array($uploadedFile)
            || !isset($uploadedFile['error'], $uploadedFile['tmp_name'], $uploadedFile['size'])
            || !is_int($uploadedFile['error'])
            || !is_string($uploadedFile['tmp_name'])
            || !is_int($uploadedFile['size'])
        ) {
            respond(400, ['error' => 'El archivo de imagen no es válido.']);
        }
        if ($uploadedFile['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
                respond(400, ['error' => 'No se pudo recibir la imagen. Comprueba que no supere el límite de 5 MB.']);
            }
            if ((int) $uploadedFile['size'] > 5 * 1024 * 1024 || !is_uploaded_file($uploadedFile['tmp_name'])) {
                respond(400, ['error' => 'La imagen debe ser válida y no superar los 5 MB.']);
            }

            $fileInfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $fileInfo->file($uploadedFile['tmp_name']);
            $imageTypes = [
                'image/jpeg' => ['extension' => 'jpg', 'type' => IMAGETYPE_JPEG],
                'image/png' => ['extension' => 'png', 'type' => IMAGETYPE_PNG],
                'image/webp' => ['extension' => 'webp', 'type' => IMAGETYPE_WEBP],
                'image/gif' => ['extension' => 'gif', 'type' => IMAGETYPE_GIF],
            ];
            $imageInfo = @getimagesize($uploadedFile['tmp_name']);
            if (
                !is_string($mimeType) || !isset($imageTypes[$mimeType])
                || $imageInfo === false || $imageInfo[2] !== $imageTypes[$mimeType]['type']
            ) {
                respond(400, ['error' => 'Formato no válido. Usa una imagen JPG, PNG, WEBP o GIF.']);
            }

            $uploadDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'productos';
            if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
                respond(500, ['error' => 'No se pudo preparar el almacenamiento compartido de imágenes.']);
            }
            if (!is_writable($uploadDirectory)) {
                respond(500, ['error' => 'El servidor no tiene permiso para guardar imágenes del catálogo.']);
            }

            $filename = bin2hex(random_bytes(16)) . '.' . $imageTypes[$mimeType]['extension'];
            $destination = $uploadDirectory . DIRECTORY_SEPARATOR . $filename;
            if (!move_uploaded_file($uploadedFile['tmp_name'], $destination)) {
                respond(500, ['error' => 'No se pudo guardar la imagen en el servidor.']);
            }
            $imagePath = 'uploads/productos/' . $filename;
        }
    }
    $values['imagen_url'] = $imagePath;
    $values = [
        'nombre' => $values['nombre'],
        'descripcion' => $values['descripcion'],
        'categoria' => $values['categoria'],
        'precio' => $values['precio'],
        'imagen_url' => $values['imagen_url'],
        'disponibilidad' => $values['disponibilidad'],
    ];
} elseif (
    $values['equipo'] === '' || strlen($values['equipo']) > 150
    || $values['lunes_jueves'] === '' || strlen($values['lunes_jueves']) > 150
    || $values['viernes'] === '' || strlen($values['viernes']) > 150
    || $values['modalidad'] === '' || strlen($values['modalidad']) > 100
    || $values['responsable'] === '' || strlen($values['responsable']) > 150
) {
    respond(400, ['error' => 'Completa correctamente los datos del horario.']);
}

$columnList = implode(', ', $columns);
$placeholders = implode(', ', array_fill(0, count($columns), '?'));
$types = $resource === 'productos' ? 'ssssss' : 'sssss';

if ($action === 'create') {
    $statement = $connection->prepare("INSERT INTO {$table} ({$columnList}) VALUES ({$placeholders})");
} else {
    $assignments = implode(' = ?, ', $columns) . ' = ?';
    $statement = $connection->prepare("UPDATE {$table} SET {$assignments} WHERE id = ?");
    $types .= 'i';
    $values['id'] = $id;
}

$parameters = [$types];
foreach ($values as &$value) {
    $parameters[] = &$value;
}
unset($value);

if (!$statement || !call_user_func_array([$statement, 'bind_param'], $parameters) || !$statement->execute()) {
    if (isset($destination) && is_file($destination) && !unlink($destination)) {
        error_log('No se pudo limpiar una imagen subida tras fallar el guardado del producto.');
    }
    respond(500, ['error' => 'No se pudo guardar el registro en la base de datos.']);
}

if ($action === 'update' && $statement->affected_rows === 0) {
    $check = $connection->prepare("SELECT id FROM {$table} WHERE id = ?");
    if (!$check || !$check->bind_param('i', $id) || !$check->execute() || $check->get_result()->num_rows === 0) {
        respond(404, ['error' => 'El registro ya no existe.']);
    }
}

if ($resource === 'productos' && $action === 'update' && $oldImage !== '' && $oldImage !== $imagePath) {
    delete_unused_product_image($connection, $oldImage);
}

respond(200, ['message' => 'Cambios guardados correctamente.']);

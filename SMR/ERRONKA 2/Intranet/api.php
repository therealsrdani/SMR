<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function respond(int $status, array $body): void
{
    http_response_code($status);
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (($_SESSION['authenticated'] ?? false) !== true) {
    respond(401, ['error' => 'Inicia sesión para continuar.']);
}

$role = strtolower(trim((string) ($_SESSION['rol'] ?? '')));
$canManage = in_array($role, ['director', 'director ejecutivo', 'gerente'], true);
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
        if ($showAll && $role !== 'director') {
            respond(403, ['error' => 'Solo el rol Director puede consultar todas las solicitudes.']);
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
        if ($role !== 'director') {
            respond(403, ['error' => 'Solo el rol Director puede cambiar el estado de una solicitud.']);
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
    if ($role !== 'director' && (int) $request['empleado_id'] !== $userId) {
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

if (!$canManage) {
    respond(403, ['error' => 'Tu rol no tiene permiso para modificar estos datos.']);
}

$csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!is_string($csrfToken) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    respond(403, ['error' => 'La sesión del formulario ha caducado. Recarga la página e inténtalo de nuevo.']);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    respond(400, ['error' => 'La petición no contiene datos válidos.']);
}

$action = $input['action'] ?? '';
$id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$table = $resource;
$columns = $resource === 'productos'
    ? ['nombre', 'descripcion', 'categoria', 'precio', 'imagen_url', 'disponibilidad']
    : ['equipo', 'lunes_jueves', 'viernes', 'modalidad', 'responsable'];

if ($action === 'delete') {
    if ($id === false) {
        respond(400, ['error' => 'Identificador no válido.']);
    }

    $statement = $connection->prepare("DELETE FROM {$table} WHERE id = ?");
    if (!$statement || !$statement->bind_param('i', $id) || !$statement->execute()) {
        respond(500, ['error' => 'No se pudo eliminar el registro.']);
    }

    if ($statement->affected_rows === 0) {
        respond(404, ['error' => 'El registro ya no existe.']);
    }

    respond(200, ['message' => 'Registro eliminado.']);
}

if (!in_array($action, ['create', 'update'], true) || ($action === 'update' && $id === false)) {
    respond(400, ['error' => 'Acción o identificador no válido.']);
}

$values = [];
foreach ($columns as $column) {
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
        || strlen($values['imagen_url']) > 2048
        || ($values['imagen_url'] !== '' && (
            !filter_var($values['imagen_url'], FILTER_VALIDATE_URL)
            || !in_array(strtolower((string) parse_url($values['imagen_url'], PHP_URL_SCHEME)), ['http', 'https'], true)
        ))
        || $values['disponibilidad'] === '' || strlen($values['disponibilidad']) > 100
    ) {
        respond(400, ['error' => 'Revisa el nombre, categoría, precio, imagen y disponibilidad del producto.']);
    }
    $values['precio'] = number_format((float) $price, 2, '.', '');
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
    respond(500, ['error' => 'No se pudo guardar el registro en la base de datos.']);
}

if ($action === 'update' && $statement->affected_rows === 0) {
    $check = $connection->prepare("SELECT id FROM {$table} WHERE id = ?");
    if (!$check || !$check->bind_param('i', $id) || !$check->execute() || $check->get_result()->num_rows === 0) {
        respond(404, ['error' => 'El registro ya no existe.']);
    }
}

respond(200, ['message' => 'Cambios guardados correctamente.']);

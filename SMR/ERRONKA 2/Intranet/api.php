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

if ($resource === 'solicitudes') {
    if ($role !== 'director') {
        respond(403, ['error' => 'Solo el rol Director puede consultar las respuestas.']);
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        header('Allow: GET');
        respond(405, ['error' => 'Método no permitido.']);
    }
}

mysqli_report(MYSQLI_REPORT_OFF);
$connection = new mysqli('localhost', 'root', '', 'empresa');

if ($connection->connect_error) {
    respond(500, ['error' => 'No se pudo conectar con la base de datos.']);
}

if (!$connection->set_charset('utf8mb4')) {
    respond(500, ['error' => 'No se pudo configurar la conexión con la base de datos.']);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($resource === 'solicitudes') {
        $statement = $connection->prepare(
            'SELECT id, usuario, tipo, fecha_inicio, fecha_fin, comentarios, creada_en
             FROM solicitudes_ausencia
             ORDER BY creada_en DESC, id DESC'
        );

        if (!$statement || !$statement->execute()) {
            respond(500, ['error' => 'No se pudieron cargar las solicitudes. Comprueba que has ejecutado solicitudes.sql.']);
        }

        $result = $statement->get_result();
        respond(200, ['items' => $result->fetch_all(MYSQLI_ASSOC)]);
    }

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

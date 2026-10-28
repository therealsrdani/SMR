<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: content.html#acceso', true, 303);
    exit;
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (!is_string($username) || !is_string($password)
    || !hash_equals('empleado', $username)
    || !hash_equals('intranet123', $password)) {
    header('Location: content.html?login_error=1#acceso', true, 303);
    exit;
}

$_SESSION['authenticated'] = true;
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Accediendo a la intranet</title>
</head>
<body>
  <p>Accediendo a la intranet...</p>
  <script>
    sessionStorage.setItem("smr-intranet-authenticated", "true");
    window.top.location.replace("../Intranet/portal.html");
  </script>
</body>
</html>
<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!is_string($username) || !is_string($password)) {
        header('Location: content.html?login_error=1#acceso', true, 303);
        exit;
    }

    $conexion = new mysqli(
        'localhost',
        'root',
        '',
        'empresa'
    );

    if ($conexion->connect_error) {
        http_response_code(500);
        exit('Error de conexión con la base de datos.');
    }

    $conexion->set_charset('utf8mb4');

    $stmt = $conexion->prepare(
        'SELECT id, usuario, password, nombre, rol
         FROM usuarios
         WHERE usuario = ?'
    );

    $stmt->bind_param('s', $username);
    $stmt->execute();

    $resultado = $stmt->get_result();
    $usuario = $resultado->fetch_assoc();

    if (!$usuario || !password_verify($password, $usuario['password'])) {
        header('Location: content.html?login_error=1#acceso', true, 303);
        exit;
    }

    session_regenerate_id(true);

    $_SESSION['authenticated'] = true;
    $_SESSION['user_id'] = $usuario['id'];
    $_SESSION['username'] = $usuario['usuario'];
    $_SESSION['nombre'] = $usuario['nombre'];
    $_SESSION['rol'] = $usuario['rol'];

    header('Location: ../Intranet/portal.html', true, 303);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit('Método no permitido.');
}

header('Content-Type: application/javascript; charset=utf-8');
?>
const sections = document.querySelectorAll(".site-content section");
const sectionIds = new Set(Array.from(sections, (section) => section.id));

function showCurrentSection() {
  const requestedSection = window.location.hash.slice(1);
  const activeSection = sectionIds.has(requestedSection) ? requestedSection : "inicio";

  sections.forEach((section) => {
    section.hidden = section.id !== activeSection;
  });
}

showCurrentSection();
window.addEventListener("hashchange", showCurrentSection);

const loginMessage = document.querySelector("#login-message");

if (loginMessage && new URLSearchParams(window.location.search).has("login_error")) {
  loginMessage.textContent = "Usuario o contraseña incorrectos.";
}

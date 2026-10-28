<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!is_string($username) || !is_string($password)
        || !hash_equals('empleado', $username)
        || !hash_equals('intranet123', $password)) {
        header('Location: content.html?login_error=1#acceso', true, 303);
        exit;
    }

    session_regenerate_id(true);
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
    window.top.location.replace("../Intranet/portal.html");
  </script>
</body>
</html>
    <?php
    exit;
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
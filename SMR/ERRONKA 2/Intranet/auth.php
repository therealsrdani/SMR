<?php
session_start();
header('Content-Type: application/javascript; charset=utf-8');
$authenticated = ($_SESSION['authenticated'] ?? false) === true;
$user = null;

if ($authenticated) {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    $role = trim((string) ($_SESSION['rol'] ?? ''));
    $normalizedRole = strtolower($role);
    $roleLabels = [
        'director' => 'Director',
        'director ejecutivo' => 'Director Ejecutivo',
        'gerente' => 'Gerente',
        'empleado' => 'Empleado',
        'cliente' => 'Cliente',
    ];
    $name = trim((string) ($_SESSION['nombre'] ?? ''));
    if ($name === '') {
        $name = (string) ($_SESSION['username'] ?? '');
    }
    $user = [
        'name' => $name,
        'role' => $roleLabels[$normalizedRole] ?? $role,
        'roleKey' => $normalizedRole,
        'canManage' => in_array($normalizedRole, ['director', 'director ejecutivo', 'gerente'], true),
        'csrfToken' => $_SESSION['csrf_token'],
    ];
}
?>
const authenticated = <?= $authenticated ? 'true' : 'false' ?>;
const publicHome = "../Web 1.0/index.html";
window.intranetUser = <?= json_encode($user, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;

if (window.self !== window.top) {
  document.body.classList.add("embedded-page");
}

if (!authenticated) {
  window.top.location.replace(publicHome);
}

if (authenticated && window.self !== window.top) {
  window.parent.postMessage({
    type: "intranet:page",
    page: window.location.pathname.split("/").pop()
  }, "*");
}

if (authenticated && window.intranetUser) {
  const { name, role, roleKey, canManage } = window.intranetUser;
  const isDirector = roleKey === "director";
  const responsesContent = document.querySelector("#responses-content");
  const responsesDenied = document.querySelector("#responses-denied");
  if (responsesContent && responsesDenied) {
    responsesContent.hidden = !isDirector;
    responsesDenied.hidden = isDirector;
  }

  document.querySelectorAll(".profile").forEach((profile) => {
    const nameElement = profile.querySelector(".profile-name");
    const avatar = profile.querySelector(".avatar");
    if (nameElement) {
      const roleElement = nameElement.querySelector("small");
      nameElement.firstChild.textContent = name;
      if (roleElement) roleElement.textContent = role;
    }
    if (avatar) {
      const initials = name.trim().split(/\s+/).slice(0, 2).map((part) => part[0] || "").join("");
      avatar.textContent = initials.toLocaleUpperCase("es") || "U";
      avatar.setAttribute("aria-label", name);
    }
  });

  document.querySelectorAll("[data-role-allow]").forEach((element) => {
    const allowedRoles = element.dataset.roleAllow.split(",").map((value) => value.trim().toLocaleLowerCase("es"));
    const currentRole = role.toLocaleLowerCase("es");
    const directorRole = currentRole === "director ejecutivo";
    element.hidden = !canManage || !(allowedRoles.includes(currentRole) || (directorRole && allowedRoles.includes("director")));
  });

  document.querySelectorAll("[data-role-exact]").forEach((element) => {
    const allowedRoles = element.dataset.roleExact.split(",").map((value) => value.trim().toLocaleLowerCase("es"));
    element.hidden = !allowedRoles.includes(roleKey);
  });
}

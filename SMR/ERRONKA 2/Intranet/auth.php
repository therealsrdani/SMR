<?php
session_start();
require_once __DIR__ . '/permissions.php';
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
        'admin' => 'Admin',
        'guest' => 'Guest',
        'recursos humanos' => 'Recursos Humanos',
        'director' => 'Director',
        'director ejecutivo' => 'Director Ejecutivo',
        'gerente' => 'Gerente',
        'empleado' => 'Empleado',
        'cliente' => 'Cliente',
    ];
    $permissions = permissions_for_role($normalizedRole);
    $name = trim((string) ($_SESSION['nombre'] ?? ''));
    if ($name === '') {
        $name = (string) ($_SESSION['username'] ?? '');
    }
    $user = [
        'name' => $name,
        'role' => $roleLabels[$normalizedRole] ?? $role,
        'roleKey' => $normalizedRole,
        'canCreateProducts' => $permissions['create_product'],
        'canUpdateProducts' => $permissions['update_product'],
        'canDeleteProducts' => $permissions['delete_product'],
        'canCreateSchedules' => $permissions['create_schedule'],
        'canUpdateSchedules' => $permissions['update_schedule'],
        'canDeleteSchedules' => $permissions['delete_schedule'],
        'canUploadDocuments' => $permissions['upload_documents'],
        'canDeleteDocuments' => $permissions['delete_documents'],
        'canDownloadDocuments' => $permissions['download_documents'],
        'canViewAllRequests' => $permissions['view_all_requests'],
        'canChangeRequestStatus' => $permissions['change_request_status'],
        'canReplyToOthers' => $permissions['reply_to_others'],
        'canCreateRequests' => $permissions['create_request'],
        'canReplyToOwnRequests' => $permissions['reply_to_own_requests'],
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
  const { name, role, roleKey } = window.intranetUser;
  const welcomeName = document.querySelector("#welcome-user-name");
  if (welcomeName) {
    welcomeName.textContent = name;
  }

  const roleAliases = roleKey === "director ejecutivo" ? ["director"] : [];
  const responsesContent = document.querySelector("#responses-content");
  const responsesDenied = document.querySelector("#responses-denied");
  if (responsesContent && responsesDenied) {
    responsesContent.hidden = !window.intranetUser.canViewAllRequests;
    responsesDenied.hidden = window.intranetUser.canViewAllRequests;
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
    element.hidden = !allowedRoles.some((allowedRole) => roleKey === allowedRole || roleAliases.includes(allowedRole));
  });

  document.querySelectorAll("[data-role-exact]").forEach((element) => {
    const allowedRoles = element.dataset.roleExact.split(",").map((value) => value.trim().toLocaleLowerCase("es"));
    element.hidden = !allowedRoles.includes(roleKey);
  });
}

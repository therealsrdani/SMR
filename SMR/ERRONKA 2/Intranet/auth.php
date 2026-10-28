<?php
session_start();
header('Content-Type: application/javascript; charset=utf-8');
$authenticated = ($_SESSION['authenticated'] ?? false) === true;
?>
const authenticated = <?= $authenticated ? 'true' : 'false' ?>;
const publicHome = "../Web 1.0/index.html";

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

<?php
session_start();

if (($_SESSION['authenticated'] ?? false) !== true) {
    header('Location: ../Web 1.0/index.html', true, 303);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestType = $_POST['request-type'] ?? '';
    $startDate = $_POST['start-date'] ?? '';
    $endDate = $_POST['end-date'] ?? '';
    $allowedTypes = ['Vacaciones', 'Asunto propio', 'Permiso retribuido', 'Otro permiso'];
    $start = is_string($startDate) ? DateTimeImmutable::createFromFormat('!Y-m-d', $startDate) : false;
    $end = is_string($endDate) ? DateTimeImmutable::createFromFormat('!Y-m-d', $endDate) : false;

    $validDates = $start !== false && $end !== false
        && $start->format('Y-m-d') === $startDate
        && $end->format('Y-m-d') === $endDate
        && $end >= $start;
    $validRequestType = is_string($requestType) && in_array($requestType, $allowedTypes, true);

    if (!$validRequestType || !$validDates) {
        header('Location: solicitudes.html?request_error=1', true, 303);
        exit;
    }

    header('Location: solicitudes.html?request_submitted=1', true, 303);
    exit;
}

header('Content-Type: application/javascript; charset=utf-8');
?>
const leaveForm = document.querySelector("#leave-form");

if (leaveForm) {
  const startDate = document.querySelector("#start-date");
  const endDate = document.querySelector("#end-date");
  const message = document.querySelector("#request-message");

  const query = new URLSearchParams(window.location.search);
  if (query.has("request_submitted")) {
    message.textContent = "Solicitud preparada. En una intranet real, se enviaría al equipo de Personas.";
  } else if (query.has("request_error")) {
    message.textContent = "Revisa el tipo de solicitud y las fechas introducidas.";
  }

  startDate.addEventListener("change", () => {
    endDate.min = startDate.value;
    if (endDate.value && endDate.value < startDate.value) {
      endDate.value = "";
    }
  });
}

const productSearch = document.querySelector("#product-search");

if (productSearch) {
  const products = [...document.querySelectorAll(".product-card")];
  const count = document.querySelector("#catalog-count");
  const emptyState = document.querySelector("#empty-state");

  productSearch.addEventListener("input", () => {
    const query = productSearch.value.trim().toLocaleLowerCase("es");
    let visibleProducts = 0;

    products.forEach((product) => {
      const matches = product.dataset.name.includes(query);
      product.hidden = !matches;
      visibleProducts += Number(matches);
    });

    count.textContent = `${visibleProducts} ${visibleProducts === 1 ? "producto" : "productos"}`;
    emptyState.hidden = visibleProducts > 0;
  });
}

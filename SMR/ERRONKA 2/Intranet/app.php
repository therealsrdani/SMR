<?php
session_start();

if (($_SESSION['authenticated'] ?? false) !== true) {
    header('Location: ../Web 1.0/index.html', true, 303);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf-token'] ?? '';
    if (!is_string($csrfToken) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        http_response_code(403);
        exit('La sesión del formulario ha caducado. Recarga la página e inténtalo de nuevo.');
    }

    $requestType = $_POST['request-type'] ?? '';
    $startDate = $_POST['start-date'] ?? '';
    $endDate = $_POST['end-date'] ?? '';
    $comments = $_POST['comments'] ?? '';
    $allowedTypes = ['Vacaciones', 'Asunto propio', 'Permiso retribuido', 'Otro permiso'];
    $start = is_string($startDate) ? DateTimeImmutable::createFromFormat('!Y-m-d', $startDate) : false;
    $end = is_string($endDate) ? DateTimeImmutable::createFromFormat('!Y-m-d', $endDate) : false;

    $validDates = $start !== false && $end !== false
        && $start->format('Y-m-d') === $startDate
        && $end->format('Y-m-d') === $endDate
        && $end >= $start;
    $validRequestType = is_string($requestType) && in_array($requestType, $allowedTypes, true);

    if (!$validRequestType || !$validDates || !is_string($comments) || strlen($comments) > 5000) {
        header('Location: solicitudes.html?request_error=1', true, 303);
        exit;
    }

    mysqli_report(MYSQLI_REPORT_OFF);
    $conexion = new mysqli('localhost', 'root', '', 'empresa');

    if ($conexion->connect_error) {
        http_response_code(500);
        exit('No se pudo guardar la solicitud. Comprueba la conexión con la base de datos.');
    }

    if (!$conexion->set_charset('utf8mb4')) {
        http_response_code(500);
        exit('No se pudo configurar la conexión con la base de datos.');
    }

    $stmt = $conexion->prepare(
        'INSERT INTO solicitudes_ausencia (empleado_id, usuario, tipo, fecha_inicio, fecha_fin, comentarios)
         VALUES (?, ?, ?, ?, ?, ?)'
    );

    if (!$stmt) {
        http_response_code(500);
        exit('No se pudo preparar el guardado de la solicitud. Comprueba que la tabla esté creada.');
    }

    $employeeId = (int) ($_SESSION['user_id'] ?? 0);
    $username = (string) ($_SESSION['username'] ?? '');
    if ($employeeId < 1 || $username === '') {
        http_response_code(403);
        exit('La sesión no contiene un usuario válido.');
    }
    if (!$stmt->bind_param('isssss', $employeeId, $username, $requestType, $startDate, $endDate, $comments)) {
        http_response_code(500);
        exit('No se pudo preparar el guardado de la solicitud.');
    }

    if (!$stmt->execute()) {
        http_response_code(500);
        exit('No se pudo guardar la solicitud en la base de datos.');
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
  const csrfInput = document.createElement("input");
  csrfInput.type = "hidden";
  csrfInput.name = "csrf-token";
  csrfInput.value = window.intranetUser.csrfToken;
  leaveForm.append(csrfInput);

  const query = new URLSearchParams(window.location.search);
  if (query.has("request_submitted")) {
    message.textContent = "Solicitud guardada correctamente para que la revise el equipo de Personas.";
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

const requestStatusLabels = {
  enviada: "Enviada",
  en_revision: "En revisión",
  cerrada: "Cerrada"
};
const requestDateLabels = new Intl.DateTimeFormat("es", { day: "numeric", month: "long", year: "numeric" });

function formatRequestDate(value) {
  const [year, month, day] = value.split("-").map(Number);
  return requestDateLabels.format(new Date(year, month - 1, day));
}

function createRequestCard(item, canManage) {
  const card = document.createElement("article");
  card.className = "card response-card";
  const heading = document.createElement("div");
  heading.className = "response-card-heading";
  const title = document.createElement("h3");
  title.textContent = item.usuario;
  const tags = document.createElement("div");
  tags.className = "request-tags";
  const type = document.createElement("span");
  type.className = "tag tag-green";
  type.textContent = item.tipo;
  const status = document.createElement("span");
  status.className = `tag request-status status-${item.estado}`;
  status.textContent = requestStatusLabels[item.estado] || item.estado;
  tags.append(type, status);
  heading.append(title, tags);

  const dates = document.createElement("p");
  dates.className = "response-dates";
  dates.textContent = `${formatRequestDate(item.fecha_inicio)} – ${formatRequestDate(item.fecha_fin)}`;
  const comments = document.createElement("p");
  comments.className = "response-comments";
  comments.textContent = item.comentarios || "Sin comentarios adicionales.";
  const submitted = document.createElement("small");
  submitted.className = "response-submitted";
  submitted.textContent = `Enviada ${new Date(item.creada_en.replace(" ", "T")).toLocaleString("es")}`;
  card.append(heading, dates, comments, submitted);

  const messages = document.createElement("div");
  messages.className = "request-messages";
  const messagesTitle = document.createElement("h4");
  messagesTitle.textContent = "Conversación";
  messages.append(messagesTitle);
  if (!item.mensajes.length) {
    const emptyMessages = document.createElement("p");
    emptyMessages.className = "request-no-messages";
    emptyMessages.textContent = "Todavía no hay respuestas.";
    messages.append(emptyMessages);
  } else {
    item.mensajes.forEach((entry) => {
      const message = document.createElement("article");
      message.className = "request-message";
      const messageHeading = document.createElement("strong");
      messageHeading.textContent = `${entry.autor} · ${entry.rol}`;
      const content = document.createElement("p");
      content.textContent = entry.mensaje;
      const timestamp = document.createElement("small");
      timestamp.textContent = new Date(entry.creada_en.replace(" ", "T")).toLocaleString("es");
      message.append(messageHeading, content, timestamp);
      messages.append(message);
    });
  }
  card.append(messages);

  if (canManage && item.estado !== "cerrada") {
    const button = document.createElement("button");
    button.className = "button button-secondary request-state-button";
    button.type = "button";
    button.dataset.requestStatus = item.estado === "enviada" ? "en_revision" : "cerrada";
    button.dataset.requestId = item.id;
    button.textContent = item.estado === "enviada" ? "Marcar en revisión" : "Cerrar solicitud";
    card.append(button);
  }

  if (item.estado === "en_revision") {
    const form = document.createElement("form");
    form.className = "request-message-form";
    form.dataset.requestId = item.id;
    const label = document.createElement("label");
    label.textContent = canManage ? "Responder al emisor" : "Enviar un mensaje";
    const textarea = document.createElement("textarea");
    textarea.name = "mensaje";
    textarea.rows = 3;
    textarea.maxLength = 2000;
    textarea.required = true;
    textarea.placeholder = "Escribe tu mensaje...";
    const submit = document.createElement("button");
    submit.className = "button button-primary";
    submit.type = "submit";
    submit.textContent = "Enviar mensaje";
    form.append(label, textarea, submit);
    card.append(form);
  }
  return card;
}

function setupRequestActions(container, messageElement, refresh) {
  container.addEventListener("submit", async (event) => {
    const form = event.target.closest(".request-message-form");
    if (!form) return;
    event.preventDefault();
    const formData = new FormData(form);
    await postRequestAction({
      action: "message",
      id: Number(form.dataset.requestId),
      mensaje: formData.get("mensaje")
    }, messageElement, refresh);
  });

  container.addEventListener("click", async (event) => {
    const button = event.target.closest("[data-request-status]");
    if (!button) return;
    await postRequestAction({
      action: "status",
      id: Number(button.dataset.requestId),
      estado: button.dataset.requestStatus
    }, messageElement, refresh);
  });
}

async function postRequestAction(data, messageElement, refresh) {
  try {
    const response = await fetch("api.php?resource=solicitudes", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-Token": window.intranetUser.csrfToken
      },
      body: JSON.stringify(data)
    });
    const result = await response.json();
    if (!response.ok) throw new Error(result.error || "No se pudo completar la acción.");
    messageElement.textContent = result.message;
    messageElement.removeAttribute("role");
    await refresh();
  } catch (error) {
    messageElement.textContent = error.message;
    messageElement.setAttribute("role", "alert");
  }
}

const myRequestsList = document.querySelector("#my-requests-list");

if (myRequestsList) {
  const message = document.querySelector("#my-requests-message");
  const emptyState = document.querySelector("#my-requests-empty");
  const renderMyRequests = async () => {
    try {
      const response = await fetch("api.php?resource=solicitudes&scope=mine");
      const result = await response.json();
      if (!response.ok) throw new Error(result.error || "No se pudieron cargar tus solicitudes.");
      myRequestsList.replaceChildren(...result.items.map((item) => createRequestCard(item, false)));
      emptyState.hidden = result.items.length > 0;
      message.textContent = "";
      message.setAttribute("role", "status");
    } catch (error) {
      message.textContent = error.message;
      message.setAttribute("role", "alert");
      emptyState.hidden = true;
    }
  };
  setupRequestActions(myRequestsList, message, renderMyRequests);
  renderMyRequests();
}

const responseList = document.querySelector("#response-list");

if (responseList && window.intranetUser?.roleKey === "director") {
  const message = document.querySelector("#responses-message");
  const typeChart = document.querySelector("#type-chart");
  const monthChart = document.querySelector("#month-chart");
  const emptyState = document.querySelector("#responses-empty");
  const monthLabels = new Intl.DateTimeFormat("es", { month: "short" });
  const createBar = (label, count, maximum) => {
    const row = document.createElement("div");
    row.className = "bar-chart-row";
    const heading = document.createElement("div");
    heading.className = "bar-chart-heading";
    const name = document.createElement("span");
    name.textContent = label;
    const value = document.createElement("strong");
    value.textContent = String(count);
    heading.append(name, value);
    const track = document.createElement("div");
    track.className = "bar-chart-track";
    track.setAttribute("role", "img");
    track.setAttribute("aria-label", `${label}: ${count}`);
    const bar = document.createElement("span");
    bar.className = "bar-chart-bar";
    bar.style.setProperty("--bar-size", `${maximum ? Math.max(4, count / maximum * 100) : 0}%`);
    track.append(bar);
    row.append(heading, track);
    return row;
  };

  const renderResponses = (items) => {
    const typeCounts = new Map();
    const now = new Date();
    const currentMonth = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, "0")}`;
    const currentMonthCount = items.filter((item) => item.creada_en.startsWith(currentMonth)).length;
    items.forEach((item) => typeCounts.set(item.tipo, (typeCounts.get(item.tipo) || 0) + 1));

    document.querySelector("#responses-total").textContent = String(items.length);
    document.querySelector("#responses-month").textContent = String(currentMonthCount);
    const popularType = [...typeCounts.entries()].sort((first, second) => second[1] - first[1])[0];
    document.querySelector("#responses-popular").textContent = popularType ? popularType[0] : "—";
    document.querySelector("#responses-count").textContent = `${items.length} ${items.length === 1 ? "solicitud" : "solicitudes"}`;

    typeChart.replaceChildren();
    const sortedTypes = [...typeCounts.entries()].sort((first, second) => second[1] - first[1]);
    sortedTypes.forEach(([type, count]) => typeChart.append(createBar(type, count, sortedTypes[0][1])));
    if (!sortedTypes.length) {
      typeChart.textContent = "Sin datos para mostrar.";
    }

    monthChart.replaceChildren();
    const months = [];
    for (let offset = 5; offset >= 0; offset -= 1) {
      const date = new Date(now.getFullYear(), now.getMonth() - offset, 1);
      const key = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}`;
      months.push({
        key,
        label: monthLabels.format(date).replace(".", ""),
        count: items.filter((item) => item.creada_en.startsWith(key)).length
      });
    }
    const maxMonthCount = Math.max(...months.map((month) => month.count));
    months.forEach((month) => monthChart.append(createBar(month.label, month.count, maxMonthCount)));

    responseList.replaceChildren();
    items.forEach((item) => {
      responseList.append(createRequestCard(item, true));
    });
    emptyState.hidden = items.length > 0;
  };

  const refreshResponses = async () => {
    const response = await fetch("api.php?resource=solicitudes&scope=all");
    const result = await response.json();
    if (!response.ok) throw new Error(result.error || "No se pudieron cargar las solicitudes.");
    renderResponses(result.items);
  };

  setupRequestActions(responseList, message, async () => {
    try {
      await refreshResponses();
    } catch (error) {
      message.textContent = error.message;
      message.setAttribute("role", "alert");
    }
  });

  refreshResponses()
    .then(() => {
      message.textContent = "";
    })
    .catch((error) => {
      message.textContent = error.message;
      message.setAttribute("role", "alert");
    });
}

const productSearch = document.querySelector("#product-search");

if (productSearch) {
  const grid = document.querySelector("#product-grid");
  const count = document.querySelector("#catalog-count");
  const catalogMessage = document.querySelector("#catalog-message");
  const emptyState = document.querySelector("#empty-state");
  const form = document.querySelector("#product-form");
  const formError = document.querySelector("#product-form-error");
  const canManage = Boolean(window.intranetUser?.canManage);
  let products = [];

  const updateProductCount = () => {
    const query = productSearch.value.trim().toLocaleLowerCase("es");
    let visibleProducts = 0;
    grid.querySelectorAll(".product-card").forEach((product) => {
      const matches = product.dataset.name.includes(query);
      product.hidden = !matches;
      visibleProducts += Number(matches);
    });
    count.textContent = `${visibleProducts} ${visibleProducts === 1 ? "producto" : "productos"}`;
    emptyState.textContent = products.length === 0
      ? "Todavía no hay productos registrados."
      : "No hay productos que coincidan con la búsqueda.";
    emptyState.hidden = visibleProducts > 0;
  };

  const renderProducts = () => {
    grid.querySelectorAll(".product-card").forEach((card) => card.remove());
    products.forEach((product) => {
      const card = document.createElement("article");
      card.className = "product-card";
      card.dataset.name = product.nombre.toLocaleLowerCase("es");

      const imageContainer = document.createElement("div");
      imageContainer.className = "product-image";
      const image = document.createElement("img");
      image.src = product.imagen_url || "img/producto-placeholder.svg";
      image.alt = `Imagen de ${product.nombre}`;
      image.loading = "lazy";
      const category = document.createElement("span");
      category.className = "product-category";
      category.textContent = product.categoria;
      imageContainer.append(image, category);

      const info = document.createElement("div");
      info.className = "product-info";
      const title = document.createElement("h2");
      title.textContent = product.nombre;
      const description = document.createElement("p");
      description.textContent = product.descripcion;
      const bottom = document.createElement("div");
      bottom.className = "product-bottom";
      const price = document.createElement("span");
      price.className = "product-price";
      price.textContent = `${Number(product.precio).toFixed(2)} €`;
      const availability = document.createElement("span");
      availability.className = "stock-label";
      availability.textContent = product.disponibilidad;
      bottom.append(price, availability);
      info.append(title, description, bottom);

      if (canManage) {
        const actions = document.createElement("div");
        actions.className = "management-actions";
        const edit = document.createElement("button");
        edit.className = "button button-secondary";
        edit.type = "button";
        edit.dataset.editProduct = product.id;
        edit.textContent = "Editar";
        const remove = document.createElement("button");
        remove.className = "button button-danger";
        remove.type = "button";
        remove.dataset.deleteProduct = product.id;
        remove.textContent = "Borrar";
        actions.append(edit, remove);
        info.append(actions);
      }

      card.append(imageContainer, info);
      grid.insertBefore(card, emptyState);
    });
    updateProductCount();
  };

  const loadProducts = async () => {
    count.textContent = "Cargando productos...";
    try {
      const response = await fetch("api.php?resource=productos");
      const result = await response.json();
      if (!response.ok) throw new Error(result.error || "No se pudieron cargar los productos.");
      products = result.items;
      renderProducts();
    } catch (error) {
      count.textContent = error.message;
      emptyState.hidden = true;
    }
  };

  const resetProductForm = () => {
    form.reset();
    document.querySelector("#product-id").value = "";
    document.querySelector("#product-form-title").textContent = "Crear producto";
    formError.textContent = "";
    form.hidden = true;
  };

  document.querySelector("#new-product")?.addEventListener("click", () => {
    resetProductForm();
    form.hidden = false;
    form.querySelector('[name="nombre"]').focus();
  });
  document.querySelector("#cancel-product")?.addEventListener("click", resetProductForm);
  productSearch.addEventListener("input", updateProductCount);

  grid.addEventListener("click", async (event) => {
    const button = event.target.closest("button");
    if (!button) return;
    if (button.dataset.editProduct) {
      const product = products.find((item) => String(item.id) === button.dataset.editProduct);
      if (!product) return;
      resetProductForm();
      document.querySelector("#product-id").value = product.id;
      document.querySelector("#product-form-title").textContent = "Editar producto";
      Object.entries(product).forEach(([key, value]) => {
        const input = form.elements.namedItem(key);
        if (input) input.value = value;
      });
      form.hidden = false;
      form.querySelector('[name="nombre"]').focus();
      return;
    }
    if (button.dataset.deleteProduct && window.confirm("¿Quieres borrar este producto?")) {
      await saveRecord("productos", "delete", { id: Number(button.dataset.deleteProduct) }, catalogMessage, loadProducts);
    }
  });

  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    const data = Object.fromEntries(new FormData(form));
    const id = document.querySelector("#product-id").value;
    const saved = await saveRecord("productos", id ? "update" : "create", {
      ...data,
      precio: Number(data.precio),
      ...(id ? { id: Number(id) } : {})
    }, formError, loadProducts);
    if (saved) {
      catalogMessage.textContent = formError.textContent;
      resetProductForm();
    }
  });

  loadProducts();
}

async function saveRecord(resource, action, data, messageElement, refresh) {
  try {
    const response = await fetch(`api.php?resource=${resource}`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-Token": window.intranetUser.csrfToken
      },
      body: JSON.stringify({ action, ...data })
    });
    const result = await response.json();
    if (!response.ok) throw new Error(result.error || "No se pudieron guardar los cambios.");
    messageElement.textContent = result.message;
    await refresh();
    return true;
  } catch (error) {
    messageElement.textContent = error.message;
    return false;
  }
}

const scheduleList = document.querySelector("#schedule-list");

if (scheduleList) {
  const form = document.querySelector("#schedule-form");
  const formError = document.querySelector("#schedule-form-error");
  const message = document.querySelector("#schedule-message");
  const emptyState = document.querySelector("#schedule-empty");
  const canManage = Boolean(window.intranetUser?.canManage);
  let schedules = [];

  const renderSchedules = () => {
    scheduleList.replaceChildren();
    schedules.forEach((schedule) => {
      const row = document.createElement("tr");
      const team = document.createElement("th");
      team.scope = "row";
      team.textContent = schedule.equipo;
      row.append(team);
      ["lunes_jueves", "viernes", "modalidad", "responsable"].forEach((key) => {
        const cell = document.createElement("td");
        cell.textContent = schedule[key];
        row.append(cell);
      });
      if (canManage) {
        const actions = document.createElement("td");
        actions.className = "management-actions";
        const edit = document.createElement("button");
        edit.className = "button button-secondary";
        edit.type = "button";
        edit.dataset.editSchedule = schedule.id;
        edit.textContent = "Editar";
        const remove = document.createElement("button");
        remove.className = "button button-danger";
        remove.type = "button";
        remove.dataset.deleteSchedule = schedule.id;
        remove.textContent = "Borrar";
        actions.append(edit, remove);
        row.append(actions);
      }
      scheduleList.append(row);
    });
    emptyState.hidden = schedules.length > 0;
  };

  const loadSchedules = async () => {
    try {
      const response = await fetch("api.php?resource=horarios");
      const result = await response.json();
      if (!response.ok) throw new Error(result.error || "No se pudieron cargar los horarios.");
      schedules = result.items;
      renderSchedules();
    } catch (error) {
      message.textContent = error.message;
    }
  };

  const resetScheduleForm = () => {
    form.reset();
    document.querySelector("#schedule-id").value = "";
    document.querySelector("#schedule-form-title").textContent = "Añadir horario";
    formError.textContent = "";
    form.hidden = true;
  };

  document.querySelector("#new-schedule")?.addEventListener("click", () => {
    resetScheduleForm();
    form.hidden = false;
    form.querySelector('[name="equipo"]').focus();
  });
  document.querySelector("#cancel-schedule")?.addEventListener("click", resetScheduleForm);
  scheduleList.addEventListener("click", async (event) => {
    const button = event.target.closest("button");
    if (!button) return;
    if (button.dataset.editSchedule) {
      const schedule = schedules.find((item) => String(item.id) === button.dataset.editSchedule);
      if (!schedule) return;
      resetScheduleForm();
      document.querySelector("#schedule-id").value = schedule.id;
      document.querySelector("#schedule-form-title").textContent = "Editar horario";
      Object.entries(schedule).forEach(([key, value]) => {
        const input = form.elements.namedItem(key);
        if (input) input.value = value;
      });
      form.hidden = false;
      form.querySelector('[name="equipo"]').focus();
      return;
    }
    if (button.dataset.deleteSchedule && window.confirm("¿Quieres borrar este horario?")) {
      await saveRecord("horarios", "delete", { id: Number(button.dataset.deleteSchedule) }, message, loadSchedules);
    }
  });

  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    const data = Object.fromEntries(new FormData(form));
    const id = document.querySelector("#schedule-id").value;
    const saved = await saveRecord("horarios", id ? "update" : "create", {
      ...data,
      ...(id ? { id: Number(id) } : {})
    }, formError, loadSchedules);
    if (saved) resetScheduleForm();
  });

  loadSchedules();
}

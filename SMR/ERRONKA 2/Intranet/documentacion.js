const documentsContent = document.querySelector("#documents-content");

async function loadDocuments() {
  const query = new URLSearchParams(window.location.search);
  query.set("fragment", "1");

  try {
    const response = await fetch(`documentacion.php?${query}`, {
      credentials: "same-origin",
      headers: { Accept: "text/html" }
    });
    const markup = await response.text();
    if (!markup.trim()) {
      throw new Error("El servidor no devolvió el contenido de documentación.");
    }

    documentsContent.innerHTML = markup;
  } catch (error) {
    documentsContent.innerHTML = "";
    const message = document.createElement("p");
    message.className = "form-message";
    message.setAttribute("role", "alert");
    message.textContent = error instanceof Error
      ? error.message
      : "No se pudo cargar la documentación.";
    documentsContent.append(message);
  }
}

loadDocuments();

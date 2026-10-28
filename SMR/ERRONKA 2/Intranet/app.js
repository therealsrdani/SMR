const leaveForm = document.querySelector("#leave-form");

if (leaveForm) {
  const startDate = document.querySelector("#start-date");
  const endDate = document.querySelector("#end-date");
  const message = document.querySelector("#request-message");

  startDate.addEventListener("change", () => {
    endDate.min = startDate.value;
    if (endDate.value && endDate.value < startDate.value) {
      endDate.value = "";
    }
  });

  leaveForm.addEventListener("submit", (event) => {
    event.preventDefault();
    message.textContent = "Solicitud preparada. En una intranet real, se enviaría al equipo de Personas.";
    leaveForm.reset();
    endDate.removeAttribute("min");
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

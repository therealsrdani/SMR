<?php
header('Content-Type: application/javascript; charset=utf-8');
?>
const contentFrame = document.querySelector("#intranet-content");
const navigationLinks = document.querySelectorAll(".side-nav [data-page]");

window.addEventListener("message", (event) => {
  if (event.source !== contentFrame.contentWindow || !event.data || event.data.type !== "intranet:page") {
    return;
  }

  navigationLinks.forEach((link) => {
    const isCurrentPage = link.dataset.page === event.data.page;
    link.classList.toggle("active", isCurrentPage);

    if (isCurrentPage) {
      link.setAttribute("aria-current", "page");
    } else {
      link.removeAttribute("aria-current");
    }
  });
});

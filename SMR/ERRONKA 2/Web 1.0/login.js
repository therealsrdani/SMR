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

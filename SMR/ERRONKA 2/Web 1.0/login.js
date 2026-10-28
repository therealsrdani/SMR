const demoUsername = "empleado";
const demoPassword = "intranet123";
const sessionKey = "smr-intranet-authenticated";

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

const loginForm = document.querySelector("#login-form");

if (loginForm) {
  const message = document.querySelector("#login-message");

  loginForm.addEventListener("submit", (event) => {
    event.preventDefault();

    const formData = new FormData(loginForm);
    const username = formData.get("username");
    const password = formData.get("password");

    if (username !== demoUsername || password !== demoPassword) {
      message.textContent = "Usuario o contraseña incorrectos.";
      return;
    }

    sessionStorage.setItem(sessionKey, "true");
    window.top.location.href = "../Intranet/portal.html";
  });
}

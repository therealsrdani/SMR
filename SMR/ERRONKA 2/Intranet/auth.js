const sessionKey = "smr-intranet-authenticated";
const publicHome = "../Web 1.0/index.html";

if (sessionStorage.getItem(sessionKey) !== "true") {
  window.location.replace(publicHome);
}

document.querySelectorAll("[data-logout]").forEach((link) => {
  link.addEventListener("click", (event) => {
    event.preventDefault();
    sessionStorage.removeItem(sessionKey);
    window.location.replace(publicHome);
  });
});

const sessionKey = "smr-intranet-authenticated";
const publicHome = "../Web 1.0/index.html";

if (window.self !== window.top) {
  document.body.classList.add("embedded-page");
}

if (sessionStorage.getItem(sessionKey) !== "true") {
  window.location.replace(publicHome);
}

if (window.self !== window.top) {
  window.parent.postMessage({
    type: "intranet:page",
    page: window.location.pathname.split("/").pop()
  }, "*");
}

document.querySelectorAll("[data-logout]").forEach((link) => {
  link.addEventListener("click", (event) => {
    event.preventDefault();
    sessionStorage.removeItem(sessionKey);
    window.location.replace(publicHome);
  });
});

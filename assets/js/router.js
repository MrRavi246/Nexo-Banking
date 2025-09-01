const routes = {
  '/': () => render(index.html),
  '/feature': () => render(Feature.html),
  '/about': () => render(About.html),
  '/services': () => render(Services.html),
  '/404': () => render(NotFound.html)
};

function render(title, html) {
  const app = document.getElementById('app');
  if (!app) return console.warn('#app element missing');
  document.title = title + ' — Nexo';
  app.innerHTML = html;
}

function routeFromHash() {
  const path = (location.hash.slice(1) || '/').replace(/\/+$/, '') || '/';
  (routes[path] || routes['/404'])();
}

window.addEventListener('hashchange', routeFromHash);
window.addEventListener('load', routeFromHash);
let globalObserver = null;
function iniciarScrollInfinit() {
  const initialSentinel = document.querySelector(".sentinel");
  if (!initialSentinel) return;
  if (globalObserver) globalObserver.disconnect();

  const observerOptions = {
    root: null,
    rootMargin: "300px",
    threshold: 0.1,
  };

  globalObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        const sentinel = entry.target;
        if (sentinel.classList.contains("loading")) return;
        const slug = sentinel.dataset.slug;
        const lastId = sentinel.dataset.lastid;
        const searchTerm = sentinel.dataset.search || "";
        sentinel.classList.add("loading");
        executarCargaInfinita(
          slug,
          lastId,
          sentinel,
          searchTerm,
          globalObserver,
        );
      }
    });
  }, observerOptions);
  function executarCargaInfinita(slug, lastId, sentinel, searchTerm, obs) {
    const tbody = document.querySelector("table tbody");
    const dados = new FormData();
    const formFiltro = document.getElementById("formFiltros");

    if (formFiltro) {
      const filtrosAtivos = new FormData(formFiltro);
      for (let [key, value] of filtrosAtivos.entries()) {
        dados.append(key, value);
      }
    }
    dados.append("slug", slug);
    dados.append("last_id", lastId);
    dados.append("search", searchTerm);
    fetch(window.location.pathname, {
      method: "POST",
      body: dados,
      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
    })
      .then((response) => {
        if (!response.ok) throw new Error();
        return response.text();
      })
      .then((html) => {
        globalObserver.unobserve(sentinel);
        sentinel.remove();
        tbody.insertAdjacentHTML("beforeend", html);
        const novoSentinel = tbody.querySelector(".sentinel");
        if (novoSentinel) obs.observe(novoSentinel);
      })
      .catch(() => {
        sentinel.classList.remove("loading");
        sentinel.innerText = "Erro ao carregar mais dados.";
      });
  }
  globalObserver.observe(initialSentinel);
}
function checkCarregoScrollInfinit() {
  if (document.getElementById("carregaTabela")) iniciarScrollInfinit();
}
document.addEventListener("DOMContentLoaded", checkCarregoScrollInfinit);
document.addEventListener("contentUpdated", checkCarregoScrollInfinit);

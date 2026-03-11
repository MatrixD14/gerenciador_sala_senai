function carregarMais(slug, lastId, btnElement) {
  const tbody = document.querySelector("table tbody");
  const rowBtn = btnElement.closest("tr");
  btnElement.innerText = "Carregando...";
  btnElement.disabled = true;
  const dados = new FormData();
  dados.append("slug", slug);
  dados.append("last_id", lastId);
  fetch(window.location.pathname, {
    method: "POST",
    body: dados,
    headers: {
      "X-Requested-With": "XMLHttpRequest",
    },
  })
    .then((response) => {
      if (!response.ok) throw new Error("Erro na rede");
      return response.text();
    })
    .then((html) => {
      if (rowBtn) rowBtn.remove();
      tbody.insertAdjacentHTML("beforeend", html);
    })
    .catch((error) => {
      console.error("Erro ao carregar mais dados:", error);
      btnElement.innerText = "Erro ao carregar";
      btnElement.disabled = false;
    });
}

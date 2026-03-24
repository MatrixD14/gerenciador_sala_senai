document.addEventListener("input", function (e) {
  if (e.target.classList.contains("custom-search")) {
    const input = e.target;
    const container = input.closest(".custom-select-container");
    const scrollArea = container.querySelector(".options-scroll-area");
    const term = input.value.trim();
    clearTimeout(input.searchTimeout);

    if (term.length === 0 || term.length >= 2) {
      input.searchTimeout = setTimeout(() => {
        executarBusca(input, scrollArea, term);
      }, 300);
    }
  }
});

function executarBusca(input, scrollArea, term) {
  const container = input.closest(".custom-select-container");
  if (!container || !container.dataset.tabela) {
    const options = scrollArea.querySelectorAll(".custom-option");
    options.forEach((opt) => {
      const text = opt.innerText.toLowerCase();
      if (text.includes(term.toLowerCase())) {
        opt.style.opacity = "1";
        opt.style.pointerEvents = "auto";
        opt.style.visibility = "visible";
        opt.style.position = "static";
      } else {
        opt.style.opacity = "0";
        opt.style.pointerEvents = "none";
        opt.style.visibility = "hidden";
        opt.style.position = "absolute";
      }
    });
    return;
  }

  const fd = new FormData();
  fd.append("acao", "fetch_select_options");
  fd.append("tabela", container.dataset.tabela);
  fd.append("coluna", container.dataset.coluna);
  fd.append("value_col", container.dataset.valueCol);
  fd.append("slug", container.dataset.slug);
  fd.append("nome_campo_origem", container.dataset.nomeCampoOrigem);
  fd.append("offset", "0");
  fd.append("search", term);

  scrollArea.innerHTML =
    '<div style="padding:10px; text-align:center;">Buscando...</div>';

  fetch("/buscaList", { method: "POST", body: fd })
    .then((r) => r.text())
    .then((html) => {
      if (!html.trim()) {
        scrollArea.innerHTML =
          '<div style="padding:10px; color: #999;">Nenhum resultado encontrado.</div>';
      } else {
        scrollArea.innerHTML = html;
      }
    })
    .catch(() => {
      scrollArea.innerHTML =
        '<div style="padding:10px; color: red;">Erro na busca.</div>';
    });
}

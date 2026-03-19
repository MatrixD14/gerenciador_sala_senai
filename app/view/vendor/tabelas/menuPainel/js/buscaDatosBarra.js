document.addEventListener("input", function (e) {
  if (e.target.id === "search") {
    const input = e.target;
    const termo = input.value;
    const tabelaPath = window.location.pathname;

    clearTimeout(input.searchTimeout);
    if (termo.length === 0 || termo.length >= 1) {
      input.searchTimeout = setTimeout(() => {
        const dados = new FormData();
        dados.append("search", termo);
        dados.append("is_search_ajax", "true");

        fetch(tabelaPath, {
          method: "POST",
          body: dados,
          headers: { "X-Requested-With": "XMLHttpRequest" },
        })
          .then((response) => response.text())
          .then((html) => {
            const tbody = document.getElementById("carregaTabela");
            if (tbody) {
              tbody.innerHTML = html;
              document.dispatchEvent(new Event("contentUpdated"));
            }
          })
          .catch((error) => console.error("Erro na busca:", error));
      }, 400);
    }
  }
});
function toggleSearch() {
  const wrapper = document.getElementById("search-wrapper");
  const input = document.getElementById("search");
  wrapper.classList.toggle("escondido");
  if (!wrapper || !input) return;
  if (!wrapper.classList.contains("escondido")) {
    input.focus();
  } else {
    if (input.value !== "") {
      input.value = "";
      input.dispatchEvent(new Event("input", { bubbles: true }));
    }
  }
}

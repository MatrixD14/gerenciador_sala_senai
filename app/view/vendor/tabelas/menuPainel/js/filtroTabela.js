function filtraTabele(e) {
  if (e) e.preventDefault();
  const form = e.currentTarget || e.target;
  const url = form.getAttribute("action");
  //   const formData = new FormData(form);
  //   formData.append("is_search_ajax", "true");
  const formData = montarFormDataBase({
    filtros: form,
  });
  loadPagePost(url, formData, true);
}

function montarFormDataBase(extra = {}) {
  const dados = new FormData();

  const form = document.querySelector("form");
  if (form) {
    new FormData(form).forEach((value, key) => {
      if (key === "search") return;
      if (Array.isArray(value)) {
        value.forEach((v) => dados.append(`${key}[]`, v));
      } else {
        dados.append(key, value);
      }
    });
  }
  const searchInput = document.querySelector("#search");
  const search = searchInput ? searchInput.value : "";
  dados.set("search", extra.search ?? search);

  //   document
  //     .querySelectorAll("input[name='show_cols[]']:checked")
  //     .forEach((cb) => dados.append("show_cols[]", cb.value));
  //   const search = document.querySelector("#search")?.value || "";
  //   dados.set("search", search);
  Object.entries(extra).forEach(([k, v]) => {
    if (k === "search") return;
    dados.set(k, v);
  });

  dados.set("is_search_ajax", "true");
  console.log("SEARCH FINAL:", dados.get("search"));
  return dados;
}

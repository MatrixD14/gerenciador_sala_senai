(function () {
  const LIMITE_LINHAS = 150;
  const TAMANHO_PAGINA = 50;
  const MARGEM_OBSERVER = 300;
  if (typeof window.tabelaState !== "undefined") {
    console.warn("tabelaState já existe, reaproveitando");
    var tabelaState = window.tabelaState;
  } else {
    var tabelaState = {
      slug: null,
      offset: 0,
      isLoading: false,
      loadingUp: false,
      loadingDown: false,
      hasMoreUp: false,
      hasMoreDown: true,
      observer: null,
      search: "",
      filtros: {},
      blocosCarregados: new Set(),
      ultimoBlocoCompleto: true,
      lockScroll: false,
    };
    window.tabelaState = tabelaState;
  }

  async function carregarBloco(offset, direcao) {
    if (direcao === "up" && tabelaState.loadingUp) return;
    if (direcao === "down" && tabelaState.loadingDown) return;
    if (tabelaState.isLoading) return;
    if (tabelaState.blocosCarregados.has(offset)) return;
    if (direcao === "down" && !tabelaState.hasMoreDown) return;
    if (direcao === "up" && !tabelaState.hasMoreUp) return;

    if (direcao === "up") tabelaState.loadingUp = true;
    else tabelaState.loadingDown = true;
    tabelaState.isLoading = true;
    tabelaState.lockScroll = true;

    const container = document.getElementById("carregaTabela");
    const loader = document.getElementById("loading-init");
    if (loader) loader.style.display = "block";

    const formData = new FormData();
    formData.append("offset", offset);
    formData.append("slug", tabelaState.slug);
    formData.append("get_fragmento", "1");
    if (tabelaState.search) formData.append("search", tabelaState.search);

    try {
      const response = await fetch(window.location.href, {
        method: "POST",
        body: formData,
        headers: { "X-Requested-With": "XMLHttpRequest" },
      });
      const json = await response.json();
      if (json.erro) throw new Error(json.erro);

      const novoHtml = TabelaConstrutor.construirLinhas(json);
      if (!novoHtml && offset === 0) {
        container.innerHTML =
          '<tr><td colspan="100%">Nenhum dado encontrado.</td></tr>';
        tabelaState.hasMoreDown = false;
        tabelaState.hasMoreUp = false;
        return;
      }

      tabelaState.ultimoBlocoCompleto = json.has_more;
      if (direcao === "down") {
        const sentinelaBaixo = container.querySelector(".sentinel-bottom");
        if (sentinelaBaixo) sentinelaBaixo.remove();
        container.insertAdjacentHTML("beforeend", novoHtml);
      } else {
        const container = document.getElementById("carregaTabela");
        if (tabelaState.observer) tabelaState.observer.disconnect();

        document.body.style.overflow = "hidden";

        const scrollAntes = window.scrollY;
        const alturaAntes = container.scrollHeight;

        const sentinela = container.querySelector(".sentinel-top");
        if (sentinela) sentinela.remove();
        container.insertAdjacentHTML("afterbegin", novoHtml);
        container.offsetHeight;

        const alturaDepois = container.scrollHeight;
        const delta = alturaDepois - alturaAntes;
        window.scrollTo({
          top: scrollAntes + delta,
          behavior: "instant",
        });
        document.body.style.overflow = "";
      }

      tabelaState.blocosCarregados.add(offset);

      if (direcao === "down") {
        tabelaState.hasMoreDown = tabelaState.ultimoBlocoCompleto;
      }
      if (direcao === "up") {
        tabelaState.hasMoreUp = offset > 0;
      }

      gerenciarLimite(direcao);
    } catch (err) {
      console.error("Erro no carregamento:", err);
    } finally {
      tabelaState.isLoading = false;
      tabelaState.loadingUp = false;
      tabelaState.loadingDown = false;
      if (loader) loader.style.display = "none";
      criarSentinelas();
      setTimeout(() => {
        tabelaState.lockScroll = false;
        configurarObserver();
      }, 1000);
    }
  }

  function criarSentinelas() {
    const container = document.getElementById("carregaTabela");
    if (!container) return;
    container
      .querySelectorAll(".sentinel-top, .sentinel-bottom")
      .forEach((el) => el.remove());

    const nextUp = proximoOffsetUp();

    if (nextUp !== null) {
      const topSentinel = document.createElement("tr");
      topSentinel.className = "sentinel-top";
      topSentinel.setAttribute("data-offset", nextUp);
      topSentinel.innerHTML =
        '<td colspan="100%" style="text-align:center;">▲ Carregar anteriores ▲</td>';
      container.insertAdjacentElement("afterbegin", topSentinel);
    }

    const nextDown = proximoOffsetDown();
    if (nextDown !== null) {
      const bottomSentinel = document.createElement("tr");
      bottomSentinel.className = "sentinel-bottom";
      bottomSentinel.setAttribute("data-offset", nextDown);
      bottomSentinel.innerHTML =
        '<td colspan="100%" style="text-align:center;">▼ Carregar mais ▼</td>';
      container.appendChild(bottomSentinel);
    } else if (!tabelaState.hasMoreDown) {
      const fimMsg = document.createElement("tr");
      fimMsg.innerHTML =
        '<td colspan="100%" style="text-align:center; color:#aaa;">Fim dos resultados</td>';
      container.appendChild(fimMsg);
    }
    console.log("📌 Criando sentinelas - up:", nextUp, "down:", nextDown);
  }

  function atualizarFlags() {
    if (tabelaState.blocosCarregados.size === 0) {
      tabelaState.hasMoreUp = false;
      tabelaState.hasMoreDown = true;
      return;
    }
    const minOffset = Math.min(...tabelaState.blocosCarregados);
    tabelaState.hasMoreUp = minOffset > 0;
    if (!tabelaState.ultimoBlocoCompleto) {
      tabelaState.hasMoreDown = false;
    }
  }
  function proximoOffsetUp() {
    if (!tabelaState.hasMoreUp || tabelaState.blocosCarregados.size === 0)
      return null;
    return Math.min(...tabelaState.blocosCarregados) - TAMANHO_PAGINA;
  }

  function proximoOffsetDown() {
    if (!tabelaState.hasMoreDown || tabelaState.blocosCarregados.size === 0)
      return 0;
    return Math.max(...tabelaState.blocosCarregados) + TAMANHO_PAGINA;
  }

  function removerBloco(offset) {
    const container = document.getElementById("carregaTabela");
    const linhas = Array.from(container.querySelectorAll("tr[data-id]"));
    if (!container) return;
    const fim = offset + TAMANHO_PAGINA;
    let removidos = false;
    linhas.forEach((tr) => {
      const id = parseInt(tr.dataset.id);
      if (id > offset && id <= fim) {
        tr.remove();
        removidos = true;
      }
    });
    if (removidos) {
      tabelaState.blocosCarregados.delete(offset);
    }
  }
  function gerenciarLimite(direcaoCarregada) {
    const container = document.getElementById("carregaTabela");
    const linhas = container.querySelectorAll("tr[data-id]");
    if (linhas.length <= LIMITE_LINHAS) return;

    let blocosRemover = 0;
    const excedente = linhas.length - LIMITE_LINHAS;
    blocosRemover = Math.ceil(excedente / TAMANHO_PAGINA);
    const offsetsOrdenados = Array.from(tabelaState.blocosCarregados).sort(
      (a, b) => a - b,
    );

    if (direcaoCarregada === "down") {
      const remover = offsetsOrdenados.slice(0, blocosRemover);
      remover.forEach((offset) => removerBloco(offset));
    } else {
      const remover = offsetsOrdenados.slice(-blocosRemover);
      remover.forEach((offset) => removerBloco(offset));
    }
    atualizarFlags();
  }
  function configurarObserver() {
    if (tabelaState.observer) tabelaState.observer.disconnect();

    const sentinelas = document.querySelectorAll(
      ".sentinel-top, .sentinel-bottom",
    );
    console.log("👀 Observando sentinelas:", sentinelas.length);
    if (!sentinelas.length) return;

    const observer = new IntersectionObserver(
      async (entries) => {
        if (tabelaState.isLoading) return;

        for (const entry of entries) {
          if (!entry.isIntersecting) continue;
          const sentinel = entry.target;
          const offset = parseInt(sentinel.dataset.offset);
          if (isNaN(offset)) continue;

          observer.disconnect();

          const direcao = sentinel.classList.contains("sentinel-top")
            ? "up"
            : "down";
          await carregarBloco(offset, direcao);
          break;
        }
      },
      {
        rootMargin: `${MARGEM_OBSERVER}px 0px ${MARGEM_OBSERVER}px 0px`,
        threshold: 0.1,
      },
    );

    sentinelas.forEach((s) => observer.observe(s));
    tabelaState.observer = observer;
  }
  window.pesquisarTabela = function (termo) {
    tabelaState.search = termo;
    tabelaState.isLoading = false;
    tabelaState.loadingUp = false;
    tabelaState.loadingDown = false;
    tabelaState.hasMoreUp = false;
    tabelaState.hasMoreDown = true;
    tabelaState.blocosCarregados.clear();
    tabelaState.ultimoBlocoCompleto = true;
    // Limpa o container e carrega o primeiro bloco (offset 0)
    const container = document.getElementById("carregaTabela");
    if (container) container.innerHTML = "";
    if (tabelaState.observer) {
      tabelaState.observer.disconnect();
      tabelaState.observer = null;
    }
    carregarBloco(0, "down");
  };

  window.initTabela = function (slug) {
    tabelaState.slug = slug;
    tabelaState.isLoading = false;
    tabelaState.loadingUp = false;
    tabelaState.loadingDown = false;
    tabelaState.hasMoreUp = false;
    tabelaState.hasMoreDown = true;
    tabelaState.blocosCarregados.clear();
    tabelaState.ultimoBlocoCompleto = true;
    tabelaState.search = "";
    tabelaState.filtros = {};
    const container = document.getElementById("carregaTabela");
    if (container) container.innerHTML = "";
    carregarBloco(0, "down");
  };
})();
document.addEventListener("contentUpdated", function () {
  const tbody = document.getElementById("carregaTabela");
  if (!tbody) return;
  const slug = tbody.dataset.slug;
  if (!slug) return;
  if (window.tabelaState.slug !== slug) {
    if (window.tabelaState.observer) {
      window.tabelaState.observer.disconnect();
      window.tabelaState.observer = null;
    }
    window.tabelaState.slug = slug;
    window.tabelaState.blocosCarregados?.clear();
    window.tabelaState.isLoading = false;
    window.tabelaState.loadingUp = false;
    window.tabelaState.loadingDown = false;
    window.tabelaState.hasMoreUp = false;
    window.tabelaState.hasMoreDown = true;
    window.tabelaState.ultimoBlocoCompleto = true;
    window.tabelaState.search = "";
    window.tabelaState.filtros = {};
  }
  window.initTabela(slug);
});

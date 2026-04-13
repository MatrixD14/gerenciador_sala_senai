import { carregarBloco, carregarFiltrosDoDOM } from "./ScrollCore.js";
import {
  criarSentinelas,
  configurarObserver,
  salvarFiltrosNoDOM,
} from "./domScroll.js";
import { tabelaState } from "./constantsScroll.js";

let debounceTimer;
window.pesquisarTabela = function (termo) {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    console.log(
      `[pesquisarTabela] termo="${termo}", isSearching=${tabelaState.isSearching}, isLoading=${tabelaState.isLoading}`,
    );
    if (tabelaState.isSearching || tabelaState.isLoading) {
      tabelaState.pendingSearch = { type: "search", termo };
      return;
    }
    tabelaState.isSearching = true;
    if (tabelaState.observer) {
      tabelaState.observer.disconnect();
      tabelaState.observer = null;
    }
    tabelaState.search = termo;
    tabelaState.hasMoreUp = false;
    tabelaState.hasMoreDown = true;
    tabelaState.blocosCarregados.clear();
    tabelaState.ultimoBlocoCompleto = true;
    tabelaState.loadingUp = false;
    tabelaState.loadingDown = false;
    tabelaState.isLoading = false;
    const container = document.getElementById("carregaTabela");
    if (container) container.innerHTML = "";

    carregarBloco(tabelaState, 0, "down").finally(() => {
      tabelaState.isSearching = false;
      if (tabelaState.pendingSearch !== null) {
        const pending = tabelaState.pendingSearch;
        tabelaState.pendingSearch = null;
        window.pesquisarTabela(pending);
      }
    });
  }, 300);
};

window.aplicarFiltros = function (dadosFiltro) {
  const container = document.getElementById("carregaTabela");
  if (!container) {
    console.error(
      "Container #carregaTabela não encontrado. Recarregando página...",
    );
    location.reload();
    return;
  }
  if (tabelaState.isLoading) {
    tabelaState.pendingSearch = { type: "filter", filtros: dadosFiltro };
    return;
  }
  tabelaState.filtros = { ...dadosFiltro };
  salvarFiltrosNoDOM(tabelaState.filtros);
  if (tabelaState.observer) {
    tabelaState.observer.disconnect();
    tabelaState.observer = null;
  }

  tabelaState.hasMoreUp = false;
  tabelaState.hasMoreDown = true;
  tabelaState.blocosCarregados.clear();
  tabelaState.ultimoBlocoCompleto = true;
  tabelaState.loadingUp = false;
  tabelaState.loadingDown = false;
  tabelaState.isLoading = false;
  tabelaState.isSearching = true;

  if (container) container.innerHTML = "";
  carregarBloco(tabelaState, 0, "down").finally(() => {
    tabelaState.isSearching = false;
    if (tabelaState.pendingSearch) {
      const pending = tabelaState.pendingSearch;
      tabelaState.pendingSearch = null;
      if (pending.type === "filter") {
        window.aplicarFiltros(pending.filtros);
      } else if (pending.type === "search") {
        window.pesquisarTabela(pending.termo);
      }
    }
  });
};

window.initTabela = async function (
  slug,
  searchTerm = "",
  filtrosIniciais = {},
) {
  if (
    tabelaState.isSearching ||
    tabelaState.isLoading ||
    (tabelaState.blocosCarregados.has(0) && tabelaState.slug === slug)
  )
    return;

  const container = document.getElementById("carregaTabela");
  if (container) container.innerHTML = "";

  const slugMudou = tabelaState.slug !== slug;
  tabelaState.slug = slug;
  const filtrosSalvos = carregarFiltrosDoDOM();
  tabelaState.filtros = { ...filtrosIniciais, ...filtrosSalvos };
  if (slugMudou || searchTerm !== undefined) {
    tabelaState.search = searchTerm;
  }
  tabelaState.isLoading = false;
  tabelaState.loadingUp = false;
  tabelaState.loadingDown = false;
  tabelaState.hasMoreUp = false;
  tabelaState.hasMoreDown = true;
  tabelaState.ultimoBlocoCompleto = true;
  tabelaState.pendingSearch = null;
  tabelaState.isSearching = false;
  tabelaState.blocosCarregados.clear();
  await carregarBloco(tabelaState, 0, "down");
  criarSentinelas(tabelaState);
  configurarObserver(tabelaState, carregarBloco);
};

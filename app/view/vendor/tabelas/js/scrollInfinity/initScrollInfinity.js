import { carregarBloco } from './ScrollCore.js';
import { criarSentinelas, configurarObserver } from './domScroll.js';
import { tabelaState } from './constantsScroll.js';

window.pesquisarTabela = function (termo) {
    console.log(
        `[pesquisarTabela] termo="${termo}", isSearching=${tabelaState.isSearching}, isLoading=${tabelaState.isLoading}`,
    );
    if (tabelaState.isSearching || tabelaState.isLoading) {
        tabelaState.pendingSearch = termo;
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
    const container = document.getElementById('carregaTabela');
    if (container) container.innerHTML = '';

    carregarBloco(0, 'down').finally(() => {
        tabelaState.isSearching = false;
        if (tabelaState.pendingSearch !== null) {
            const pending = tabelaState.pendingSearch;
            tabelaState.pendingSearch = null;
            window.pesquisarTabela(pending);
        }
    });
};

window.initTabela = async function (slug, searchTerm = '') {
    if (tabelaState.isSearching || tabelaState.isLoading) return;

    const slugMudou = tabelaState.slug !== slug;
    tabelaState.slug = slug;
    if (slugMudou || searchTerm !== undefined) {
        tabelaState.search = searchTerm;
    }
    tabelaState.isLoading = false;
    tabelaState.loadingUp = false;
    tabelaState.loadingDown = false;
    tabelaState.hasMoreUp = false;
    tabelaState.hasMoreDown = true;
    tabelaState.blocosCarregados.clear();
    tabelaState.ultimoBlocoCompleto = true;
    tabelaState.pendingSearch = null;
    tabelaState.isSearching = false;
    await carregarBloco(tabelaState, 0, 'down');
    criarSentinelas(tabelaState);
    configurarObserver(tabelaState, carregarBloco);
};

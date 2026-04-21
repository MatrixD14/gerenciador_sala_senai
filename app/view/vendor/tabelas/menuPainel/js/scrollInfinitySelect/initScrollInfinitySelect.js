import { SelectState } from './constantsScrollSelect.js';
import { carregarBloco } from './ScrollCoreSelect.js';
import { criarSentinelas, configurarObserver } from './domScrollSelect.js';

window.initSelect = async function (containerElement) {
    if (SelectState.container === containerElement && SelectState.blocosCarregados.has(0)) {
        return;
    }

    // Reset completo do estado para este container
    SelectState.container = containerElement;
    SelectState.isLoading = false;
    SelectState.loadingUp = false;
    SelectState.loadingDown = false;
    SelectState.hasMoreUp = false;
    SelectState.hasMoreDown = true;
    SelectState.ultimoBlocoCompleto = true;
    SelectState.blocosCarregados.clear();
    SelectState.search = '';
    SelectState.isSearching = false;
    SelectState.pendingSearch = null;
    SelectState.currentOffset = 0;

    // Limpa o scroll area (remove todas as opções, mas preserva a estrutura)
    const scrollArea = containerElement.querySelector('.options-scroll-area');
    if (scrollArea) {
        const allOptions = scrollArea.querySelectorAll('.custom-option:not(.default-option)');
        allOptions.forEach((opt) => opt.remove());
        scrollArea.querySelectorAll('.select-sentinel, .select-sentinel-end').forEach((el) => el.remove());
    }
    // Carrega o primeiro bloco (offset 0)
    await carregarBloco(SelectState, 0, 'down');
    criarSentinelas(SelectState);
    configurarObserver(SelectState, carregarBloco);
};

let searchTimeout;
window.pesquisarSelect = function (termo) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        if (!SelectState.container) return;
        SelectState.search = termo;
        // Reset e recarrega do zero
        SelectState.blocosCarregados.clear();
        SelectState.hasMoreDown = true;
        SelectState.ultimoBlocoCompleto = true;
        SelectState.currentOffset = 0;
        const scrollArea = SelectState.container.querySelector('.options-scroll-area');
        if (scrollArea) {
            const opts = scrollArea.querySelectorAll('.custom-option:not(.default-option)');
            opts.forEach((opt) => opt.remove());
        }
        carregarBloco(SelectState, 0, 'down');
    }, 400);
};

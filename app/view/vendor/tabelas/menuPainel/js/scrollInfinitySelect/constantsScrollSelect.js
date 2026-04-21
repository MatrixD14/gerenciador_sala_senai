export const LIMITE_OPCOES = 150; // máximo de opções no DOM
export const TAMANHO_PAGINA = 50; // tamanho de cada bloco
export const MARGEM_OBSERVER = 300; // margem para o IntersectionObserver

// Estado global para o select atualmente aberto
if (typeof window.SelectState !== 'undefined') {
    console.warn('SelectState já existe, reaproveitando');
    var SelectState = window.SelectState;
} else {
    var SelectState = {
        container: null, // .custom-select-container atual
        isLoading: false,
        loadingUp: false,
        loadingDown: false,
        hasMoreUp: false,
        hasMoreDown: true,
        observer: null,
        search: '',
        blocosCarregados: new Set(),
        ultimoBlocoCompleto: true,
        isSearching: false,
        pendingSearch: null,
        currentOffset: 0,
    };
    window.SelectState = SelectState;
}
export { SelectState };

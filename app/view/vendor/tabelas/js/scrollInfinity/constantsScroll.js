export const LIMITE_LINHAS = 150;
export const TAMANHO_PAGINA = 50;
export const MARGEM_OBSERVER = 300;
if (typeof window.tabelaState !== 'undefined') {
    console.warn('tabelaState já existe, reaproveitando');
    var tabelaState = window.tabelaState;
} else {
    var tabelaState = {
        slug: null,
        isLoading: false,
        loadingUp: false,
        loadingDown: false,
        hasMoreUp: false,
        hasMoreDown: true,
        observer: null,
        search: '',
        filtros: {},
        blocosCarregados: new Set(),
        ultimoBlocoCompleto: true,
        isSearching: false,
        pendingSearch: null,
    };
    window.tabelaState = tabelaState;
}
export { tabelaState };

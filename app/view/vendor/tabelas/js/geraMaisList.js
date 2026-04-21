let ultimoSlugProcessado = null;
let timeoutContentUpdated = null;
document.addEventListener('contentUpdated', function () {
    const tbody = document.getElementById('carregaTabela');
    if (!tbody) return;
    const slug = tbody.dataset.slug;
    if (!slug) return;

    clearTimeout(timeoutContentUpdated);
    timeoutContentUpdated = setTimeout(() => {
        if (window.tabelaState && window.tabelaState.slug !== slug) {
            console.log('Slug alterado, resetando tabelaState');
            window.tabelaState.blocosCarregados.clear();
            window.tabelaState.hasMoreUp = false;
            window.tabelaState.hasMoreDown = true;
            window.tabelaState.slug = slug;
        }

        if (window.tabelaState && window.tabelaState.slug === slug && window.tabelaState.blocosCarregados.has(0)) {
            return;
        }
        ultimoSlugProcessado = slug;
        const currentSearch = window.tabelaState ? window.tabelaState.search : '';
        const filtros = tbody.dataset.filtros ? JSON.parse(tbody.dataset.filtros) : {};

        if (window.tabelaState && window.tabelaState.observer) {
            window.tabelaState.observer.disconnect();
            window.tabelaState.observer = null;
        }
        window.initTabela(slug, currentSearch, filtros);
    }, 100);
});
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('carregaTabela');
    if (!container) {
        console.error('Elemento com id "carregaTabela" não encontrado no DOM.');
        return;
    }
    const slug = container.getAttribute('data-slug');
    let filtrosIniciais = {};
    try {
        const savedFiltros = sessionStorage.getItem(`filtros_${slug}`);
        if (savedFiltros) {
            filtrosIniciais = JSON.parse(savedFiltros);
        } else {
            filtrosIniciais = JSON.parse(container.getAttribute('data-filtros') || '{}');
        }
    } catch (e) {
        console.error('Erro ao ler filtros iniciais');
        filtrosIniciais = {};
    }

    if (slug && typeof window.initTabela === 'function') {
        window.initTabela(slug, '', filtrosIniciais);
    } else {
        console.error('initTabela não carregada ou slug vazio');
    }
});

let ultimoSlugProcessado = null;
let timeoutContentUpdated = null;
document.addEventListener('contentUpdated', function () {
    const tbody = document.getElementById('carregaTabela');
    if (window.tabelaState && window.tabelaState.blocosCarregados.has(0)) {
        console.log('Tabela já inicializada, pulando contentUpdated');
        return;
    }
    if (!tbody) return;
    if (window.tabelaState) {
        window.tabelaState.blocosCarregados.clear();
        window.tabelaState.isLoading = false;
    }
    const slug = tbody.dataset.slug;
    if (!slug) return;
    if (window.tabelaState && (window.tabelaState.isSearching || window.tabelaState.isLoading)) {
        console.warn('contentUpdated ignorado devido a pesquisa/carregamento ativo');
        return;
    }

    clearTimeout(timeoutContentUpdated);
    timeoutContentUpdated = setTimeout(() => {
        if (window.tabelaState && window.tabelaState.slug === slug && ultimoSlugProcessado === slug) {
            return;
        }
        ultimoSlugProcessado = slug;
        const currentSearch = window.tabelaState ? window.tabelaState.search : '';
        const filtros = tbody.dataset.filtros ? JSON.parse(tbody.dataset.filtros) : {};
        window.initTabela(slug, currentSearch, filtros);
    }, 100);
});

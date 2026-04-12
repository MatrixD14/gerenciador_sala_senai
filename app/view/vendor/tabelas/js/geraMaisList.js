let ultimoSlugProcessado = null;
let timeoutContentUpdated = null;
document.addEventListener('contentUpdated', function () {
    const tbody = document.getElementById('carregaTabela');
    if (!tbody) return;
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
        window.initTabela(slug, currentSearch);
    }, 100);
});

let ultimoSlugProcessado = null;
let timeoutContentUpdated = null;
document.addEventListener('contentUpdated', function () {
    const tbody = document.getElementById('carregaTabela');
    if (!tbody) return;
    const slug = tbody.dataset.slug;
    if (!slug) return;
    if (window.tabelaState && window.tabelaState.slug !== slug) {
        console.log('Slug alterado, resetando tabelaState');
        window.tabelaState.blocosCarregados.clear();
        window.tabelaState.hasMoreUp = false;
        window.tabelaState.hasMoreDown = true;
        window.tabelaState.slug = slug;
    }
    clearTimeout(timeoutContentUpdated);
    timeoutContentUpdated = setTimeout(() => {
        if (window.tabelaState && window.tabelaState.slug === slug && window.tabelaState.blocosCarregados.has(0)) {
            return;
        }
        ultimoSlugProcessado = slug;
        const currentSearch = window.tabelaState ? window.tabelaState.search : '';
        const filtros = tbody.dataset.filtros ? JSON.parse(tbody.dataset.filtros) : {};
        window.initTabela(slug, currentSearch, filtros);
    }, 100);
});
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('carregaTabela');
    const slug = container.getAttribute('data-slug');
    console.log('Iniciando tabela para o slug:', slug);
    let filtrosIniciais = {};
    try {
        filtrosIniciais = JSON.parse(container.getAttribute('data-filtros') || '{}');
        console.log('Filtros carregados:', filtrosIniciais);
    } catch (e) {
        console.error('Erro ao ler filtros iniciais');
    }

    if (slug && typeof window.initTabela === 'function') {
        window.initTabela(slug, '', filtrosIniciais);
    } else {
        console.error('initTabela não carregada ou slug vazio');
    }
});

let filtroTimeout;
function filtraTabele(e) {
    if (e) e.preventDefault();

    clearTimeout(filtroTimeout);
    filtroTimeout = setTimeout(() => {
        const formData = new FormData(e.currentTarget || e.target);

        tabelaState.filtros = {};
        tabelaState.offset = 0;

        tabelaState.filtros = {};

        for (let [key, value] of formData.entries()) {
            if (!value) continue;

            if (key.endsWith('[]')) {
                const cleanKey = key.replace('[]', '');

                if (!tabelaState.filtros[cleanKey]) {
                    tabelaState.filtros[cleanKey] = [];
                }

                tabelaState.filtros[cleanKey].push(value);
            } else {
                tabelaState.filtros[key] = value;
            }
        }

        executarFetchNovaTabela();
    }, 300);
}

function executarFetchNovaTabela() {
    const container = document.getElementById('carregaTabela');
    if (!container) {
        return;
    }
    container.innerHTML = '<tr><td colspan="100%">Carregando...</td></tr>';
    fetch(window.location.pathname, {
        method: 'POST',
        body: buildRequestData(),
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
        .then((r) => r.text())
        .then((html) => {
            const container = document.getElementById('carregaTabela');

            if (!container) {
                console.warn('⛔ container sumiu após fetch');
                return;
            }

            container.innerHTML = html;

            const temFiltro = Object.values(tabelaState.filtros).some((v) =>
                Array.isArray(v) ? v.length > 0 : v !== '',
            );

            if (!tabelaState.search && !temFiltro) {
                iniciarScrollInfinit();
            }
        });
}

function buildRequestData(extra = {}) {
    const dados = new FormData();

    // estado atual
    dados.set('slug', tabelaState.slug || '');
    dados.set('search', tabelaState.search || '');
    dados.set('offset', tabelaState.offset || 0);

    Object.entries(tabelaState.filtros).forEach(([k, v]) => {
        if (Array.isArray(v)) {
            v.forEach((val) => dados.append(k + '[]', val));
        } else {
            dados.set(k, v);
        }
    });

    dados.set('is_search_ajax', 'true');

    return dados;
}

(function () {
    const LIMITE_LINHAS = 150;
    const TAMANHO_PAGINA = 50;
    const MARGEM_OBSERVER = 300;

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
            blocosCarregados: new Set(),
            ultimoBlocoCompleto: true,
            isSearching: false,
            pendingSearch: null,
        };
        window.tabelaState = tabelaState;
    }

    function getReferenciaScroll(container) {
        const trs = container.children;
        for (let i = 0; i < trs.length; i++) {
            const tr = trs[i];
            if (tr.dataset && tr.dataset.id) {
                const rect = tr.getBoundingClientRect();
                if (rect.top >= 0 && rect.top < window.innerHeight) {
                    return { tr, posTop: rect.top };
                }
            }
        }
        for (let i = 0; i < trs.length; i++) {
            const tr = trs[i];
            if (tr.dataset && tr.dataset.id) {
                return { tr, posTop: tr.getBoundingClientRect().top };
            }
        }
        return null;
    }

    function ajustarScrollAposInsercao(scrollContainer, referencia) {
        if (referencia && referencia.tr && referencia.tr.isConnected) {
            const novaPos = referencia.tr.getBoundingClientRect().top;
            const delta = novaPos - referencia.posTop;
            scrollContainer.scrollTop += delta;
        }
    }

    function removerBloco(offset, scrollContainer, containerTabela) {
        const linhas = Array.from(containerTabela.querySelectorAll(`tr[data-bloco="${offset}"]`));
        let referencia = getReferenciaScroll(containerTabela);
        let removidos = false;

        for (const tr of linhas) {
            tr.remove();
            removidos = true;
        }

        if (removidos) {
            tabelaState.blocosCarregados.delete(offset);
            if (referencia && referencia.tr.isConnected) {
                const novaPos = referencia.tr.getBoundingClientRect().top;
                const delta = novaPos - referencia.posTop;
                scrollContainer.scrollTop += delta;
            }
        }
    }

    function gerenciarLimite(direcao, scrollContainer, containerTabela) {
        if (!containerTabela) return;
        const linhas = containerTabela.querySelectorAll('tr[data-id]');
        if (linhas.length <= LIMITE_LINHAS) return;

        const excedente = linhas.length - LIMITE_LINHAS;
        const blocosRemover = Math.ceil(excedente / TAMANHO_PAGINA);
        const offsetsOrdenados = Array.from(tabelaState.blocosCarregados).sort((a, b) => a - b);

        let remover = [];
        if (direcao === 'down') {
            remover = offsetsOrdenados.slice(0, blocosRemover);
        } else {
            remover = offsetsOrdenados.slice(-blocosRemover);
        }

        for (const offset of remover) {
            removerBloco(offset, scrollContainer, containerTabela);
        }
        atualizarFlags();
    }

    function atualizarFlags() {
        if (tabelaState.blocosCarregados.size === 0) {
            tabelaState.hasMoreUp = false;
            tabelaState.hasMoreDown = true;
            return;
        }
        const offsets = [...tabelaState.blocosCarregados];
        const minOffset = Math.min(...offsets);

        tabelaState.hasMoreUp = minOffset > 0;
        tabelaState.hasMoreDown = tabelaState.ultimoBlocoCompleto;
    }

    function proximoOffsetUp() {
        if (!tabelaState.hasMoreUp || tabelaState.blocosCarregados.size === 0) return null;
        const next = Math.min(...tabelaState.blocosCarregados) - TAMANHO_PAGINA;

        return next >= 0 ? next : null;
    }

    function proximoOffsetDown() {
        if (!tabelaState.hasMoreDown || tabelaState.blocosCarregados.size === 0) return null;
        const max = Math.max(...tabelaState.blocosCarregados);

        return max + TAMANHO_PAGINA;
    }

    async function carregarBloco(offset, direcao) {
        if (
            tabelaState.isLoading ||
            (direcao === 'up' && tabelaState.loadingUp) ||
            (direcao === 'down' && tabelaState.loadingDown) ||
            tabelaState.blocosCarregados.has(offset) ||
            (direcao === 'down' && !tabelaState.hasMoreDown) ||
            (direcao === 'up' && !tabelaState.hasMoreUp)
        )
            return;

        if (offset < 0) return;

        if (direcao === 'up') tabelaState.loadingUp = true;
        else tabelaState.loadingDown = true;
        tabelaState.isLoading = true;

        const container = document.getElementById('carregaTabela');
        const loader = document.getElementById('loading-init');
        if (loader) loader.style.display = 'block';

        const formData = new FormData();
        formData.append('offset', offset);
        formData.append('slug', tabelaState.slug);
        formData.append('get_fragmento', '1');
        if (tabelaState.search) formData.append('search', tabelaState.search);

        try {
            const response = await fetch(window.location.href, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            const json = await response.json();
            if (json.erro) throw new Error(json.erro);

            const novoHtml = TabelaConstrutor.construirLinhas(json);
            if (!novoHtml && offset === 0) {
                container.innerHTML = '<tr><td colspan="100%">Nenhum dado encontrado.</td></tr>';
                tabelaState.hasMoreDown = false;
                tabelaState.hasMoreUp = false;
                tabelaState.blocosCarregados.clear();
                return;
            }

            tabelaState.ultimoBlocoCompleto = json.has_more;
            if (tabelaState.observer) {
                tabelaState.observer.disconnect();
                tabelaState.observer = null;
            }

            if (direcao === 'down') {
                const sentinelaBaixo = container.querySelector('.sentinel-bottom');
                if (sentinelaBaixo) sentinelaBaixo.remove();
                container.insertAdjacentHTML('beforeend', novoHtml);
            } else {
                const scrollContainer = document.querySelector('.table-center');
                if (!scrollContainer) throw new Error('Container .table-center não encontrado');
                // if (tabelaState.observer) tabelaState.observer.disconnect();

                const referencia = getReferenciaScroll(container);
                const sentinelaTopo = container.querySelector('.sentinel-top');
                if (sentinelaTopo) sentinelaTopo.remove();
                container.insertAdjacentHTML('afterbegin', novoHtml);
                ajustarScrollAposInsercao(scrollContainer, referencia);
            }

            const linhasNovas = container.querySelectorAll('tr[data-id]:not([data-bloco])');
            linhasNovas.forEach((tr) => tr.setAttribute('data-bloco', offset));

            // tabelaState.blocosCarregados = new Set([...tabelaState.blocosCarregados, offset].sort((a, b) => a - b));
            tabelaState.blocosCarregados.add(offset);

            if (direcao === 'down') {
                tabelaState.hasMoreDown = tabelaState.ultimoBlocoCompleto;
            }
            if (direcao === 'up') {
                tabelaState.hasMoreUp = offset > 0;
            }
        } catch (err) {
            console.error('Erro no carregamento:', err);
        } finally {
            tabelaState.isLoading = false;
            tabelaState.loadingUp = false;
            tabelaState.loadingDown = false;
            if (loader) loader.style.display = 'none';

            if (tabelaState.pendingSearch !== null) {
                const term = tabelaState.pendingSearch;
                tabelaState.pendingSearch = null;
                window.pesquisarTabela(term);
                return;
            }

            const scrollContainer = document.querySelector('.table-center');
            const removerLimite = () => {
                if (scrollContainer) {
                    gerenciarLimite(direcao, scrollContainer, container);
                } else {
                    gerenciarLimite(direcao, null, container);
                }
                criarSentinelas();
                configurarObserver();
            };

            if (window.requestIdleCallback) {
                requestIdleCallback(removerLimite, { timeout: 200 });
            } else {
                setTimeout(removerLimite, 100);
            }
        }
    }
    function criarSentinelas() {
        const container = document.getElementById('carregaTabela');
        if (!container) return;
        container.querySelectorAll('.sentinel-top, .sentinel-bottom').forEach((el) => el.remove());

        const nextUp = proximoOffsetUp();
        if (nextUp !== null && !tabelaState.isLoading && !tabelaState.isSearching) {
            const topSentinel = document.createElement('tr');
            topSentinel.className = 'sentinel-top';
            topSentinel.setAttribute('data-offset', nextUp);
            topSentinel.innerHTML = '<td colspan="100%" style="text-align:center;">▲ Carregar anteriores ▲</td>';
            container.insertAdjacentElement('afterbegin', topSentinel);
        }

        const nextDown = proximoOffsetDown();
        if (nextDown !== null && !tabelaState.isLoading && !tabelaState.isSearching) {
            const bottomSentinel = document.createElement('tr');
            bottomSentinel.className = 'sentinel-bottom';
            bottomSentinel.setAttribute('data-offset', nextDown);
            bottomSentinel.innerHTML = '<td colspan="100%" style="text-align:center;">▼ Carregar mais ▼</td>';
            container.appendChild(bottomSentinel);
        } else if (!tabelaState.hasMoreDown && !tabelaState.isLoading && tabelaState.blocosCarregados.size > 0) {
            const fimMsg = document.createElement('tr');
            fimMsg.innerHTML = '<td colspan="100%" style="text-align:center; color:#aaa;">Fim dos resultados</td>';
            container.appendChild(fimMsg);
        }
    }

    function configurarObserver() {
        if (tabelaState.observer) {
            tabelaState.observer.disconnect();
            tabelaState.observer = null;
        }

        const sentinelas = document.querySelectorAll('.sentinel-top, .sentinel-bottom');
        if (sentinelas.length === 0) return;

        const observer = new IntersectionObserver(
            async (entries) => {
                if (tabelaState.isLoading || tabelaState.isSearching) return;
                for (const entry of entries) {
                    if (!entry.isIntersecting) continue;
                    const sentinel = entry.target;
                    const offset = parseInt(sentinel.dataset.offset);
                    if (isNaN(offset)) continue;

                    observer.unobserve(sentinel);
                    const direcao = sentinel.classList.contains('sentinel-top') ? 'up' : 'down';
                    await carregarBloco(offset, direcao);
                    break;
                }
            },
            {
                rootMargin: `${MARGEM_OBSERVER}px 0px ${MARGEM_OBSERVER}px 0px`,
                threshold: 0.1,
            },
        );

        sentinelas.forEach((s) => observer.observe(s));
        tabelaState.observer = observer;
    }

    window.pesquisarTabela = function (termo) {
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
            // Verifica se houve pesquisa pendente durante a execução
            if (tabelaState.pendingSearch !== null) {
                const pending = tabelaState.pendingSearch;
                tabelaState.pendingSearch = null;
                window.pesquisarTabela(pending);
            }
        });
    };

    window.initTabela = function (slug, searchTerm = '') {
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
        const container = document.getElementById('carregaTabela');
        if (container) container.innerHTML = '';
        carregarBloco(0, 'down');
    };
})();
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

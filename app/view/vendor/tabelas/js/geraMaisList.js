(function () {
    const LIMITE_LINHAS = 150;
    const TAMANHO_PAGINA = 50;
    const MARGEM_OBSERVER = 200;
    if (typeof window.tabelaState !== 'undefined') {
        console.warn('tabelaState já existe, reaproveitando');
        var tabelaState = window.tabelaState;
    } else {
        var tabelaState = {
            slug: null,
            offset: 0,
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
        };
        window.tabelaState = tabelaState;
    }

    async function carregarBloco(offset, direcao) {
        if (direcao === 'up' && tabelaState.loadingUp) return;
        if (direcao === 'down' && tabelaState.loadingDown) return;
        if (tabelaState.isLoading) return;
        if (tabelaState.blocosCarregados.has(offset)) return;
        if (direcao === 'down' && !tabelaState.hasMoreDown) return;
        if (direcao === 'up' && !tabelaState.hasMoreUp) return;

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
                return;
            }

            // tabelaState.ultimoBlocoCompleto = json.dados.length === TAMANHO_PAGINA;

            tabelaState.ultimoBlocoCompleto = json.has_more;
            const alturaAntes = container.scrollHeight;
            const scrollTopAntes = window.scrollY;

            if (direcao === 'down') {
                const sentinelaBaixo = container.querySelector('.sentinel-bottom');
                if (sentinelaBaixo) sentinelaBaixo.remove();
                container.insertAdjacentHTML('beforeend', novoHtml);
            } else {
                const sentinelaTopo = container.querySelector('.sentinel-top');
                if (sentinelaTopo) sentinelaTopo.remove();
                container.insertAdjacentHTML('afterbegin', novoHtml);
                const alturaDepois = container.scrollHeight;
                const deltaAltura = alturaDepois - alturaAntes;
                window.scrollTo(0, scrollTopAntes + deltaAltura);
            }

            tabelaState.blocosCarregados.add(offset);

            if (direcao === 'down') {
                tabelaState.hasMoreDown = tabelaState.ultimoBlocoCompleto;
            }
            if (direcao === 'up') {
                tabelaState.hasMoreUp = offset > 0;
            }

            gerenciarLimite(direcao);
            criarSentinelas();
        } catch (err) {
            console.error('Erro no carregamento:', err);
        } finally {
            tabelaState.isLoading = false;
            if (direcao === 'up') tabelaState.loadingUp = false;
            else tabelaState.loadingDown = false;
            if (loader) loader.style.display = 'none';
            setTimeout(() => configurarObserver(), 150);
        }
    }

    function criarSentinelas() {
        const container = document.getElementById('carregaTabela');
        if (!container) return;
        container.querySelectorAll('.sentinel-top, .sentinel-bottom').forEach((el) => el.remove());

        const nextUp = proximoOffsetUp();

        if (nextUp !== null) {
            const topSentinel = document.createElement('tr');
            topSentinel.className = 'sentinel-top';
            topSentinel.setAttribute('data-offset', nextUp);
            topSentinel.innerHTML = '<td colspan="100%" style="text-align:center;">▲ Carregar anteriores ▲</td>';
            container.insertAdjacentElement('afterbegin', topSentinel);
        }

        const nextDown = proximoOffsetDown();
        if (nextDown !== null) {
            const bottomSentinel = document.createElement('tr');
            bottomSentinel.className = 'sentinel-bottom';
            bottomSentinel.setAttribute('data-offset', nextDown);
            bottomSentinel.innerHTML = '<td colspan="100%" style="text-align:center;">▼ Carregar mais ▼</td>';
            container.appendChild(bottomSentinel);
        } else if (!tabelaState.hasMoreDown) {
            const fimMsg = document.createElement('tr');
            fimMsg.innerHTML = '<td colspan="100%" style="text-align:center; color:#aaa;">Fim dos resultados</td>';
            container.appendChild(fimMsg);
        }
        console.log('📌 Criando sentinelas - up:', nextUp, 'down:', nextDown);
    }

    function atualizarFlags() {
        if (tabelaState.blocosCarregados.size === 0) {
            tabelaState.hasMoreUp = false;
            tabelaState.hasMoreDown = true;
            return;
        }
        const minOffset = Math.min(...tabelaState.blocosCarregados);
        tabelaState.hasMoreUp = minOffset > 0;
        if (!tabelaState.ultimoBlocoCompleto) {
            tabelaState.hasMoreDown = false;
        }
    }

    // Retorna o próximo offset para cima (ou null se não houver)
    function proximoOffsetUp() {
        if (!tabelaState.hasMoreUp) return null;
        const minOffset = Math.min(...tabelaState.blocosCarregados);
        return minOffset - TAMANHO_PAGINA;
    }

    function proximoOffsetDown() {
        if (!tabelaState.hasMoreDown) return null;
        const maxOffset = Math.max(...tabelaState.blocosCarregados);
        return maxOffset + TAMANHO_PAGINA;
    }

    function removerBloco(offset) {
        const container = document.getElementById('carregaTabela');
        if (!container) return;
        const inicio = offset + 1;
        const fim = offset + TAMANHO_PAGINA;
        const linhas = Array.from(container.querySelectorAll('tr[data-id]'));
        let alturaRemovida = 0;
        linhas.forEach((tr) => {
            const id = parseInt(tr.dataset.id);
            if (id >= inicio && id <= fim) {
                alturaRemovida += tr.offsetHeight;
                tr.remove();
            }
        });
        tabelaState.blocosCarregados.delete(offset);
        const minAtual = tabelaState.blocosCarregados.size ? Math.min(...tabelaState.blocosCarregados) : null;
        if (offset === minAtual && alturaRemovida > 0) {
            window.scrollBy(0, -alturaRemovida);
        }
    }
    function gerenciarLimite(direcaoCarregada) {
        const container = document.getElementById('carregaTabela');
        const linhas = container.querySelectorAll('tr[data-id]');
        if (linhas.length <= LIMITE_LINHAS) return;

        const excedente = linhas.length - LIMITE_LINHAS;
        const blocosRemover = Math.ceil(excedente / TAMANHO_PAGINA);
        const offsetsOrdenados = Array.from(tabelaState.blocosCarregados).sort((a, b) => a - b);

        if (direcaoCarregada === 'down') {
            // Remove blocos do início (mais antigos)
            const remover = offsetsOrdenados.slice(0, blocosRemover);
            remover.forEach((offset) => removerBloco(offset));
        } else if (direcaoCarregada === 'up') {
            // Remove blocos do fim
            const remover = offsetsOrdenados.slice(-blocosRemover);
            remover.forEach((offset) => removerBloco(offset));
        }
        atualizarFlags();
    }
    function configurarObserver() {
        if (tabelaState.observer) {
            tabelaState.observer.disconnect();
        }
        const sentinelas = document.querySelectorAll('.sentinel-top, .sentinel-bottom');
        console.log('👀 Observando sentinelas:', sentinelas.length);
        if (sentinelas.length === 0) return;

        const observer = new IntersectionObserver(
            (entries) => {
                if (tabelaState.isLoading) return;
                for (const entry of entries) {
                    if (!entry.isIntersecting) continue;
                    const sentinel = entry.target;
                    const offset = parseInt(sentinel.dataset.offset);
                    if (isNaN(offset)) continue;

                    if (sentinel.classList.contains('sentinel-top')) {
                        carregarBloco(offset, 'up');
                    } else if (sentinel.classList.contains('sentinel-bottom')) {
                        carregarBloco(offset, 'down');
                    }
                    break; // apenas um por vez
                }
            },
            { rootMargin: `0px 0px ${MARGEM_OBSERVER}px 0px`, threshold: 0.1 },
        );

        sentinelas.forEach((s) => observer.observe(s));
        tabelaState.observer = observer;
    }
    window.pesquisarTabela = function (termo) {
        // Atualiza o termo no estado
        tabelaState.search = termo;
        // Reseta todos os flags e dados
        tabelaState.isLoading = false;
        tabelaState.loadingUp = false;
        tabelaState.loadingDown = false;
        tabelaState.hasMoreUp = false;
        tabelaState.hasMoreDown = true;
        tabelaState.blocosCarregados.clear();
        tabelaState.ultimoBlocoCompleto = true;
        // Limpa o container e carrega o primeiro bloco (offset 0)
        const container = document.getElementById('carregaTabela');
        if (container) container.innerHTML = '';
        if (tabelaState.observer) {
            tabelaState.observer.disconnect();
            tabelaState.observer = null;
        }
        carregarBloco(0, 'down');
    };

    window.initTabela = function (slug) {
        tabelaState.slug = slug;
        tabelaState.isLoading = false;
        tabelaState.loadingUp = false;
        tabelaState.loadingDown = false;
        tabelaState.hasMoreUp = false;
        tabelaState.hasMoreDown = true;
        tabelaState.blocosCarregados.clear();
        tabelaState.ultimoBlocoCompleto = true;
        tabelaState.search = '';
        tabelaState.filtros = {};
        const container = document.getElementById('carregaTabela');
        if (container) container.innerHTML = '';
        carregarBloco(0, 'down');
    };
})();
document.addEventListener('contentUpdated', function () {
    const tbody = document.getElementById('carregaTabela');
    if (!tbody) return;
    const slug = tbody.dataset.slug;
    if (!slug) return;
    if (window.tabelaState.slug !== slug) {
        if (window.tabelaState.observer) {
            window.tabelaState.observer.disconnect();
            window.tabelaState.observer = null;
        }
        window.tabelaState.slug = slug;
        window.tabelaState.blocosCarregados?.clear();
        window.tabelaState.isLoading = false;
        window.tabelaState.loadingUp = false;
        window.tabelaState.loadingDown = false;
        window.tabelaState.hasMoreUp = false;
        window.tabelaState.hasMoreDown = true;
        window.tabelaState.ultimoBlocoCompleto = true;
        window.tabelaState.search = '';
        window.tabelaState.filtros = {};
    }
    window.initTabela(slug);
});

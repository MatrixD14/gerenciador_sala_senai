import { TAMANHO_PAGINA, LIMITE_LINHAS } from './constantsScroll.js';
import { removerBloco, inseirirLinhas, criarSentinelas, configurarObserver } from './domScroll.js';

export function gerenciarLimite(tabelaState, direcao, scrollContainer, containerTabela) {
    if (!containerTabela || !scrollContainer) return;
    const blocosAtuais = tabelaState.blocosCarregados.size;
    const limiteDeBlocos = Math.floor(LIMITE_LINHAS / TAMANHO_PAGINA);
    if (blocosAtuais <= limiteDeBlocos) return;
    const blocosParaRemover = blocosAtuais - limiteDeBlocos;
    const offsetsOrdenados = Array.from(tabelaState.blocosCarregados).sort((a, b) => a - b);
    let remover =
        direcao === 'down' ? offsetsOrdenados.slice(0, blocosParaRemover) : offsetsOrdenados.slice(-blocosParaRemover);

    for (const offset of remover) {
        removerBloco(tabelaState, offset, scrollContainer, containerTabela);
    }
    atualizarFlags(tabelaState);
}

export function atualizarFlags(tabelaState) {
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

export function proximoOffsetUp(tabelaState) {
    if (!tabelaState.hasMoreUp || tabelaState.blocosCarregados.size === 0) return null;
    const next = Math.min(...tabelaState.blocosCarregados) - TAMANHO_PAGINA;

    return next >= 0 ? next : null;
}

export function proximoOffsetDown(tabelaState) {
    if (!tabelaState.hasMoreDown || tabelaState.blocosCarregados.size === 0) return null;
    const max = Math.max(...tabelaState.blocosCarregados);

    return max + TAMANHO_PAGINA;
}

export function NoPodeCarregar(tabelaState, direcao, offset) {
    return (
        tabelaState.isLoading ||
        (direcao === 'up' && tabelaState.loadingUp) ||
        (direcao === 'down' && tabelaState.loadingDown) ||
        tabelaState.blocosCarregados.has(offset) ||
        (direcao === 'down' && !tabelaState.hasMoreDown) ||
        (direcao === 'up' && !tabelaState.hasMoreUp) ||
        offset < 0
    );
}

export function posProcessamento(tabelaState, direcao, offset, container) {
    const linhasNovas = container.querySelectorAll('tr[data-id]:not([data-bloco])');
    linhasNovas.forEach((tr) => tr.setAttribute('data-bloco', offset));
    tabelaState.blocosCarregados.add(offset);
    if (direcao === 'down') {
        tabelaState.hasMoreDown = tabelaState.ultimoBlocoCompleto;
    }
    if (direcao === 'up') {
        tabelaState.hasMoreUp = offset > 0;
    }
}

export function iniciarLoading(tabelaState, direcao) {
    tabelaState.isLoading = true;
    if (direcao === 'up') tabelaState.loadingUp = true;
    else tabelaState.loadingDown = true;

    const loader = document.getElementById('loading-init');
    if (loader) loader.style.display = 'block';
}

export function finalizarLoading(tabelaState) {
    tabelaState.isLoading = false;
    tabelaState.loadingUp = false;
    tabelaState.loadingDown = false;

    const loader = document.getElementById('loading-init');
    if (loader) loader.style.display = 'none';
}

export async function buscarDados(tabelaState, offset) {
    const formData = new FormData();
    formData.append('offset', offset);
    formData.append('slug', tabelaState.slug);
    formData.append('get_fragmento', '1');

    if (tabelaState.search) {
        formData.append('search', tabelaState.search);
    }
    let filtros = tabelaState.filtros;
    if (!filtros || Object.keys(filtros).length === 0) {
        const container = document.getElementById('carregaTabela');
        if (container && container.dataset.filtros) {
            try {
                filtros = JSON.parse(container.dataset.filtros);
                tabelaState.filtros = filtros;
            } catch (e) {}
        }
    }

    for (const [key, value] of Object.entries(filtros)) {
        if (Array.isArray(value)) {
            value.forEach((v) => formData.append(`${key}[]`, v));
        } else if (value !== undefined && value !== '') {
            formData.append(key, value);
        }
    }
    const urlBase = '/' + tabelaState.slug;
    const response = await fetch(urlBase, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
    });

    const text = await response.text();
    if (text.trim().startsWith('<')) {
        throw new Error('Servidor retornou HTML em vez de JSON. Verifique o backend.');
    }
    const json = JSON.parse(text);
    // const json = await response.json();
    // if (json.erro) throw new Error(json.erro);

    return json;
}

export function agendarPosProcessamento(fn) {
    if ('requestIdleCallback' in window) {
        requestIdleCallback(fn, { timeout: 200 });
    } else setTimeout(fn, 100);
}

export async function carregarBloco(tabelaState, offset, direcao) {
    if (NoPodeCarregar(tabelaState, direcao, offset)) {
        console.log(`[carregarBloco] bloqueado: offset=${offset}`);
        return;
    }
    iniciarLoading(tabelaState, direcao);
    const container = document.getElementById('carregaTabela');

    try {
        const json = await buscarDados(tabelaState, offset);

        const html = TabelaConstrutor.construirLinhas(json);
        if (!html && offset === 0) {
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

        inseirirLinhas(container, html, direcao);

        posProcessamento(tabelaState, direcao, offset, container);
    } catch (err) {
        console.error('Erro no carregamento:', err);
    } finally {
        finalizarLoading(tabelaState);

        if (tabelaState.pendingSearch !== null) {
            const pending = tabelaState.pendingSearch;
            tabelaState.pendingSearch = null;
            if (pending.type === 'search') window.pesquisarTabela(pending.termo);
            else if (pending.type === 'filter') window.aplicarFiltros(pending.filtros);
        }

        const scrollContainer = document.querySelector('.table-center');
        agendarPosProcessamento(() => {
            gerenciarLimite(tabelaState, direcao, scrollContainer ? scrollContainer : null, container);
            criarSentinelas(tabelaState);
            configurarObserver(tabelaState, carregarBloco);
        });
    }
}
export function carregarFiltrosDoDOM() {
    const container = document.getElementById('carregaTabela');
    if (container && container.dataset.filtros) {
        try {
            return JSON.parse(container.dataset.filtros);
        } catch (e) {
            return {};
        }
    }
    return {};
}

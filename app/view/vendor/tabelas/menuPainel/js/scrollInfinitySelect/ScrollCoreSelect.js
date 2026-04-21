import { LIMITE_OPCOES, TAMANHO_PAGINA, SelectState as GlobalState } from './constantsScrollSelect.js';
import { removerBloco, inserirOpcoes, criarSentinelas, configurarObserver } from './domScrollSelect.js';

export function gerenciarLimite(SelectState, direcao, scrollArea, containerSelect) {
    if (!containerSelect || !scrollArea) return;
    const blocosAtuais = SelectState.blocosCarregados.size;
    const limiteBlocos = Math.floor(LIMITE_OPCOES / TAMANHO_PAGINA);
    if (blocosAtuais <= limiteBlocos) return;

    const blocosParaRemover = blocosAtuais - limiteBlocos;
    const offsetsOrdenados = Array.from(SelectState.blocosCarregados).sort((a, b) => a - b);
    let remover =
        direcao === 'down' ? offsetsOrdenados.slice(0, blocosParaRemover) : offsetsOrdenados.slice(-blocosParaRemover);

    for (const offset of remover) {
        removerBloco(SelectState, offset, scrollArea, containerSelect);
    }
    atualizarFlags(SelectState);
}

export function atualizarFlags(SelectState) {
    if (SelectState.blocosCarregados.size === 0) {
        SelectState.hasMoreUp = false;
        SelectState.hasMoreDown = true;
        return;
    }
    const offsets = [...SelectState.blocosCarregados];
    const minOffset = Math.min(...offsets);
    SelectState.hasMoreUp = minOffset > 0;
    SelectState.hasMoreDown = SelectState.ultimoBlocoCompleto;
}

export function proximoOffsetUp(SelectState) {
    if (!SelectState.hasMoreUp || SelectState.blocosCarregados.size === 0) return null;
    const min = Math.min(...SelectState.blocosCarregados);
    const next = min - TAMANHO_PAGINA;
    return next >= 0 ? next : null;
}

export function proximoOffsetDown(SelectState) {
    if (!SelectState.hasMoreDown || SelectState.blocosCarregados.size === 0) return null;
    const max = Math.max(...SelectState.blocosCarregados);
    return max + TAMANHO_PAGINA;
}

export function posProcessamento(SelectState, direction, offset, containerSelect) {
    SelectState.blocosCarregados.add(offset);
    if (direction === 'down') {
        SelectState.hasMoreDown = SelectState.ultimoBlocoCompleto;
    } else if (direction === 'up') {
        SelectState.hasMoreUp = offset > 0;
    }
}

export function noPodeCarregar(SelectState, direction, offset) {
    return (
        SelectState.isLoading ||
        (direction === 'up' && SelectState.loadingUp) ||
        (direction === 'down' && SelectState.loadingDown) ||
        SelectState.blocosCarregados.has(offset) ||
        (direction === 'down' && !SelectState.hasMoreDown) ||
        (direction === 'up' && !SelectState.hasMoreUp) ||
        offset < 0
    );
}

export function iniciarLoading(SelectState, direction) {
    SelectState.isLoading = true;
    if (direction === 'up') SelectState.loadingUp = true;
    else SelectState.loadingDown = true;
    // Opcional: mostrar loader no dropdown
}

export function finalizarLoading(SelectState) {
    SelectState.isLoading = false;
    SelectState.loadingUp = false;
    SelectState.loadingDown = false;
}

export async function buscarDados(SelectState, offset) {
    const containerSelect = SelectState.container;
    if (!containerSelect) throw new Error('Nenhum container select ativo');

    const tabela = containerSelect.dataset.tabela;
    const coluna = containerSelect.dataset.coluna;
    const valueCol = containerSelect.dataset.valueCol;
    const slug = containerSelect.dataset.slug;
    const nomeCampoOrigem = containerSelect.dataset.nomeCampoOrigem;

    if (!tabela || !coluna || !valueCol) {
        throw new Error('Atributos dataset incompletos: tabela, coluna, value_col');
    }

    const formData = new FormData();
    formData.append('acao', 'fetch_select_options');
    formData.append('tabela', tabela);
    formData.append('coluna', coluna);
    formData.append('value_col', valueCol);
    formData.append('offset', offset);
    formData.append('slug', slug || '');
    formData.append('search', SelectState.search || '');
    formData.append('nome_campo_origem', nomeCampoOrigem || '');

    const response = await fetch('/buscaList', { method: 'POST', body: formData });
    if (!response.ok) throw new Error(`HTTP ${response.status}`);
    const data = await response.json();
    if (data.error) throw new Error(data.error);
    return { html: data.html, has_more: data.has_more };
}

export function agendarPosProcessamento(fn) {
    if ('requestIdleCallback' in window) requestIdleCallback(fn, { timeout: 200 });
    else setTimeout(fn, 100);
}

export async function carregarBloco(SelectState, offset, direction) {
    if (noPodeCarregar(SelectState, direction, offset)) {
        console.log(`[carregarBloco] bloqueado offset=${offset} dir=${direction}`);
        return;
    }
    iniciarLoading(SelectState, direction);
    const containerSelect = SelectState.container;
    const scrollArea = containerSelect?.querySelector('.options-scroll-area');

    try {
        const { html, has_more } = await buscarDados(SelectState, offset);
        if (!html || !html.trim()) {
            SelectState.ultimoBlocoCompleto = false;
            SelectState.hasMoreDown = false;
            atualizarFlags(SelectState);
            return;
        }
        SelectState.ultimoBlocoCompleto = has_more;

        if (SelectState.observer) {
            SelectState.observer.disconnect();
            SelectState.observer = null;
        }

        inserirOpcoes(containerSelect, html, offset, direction);
        posProcessamento(SelectState, direction, offset, containerSelect);
        if (offset === 0 && direction === 'down') {
            const scrollArea = containerSelect?.querySelector('.options-scroll-area');
            if (scrollArea) {
                // Usa scrollIntoView no primeiro elemento como garantia
                const firstOption = scrollArea.querySelector('.custom-option:not(.option-disabled)');
                if (firstOption) {
                    firstOption.scrollIntoView({ block: 'start', behavior: 'auto' });
                } else {
                    scrollArea.scrollTop = 0;
                }
            }
        }
    } catch (err) {
        console.error('Erro carregando bloco do select:', err);
    } finally {
        finalizarLoading(SelectState);
        agendarPosProcessamento(() => {
            if (scrollArea && containerSelect) {
                gerenciarLimite(SelectState, direction, scrollArea, containerSelect);
            }
            criarSentinelas(SelectState);
            configurarObserver(SelectState, carregarBloco);
        });
    }
}

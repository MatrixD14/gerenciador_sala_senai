import { MARGEM_OBSERVER } from './constantsScroll.js';
import { proximoOffsetUp, proximoOffsetDown } from './ScrollCore.js';
export function getReferenciaScroll(container) {
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

export function ajustarScrollAposInsercao(scrollContainer, referencia) {
    if (referencia && referencia.tr && referencia.tr.isConnected) {
        const novaPos = referencia.tr.getBoundingClientRect().top;
        const delta = novaPos - referencia.posTop;
        scrollContainer.scrollTop += delta;
    }
}

export function removerBloco(tabelaState, offset, scrollContainer, containerTabela) {
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
export function inseirirLinhas(container, html, direcao) {
    if (direcao === 'down') {
        const sentinelaBaixo = container.querySelector('.sentinel-bottom');
        if (sentinelaBaixo) sentinelaBaixo.remove();
        container.insertAdjacentHTML('beforeend', html);
    } else {
        const scrollContainer = document.querySelector('.table-center');
        if (!scrollContainer) throw new Error('Container .table-center não encontrado');
        const referencia = getReferenciaScroll(container);
        const sentinelaTopo = container.querySelector('.sentinel-top');
        if (sentinelaTopo) sentinelaTopo.remove();
        container.insertAdjacentHTML('afterbegin', html);
        ajustarScrollAposInsercao(scrollContainer, referencia);
    }
}
export function criarSentinelas(tabelaState) {
    const container = document.getElementById('carregaTabela');
    if (!container) return;
    container.querySelectorAll('.sentinel-top, .sentinel-bottom').forEach((el) => el.remove());

    const nextUp = proximoOffsetUp(tabelaState);
    if (nextUp !== null && !tabelaState.isLoading && !tabelaState.isSearching) {
        const topSentinel = document.createElement('tr');
        topSentinel.className = 'sentinel-top';
        topSentinel.setAttribute('data-offset', nextUp);
        topSentinel.innerHTML = '<td colspan="100%" style="text-align:center;">▲ Carregar anteriores ▲</td>';
        container.insertAdjacentElement('afterbegin', topSentinel);
    }
    const nextDown = proximoOffsetDown(tabelaState);
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

export function configurarObserver(tabelaState, callbackCarregarBloco) {
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
                await callbackCarregarBloco(tabelaState, offset, direcao);
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

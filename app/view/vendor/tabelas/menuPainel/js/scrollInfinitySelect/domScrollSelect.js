import { MARGEM_OBSERVER } from './constantsScrollSelect.js';
import { proximoOffsetUp, proximoOffsetDown } from './ScrollCoreSelect.js';

export function getReferenciaScroll(scrollArea) {
    const options = scrollArea.querySelectorAll('.custom-option:not(.option-disabled)');
    if (options.length === 0) return null;
    for (let i = 0; i < options.length; i++) {
        const rect = options[i].getBoundingClientRect();
        if (rect.top >= 0 && rect.bottom > 0) {
            return { element: options[i], posTop: rect.top };
        }
    }
    return null;
}

export function ajustarScrollAposRemocao(scrollArea, referencia) {
    if (referencia?.element?.isConnected) {
        const novaPos = referencia.element.getBoundingClientRect().top;
        const delta = novaPos - referencia.posTop;
        scrollArea.scrollTop += delta;
    }
}

export function removerBloco(SelectState, offset, scrollArea, containerSelect) {
    const options = containerSelect.querySelectorAll(`.custom-option[data-pg-offset="${offset}"]`);
    if (!options.length) return;
    const referencia = getReferenciaScroll(scrollArea);
    options.forEach((opt) => opt.remove());
    SelectState.blocosCarregados.delete(offset);
    ajustarScrollAposRemocao(scrollArea, referencia);
}

export function inserirOpcoes(containerSelect, html, bloco, direcao = 'down') {
    const scrollArea = containerSelect.querySelector('.options-scroll-area');
    if (!scrollArea || !html) return;

    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = html;
    const fragment = document.createDocumentFragment();
    const novasOpcoes = tempDiv.querySelectorAll('.custom-option');
    novasOpcoes.forEach((opt) => {
        if (opt.classList.contains('default-option')) return;
        opt.setAttribute('data-pg-offset', bloco);
        fragment.appendChild(opt);
    });

    if (direcao === 'down') {
        const bottomSentinel = scrollArea.querySelector('.select-sentinel-bottom');
        if (bottomSentinel) {
            scrollArea.insertBefore(fragment, bottomSentinel);
        } else {
            scrollArea.appendChild(fragment);
        }
    } else {
        const referencia = getReferenciaScroll(scrollArea);
        const topSentinel = scrollArea.querySelector('.select-sentinel-top');
        const defaultOpt = scrollArea.querySelector('.default-option');
        if (topSentinel) topSentinel.after(fragment);
        else if (defaultOpt) defaultOpt.after(fragment);
        else scrollArea.prepend(fragment);
        ajustarScrollAposRemocao(scrollArea, referencia);
    }
}

export function criarSentinelas(SelectState) {
    const containerSelect = SelectState.container;
    if (!containerSelect) return;
    const scrollArea = containerSelect.querySelector('.options-scroll-area');
    if (!scrollArea) return;

    // Remove todas as sentinelas antigas
    scrollArea.querySelectorAll('.select-sentinel, .select-sentinel-end').forEach((el) => el.remove());

    const nextUp = proximoOffsetUp(SelectState);
    if (nextUp !== null && !SelectState.isLoading && !SelectState.isSearching && SelectState.hasMoreUp) {
        const topSentinel = document.createElement('div');
        topSentinel.className = 'select-sentinel select-sentinel-top';
        topSentinel.setAttribute('data-offset', nextUp);
        topSentinel.setAttribute('data-direction', 'up');
        topSentinel.innerText = '▲ Carregar anteriores ▲';
        const defaultOpt = scrollArea.querySelector('.default-option');
        if (defaultOpt) defaultOpt.after(topSentinel);
        else scrollArea.prepend(topSentinel);
    }

    const nextDown = proximoOffsetDown(SelectState);
    if (nextDown !== null && !SelectState.isLoading && !SelectState.isSearching && SelectState.hasMoreDown) {
        const bottomSentinel = document.createElement('div');
        bottomSentinel.className = 'select-sentinel select-sentinel-bottom';
        bottomSentinel.setAttribute('data-offset', nextDown);
        bottomSentinel.setAttribute('data-direction', 'down');
        bottomSentinel.innerText = '▼ Carregar mais ▼';
        scrollArea.appendChild(bottomSentinel);
    } else if (!SelectState.hasMoreDown && SelectState.blocosCarregados.size > 0) {
        const fimMsg = document.createElement('div');
        fimMsg.className = 'select-sentinel-end';
        fimMsg.innerText = 'Fim dos resultados';
        scrollArea.appendChild(fimMsg);
    }
}

export function configurarObserver(SelectState, callbackCarregarBloco) {
    if (SelectState.observer) {
        SelectState.observer.disconnect();
        SelectState.observer = null;
    }
    if (SelectState.isLoading || SelectState.isSearching) return;

    const containerSelect = SelectState.container;
    if (!containerSelect) return;
    const scrollArea = containerSelect.querySelector('.options-scroll-area');
    if (!scrollArea) return;

    const sentinelas = scrollArea.querySelectorAll('.select-sentinel');
    if (sentinelas.length === 0) return;

    const observer = new IntersectionObserver(
        async (entries) => {
            for (const entry of entries) {
                if (!entry.isIntersecting || !entry.target.isConnected) continue;
                if (SelectState.isLoading || SelectState.isSearching) continue;
                const sentinel = entry.target;
                const offset = parseInt(sentinel.dataset.offset);
                const direction = sentinel.dataset.direction;
                if (isNaN(offset)) continue;

                observer.unobserve(sentinel);
                await callbackCarregarBloco(SelectState, offset, direction);
                break;
            }
        },
        {
            root: scrollArea, // ← CRÍTICO: observa o scroll da div interna
            rootMargin: `${MARGEM_OBSERVER}px 0px ${MARGEM_OBSERVER}px 0px`,
            threshold: 0.1,
        },
    );

    sentinelas.forEach((s) => observer.observe(s));
    SelectState.observer = observer;
}

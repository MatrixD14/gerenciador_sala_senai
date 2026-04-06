let globalObserver = null;
let isFetching = false;
function iniciarScrollInfinit() {
    const container = getTabelaContainer();
    const sentinels = container.querySelectorAll('.sentinel, .sentinel-top');
    if (sentinels.length === 0) return;
    if (!container || isFetching) return;
    if (globalObserver) {
        globalObserver.disconnect();
    }
    const observerOptions = {
        root: null,
        rootMargin: '0px 0px 100px 0px',
        threshold: 0.5,
    };

    globalObserver = new IntersectionObserver((entries) => {
        if (isFetching) return;
        const entry = entries.find((e) => e.isIntersecting);
        if (entry) {
            const sentinel = entry.target;
            if (sentinel.dataset.loading === 'true' || sentinel.dataset.end === 'true') return;
            const slug = sentinel.dataset.slug;
            const offset = sentinel.dataset.offset || 0;
            sentinel.dataset.loading = 'true';
            isFetching = true;
            if (globalObserver) globalObserver.disconnect();
            if (sentinel.classList.contains('sentinel-top')) {
                carregarParaCima(slug, offset, sentinel);
            } else {
                carregarParaBaixo(slug, offset, sentinel);
            }
        }
    }, observerOptions);
    container.querySelectorAll('.sentinel, .sentinel-top').forEach((s) => globalObserver.observe(s));
}
function getTabelaContainer() {
    const el = document.getElementById('carregaTabela');

    if (!el) {
        console.warn('Tabela não encontrada');
        return null;
    }

    return el;
}
function carregarParaBaixo(slug, offset, sentinel) {
    const tbody = document.getElementById('carregaTabela');
    if (!tbody) {
        isFetching = false;
        return;
    }
    tabelaState.offset = parseInt(offset);
    console.log('🚀 Carregando para baixo com offset:', offset, 'offset salvo:', tabelaState.offset);

    fetch(window.location.pathname, {
        method: 'POST',
        body: buildRequestData(),
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
        .then((r) => r.text())
        .then((html) => {
            if (sentinel && sentinel.parentNode) sentinel.remove();
            const temp = document.createElement('tbody');
            temp.innerHTML = html;
            let novasLinhas = Array.from(temp.querySelectorAll('tr[data-id]'));
            novasLinhas = filtrarDuplicadas(tbody, novasLinhas);
            novasLinhas.forEach((tr) => tbody.appendChild(tr));
            atualizarSentinelas(tbody, temp, false);
            controlarLimite(tbody, 'cima');
        })
        .catch((err) => console.error('Erro ↓:', err))
        .finally(() => {
            setTimeout(() => {
                isFetching = false;
                const sentinels = document.querySelectorAll('.sentinel, .sentinel-top');
                let anyVisible = false;
                sentinels.forEach((s) => {
                    const rect = s.getBoundingClientRect();
                    if (rect.top < window.innerHeight && rect.bottom > 0) {
                        anyVisible = true;
                    }
                });
                if (!anyVisible) {
                    iniciarScrollInfinit();
                } else {
                    if (globalObserver) globalObserver.disconnect();
                    globalObserver = null;
                    iniciarScrollInfinit();
                }
            }, 1000);
        });
}
function carregarParaCima(slug, offset, sentinel) {
    const tbody = document.getElementById('carregaTabela');
    if (!tbody) {
        isFetching = false;
        return;
    }

    const alturaAntes = tbody.scrollHeight;
    const scrollAntes = window.scrollY;
    tabelaState.offset = parseInt(offset);
    fetch(window.location.pathname, {
        method: 'POST',
        body: buildRequestData(),
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
        .then((r) => r.text())
        .then((html) => {
            if (sentinel && sentinel.parentNode) sentinel.remove();
            const temp = document.createElement('tbody');
            temp.innerHTML = html;

            let novasLinhas = Array.from(temp.querySelectorAll('tr[data-id]'));
            novasLinhas = filtrarDuplicadas(tbody, novasLinhas);

            novasLinhas.reverse().forEach((tr) => tbody.prepend(tr));

            atualizarSentinelas(tbody, temp, true);
            const ganhoDeAltura = tbody.scrollHeight - alturaAntes;

            window.scrollTo({ top: scrollAntes + ganhoDeAltura, behavior: 'instant' });

            controlarLimite(tbody, 'baixo');
        })
        .catch((err) => console.error('Erro ↑:', err))
        .finally(() => {
            setTimeout(() => {
                isFetching = false;
                const sentinels = document.querySelectorAll('.sentinel, .sentinel-top');
                let anyVisible = false;
                sentinels.forEach((s) => {
                    const rect = s.getBoundingClientRect();
                    if (rect.top < window.innerHeight && rect.bottom > 0) {
                        anyVisible = true;
                    }
                });
                if (!anyVisible) {
                    iniciarScrollInfinit();
                } else {
                    if (globalObserver) globalObserver.disconnect();
                    globalObserver = null;
                    iniciarScrollInfinit();
                }
            }, 1000);
        });
}
function atualizarSentinelas(tbody, temp, isTopo = false) {
    const novaTop = temp.querySelector('.sentinel-top');
    const novaBottom = temp.querySelector('.sentinel');
    tbody.querySelectorAll('.sentinel, .sentinel-top').forEach((el) => el.remove());
    if (novaTop) {
        novaTop.dataset.loading = 'false';
        tbody.prepend(novaTop);
    }
    if (novaBottom) {
        novaBottom.dataset.loading = 'false';
        tbody.appendChild(novaBottom);
    }
}
function controlarLimite(tbody, direcao) {
    const linhas = Array.from(tbody.querySelectorAll('tr[data-id]'));

    if (linhas.length <= 150) return;

    if (direcao === 'cima') {
        const remover = linhas.slice(0, 50);
        let alturaRemovida = 0;
        remover.forEach((el) => {
            alturaRemovida += el.offsetHeight;
            el.remove();
        });
        window.scrollBy(0, -alturaRemovida);
    }

    if (direcao === 'baixo') {
        const remover = linhas.slice(-50);
        remover.forEach((el) => el.remove());
    }
}

function filtrarDuplicadas(tbody, novasLinhas) {
    const idsExistentes = new Set(Array.from(tbody.querySelectorAll('tr[data-id]')).map((tr) => tr.dataset.id));

    return novasLinhas.filter((tr) => !idsExistentes.has(tr.dataset.id));
}
function removerSuave(linhas) {
    let i = 0;
    function step() {
        for (let j = 0; j < 5 && i < linhas.length; j++, i++) {
            linhas[i].remove();
        }
        if (i < linhas.length) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
}
function checkCarregoScrollInfinit() {
    if (document.getElementById('carregaTabela')) iniciarScrollInfinit();
}
document.addEventListener('DOMContentLoaded', checkCarregoScrollInfinit);
document.addEventListener('contentUpdated', checkCarregoScrollInfinit);

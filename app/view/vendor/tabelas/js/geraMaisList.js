let globalObserver = null;
function iniciarScrollInfinit() {
    const initialSentinel = document.querySelector('.sentinel');
    if (!initialSentinel) return;
    if (globalObserver) globalObserver.disconnect();

    const observerOptions = {
        root: null,
        rootMargin: '300px',
        threshold: 0.1,
    };

    globalObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                const sentinel = entry.target;
                if (sentinel.classList.contains('loading')) return;
                if (sentinel.dataset.end === 'true') return;
                const slug = sentinel.dataset.slug;
                const lastId = sentinel.dataset.lastid;
                const searchTerm = sentinel.dataset.search || '';
                const filtros = sentinel.dataset.filtros;
                sentinel.classList.add('loading');
                executarCargaInfinita(slug, lastId, filtros, sentinel, searchTerm, globalObserver);
            }
        });
    }, observerOptions);
    function executarCargaInfinita(slug, lastId, filtros, sentinel, searchTerm, obs) {
        const tbody = document.getElementById('carregaTabela');
        const dados = new FormData();
        if (filtros) {
            try {
                const filtrosObj = JSON.parse(filtros);
                Object.entries(filtrosObj).forEach(([key, value]) => {
                    if (Array.isArray(value)) {
                        value.forEach((v) => dados.append(`${key}[]`, v));
                    } else dados.append(key, value);
                });
            } catch (e) {
                console.error('Erro ao processar filtros do scroll', e);
            }
        }
        dados.set('slug', slug);
        dados.set('last_id', lastId ?? '');
        dados.set('is_search_ajax', 'true');
        dados.set('search', searchTerm ?? '');

        fetch(window.location.pathname, {
            method: 'POST',
            body: dados,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then((response) => {
                if (!response.ok) throw new Error();
                return response.text();
            })
            .then((html) => {
                globalObserver.unobserve(sentinel);
                sentinel.remove();
                if (html.trim() !== '') {
                    tbody.insertAdjacentHTML('beforeend', html);
                    const novoSentinel = tbody.querySelector('.sentinel');
                    if (novoSentinel) obs.observe(novoSentinel);
                }
            })
            .catch(() => {
                sentinel.classList.remove('loading');
                sentinel.innerText = 'Erro ao carregar mais dados.';
            });
    }
    globalObserver.observe(initialSentinel);
}
function checkCarregoScrollInfinit() {
    if (document.getElementById('carregaTabela')) iniciarScrollInfinit();
}
document.addEventListener('DOMContentLoaded', checkCarregoScrollInfinit);
document.addEventListener('contentUpdated', checkCarregoScrollInfinit);

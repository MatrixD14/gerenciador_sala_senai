let currentController = null;
let isLoading = false;
let clickTimeout;
document.addEventListener('click', (e) => {
    const link = e.target.closest('.ajax-link');
    if (!link) return;
    e.preventDefault();
    clearTimeout(clickTimeout);
    clickTimeout = setTimeout(() => {
        loadPage(link.getAttribute('href'));
    }, 300);
});
async function loadPage(url, push = true) {
    if (window.location.pathname === url && push === true) {
        console.log('Já está nesta página');
        return;
    }
    if (isLoading) {
        console.log('Aguardando carregamento anterior...');
        return;
    }
    if (currentController) {
        currentController.abort();
    }

    isLoading = true;
    currentController = new AbortController();

    const mainContent = document.querySelector('.content');
    mainContent.style.opacity = '0.5';

    try {
        const response = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
            signal: currentController.signal,
        });

        if (!response.ok) throw new Error('Erro ao carregar a página');

        const html = await response.text();
        mainContent.innerHTML = html;
        if (push) window.history.pushState(null, '', url);
        mainContent.style.opacity = '1';
        document.dispatchEvent(new Event('contentUpdated'));
    } catch (error) {
        if (error.name === 'AbortError') {
            console.log('Requisição cancelada');
            return;
        }
        console.error('Falha na navegação:', error);
        mainContent.style.opacity = '1';
        mainContent.innerHTML = '<p>Erro ao carregar o conteúdo.</p>';
    } finally {
        isLoading = false;
        currentController = null;
    }
}

window.addEventListener('popstate', () => {
    loadPage(window.location.pathname, false);
});

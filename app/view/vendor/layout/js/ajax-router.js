document.addEventListener('click', (e) => {
    const link = e.target.closest('.ajax-link');
    if (link) {
        e.preventDefault();
        const url = link.getAttribute('href');
        loadPage(url);
    }
});

async function loadPage(url, push = true) {
    const mainContent = document.querySelector('.content');
    mainContent.style.opacity = '0.5';

    try {
        const response = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) throw new Error('Erro ao carregar a página');

        const html = await response.text();
        mainContent.innerHTML = html;
        if (push) window.history.pushState(null, '', url);
        mainContent.style.opacity = '1';
    } catch (error) {
        console.error('Falha na navegação:', error);
        mainContent.style.opacity = '1';
        mainContent.innerHTML = '<p>Erro ao carregar o conteúdo.</p>';
    }
}
window.addEventListener('popstate', () => {
    loadPage(window.location.pathname, false);
});

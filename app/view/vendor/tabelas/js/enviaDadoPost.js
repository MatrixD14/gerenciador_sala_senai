async function loadPagePost(url, formData) {
    const mainContent = document.querySelector('.content');
    mainContent.style.opacity = '0.5';

    try {
        const response = await fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) throw new Error('Erro na requisição');

        const html = await response.text();
        mainContent.innerHTML = html;
        mainContent.style.opacity = '1';

        window.history.pushState(null, '', url);
    } catch (error) {
        console.error('Falha ao enviar POST:', error);
        mainContent.style.opacity = '1';
    }
}

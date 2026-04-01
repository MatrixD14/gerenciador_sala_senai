async function loadPagePost(url, formData = null, saveHistory = true) {
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
        let dataToSave = null;
        if (formData instanceof FormData) dataToSave = Object.fromEntries(formData.entries());
        else dataToSave = formData;

        const state = { url, formData: dataToSave };

        if (saveHistory) history.pushState(state, '', url);
        else history.replaceState(state, '', url);

        document.dispatchEvent(new Event('contentUpdated'));
    } catch (error) {
        console.error('Falha ao enviar POST:', error);
        mainContent.style.opacity = '1';
    }
}

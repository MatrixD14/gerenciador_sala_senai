function filtraTabele(event) {
    event.preventDefault();

    const form = document.getElementById('formFiltro');
    const formData = new FormData(form);
    const tabela = formData.get('tabela');
    const urlDestino = '/' + tabela;
    const filtros = {};
    formData.forEach((value, key) => {
        if (key.endsWith('[]')) {
            const cleanKey = key.slice(0, -2);
            if (!filtros[cleanKey]) filtros[cleanKey] = [];
            filtros[cleanKey].push(value);
        } else {
            filtros[key] = value;
        }
    });
    if (window.tabelaState) {
        window.tabelaState.slug = tabela;
        window.tabelaState.filtros = filtros;
        window.tabelaState.blocosCarregados.clear();
        window.tabelaState.hasMoreUp = false;
        window.tabelaState.hasMoreDown = true;
    }
    loadPagePost(urlDestino, formData, true);
}

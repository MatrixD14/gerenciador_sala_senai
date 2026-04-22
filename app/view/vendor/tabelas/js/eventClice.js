function executarAcao(acao, tabela, id, name) {
    // console.log(`Executando ${acao} na tabela ${tabela} para o ID ${id}`);
    const slug = document.getElementById('carregaTabela').dataset.slug;
    const dados = new FormData();
    dados.append('tabela', tabela);
    dados.append('id', id);
    dados.append('name', name);
    switch (acao) {
        case 'delete':
            loadPagePost('/delete', dados, true);
            if (!slug) return;
            sessionStorage.removeItem(`filtros_${slug}`);
            break;
        case 'edite':
            loadPagePost('/editar', dados, true);
            if (!slug) return;
            sessionStorage.removeItem(`filtros_${slug}`);
            break;
        case 'confirma':
            loadPagePost('/confirma', dados, true);
            if (!slug) return;
            sessionStorage.removeItem(`filtros_${slug}`);
            break;
        case 'add':
        case 'agenda':
            loadPagePost('/insert', dados, true);
            if (!slug) return;
            sessionStorage.removeItem(`filtros_${slug}`);
            break;
        case 'view':
            toggleSearch();
            if (!slug) return;
            sessionStorage.removeItem(`filtros_${slug}`);
            break;
        case 'reivindicar':
            loadPagePost('/reivindicar', dados, true);
            if (!slug) return;
            sessionStorage.removeItem(`filtros_${slug}`);
            break;
        case 'filtro':
            loadPagePost('/filtro', dados, true);
            if (!slug) return;
            sessionStorage.removeItem(`filtros_${slug}`);
            break;
        case 'ViewInPDF':
            loadPagePost('/geraPDF', dados, true);
            break;
        case 'reload':
            if (!slug) return;
            sessionStorage.removeItem(`filtros_${slug}`);
            fetch(`/limpar_filtros`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'tabela=' + encodeURIComponent(slug),
            })
                .then(() => {
                    location.reload();
                })
                .catch(() => {
                    location.reload();
                });
            break;
    }
}

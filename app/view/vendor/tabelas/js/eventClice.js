function executarAcao(acao, tabela, id, name) {
    console.log(`Executando ${acao} na tabela ${tabela} para o ID ${id}`);
    const dados = new FormData();
    dados.append('tabela', tabela);
    dados.append('id', id);
    dados.append('name', name);
    switch (acao) {
        case 'delete':
            loadPagePost('/delete', dados);
            break;
        case 'edite':
            loadPagePost('/editar', dados);
            break;
        case 'add':
        case 'agenda':
            loadPagePost('/insert', dados);
            break;
        case 'reload':
            location.reload();
            break;
    }
}

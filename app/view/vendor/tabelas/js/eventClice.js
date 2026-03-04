function executarAcao(acao, tabela, id, name) {
    console.log(`Executando ${acao} na tabela ${tabela} para o ID ${id}`);

    switch (acao) {
        case 'icon-lixeira':
            const dados = new FormData();
            dados.append('tabela', tabela);
            dados.append('id', id);
            dados.append('name', name);

            loadPagePost('/delete', dados);
            break;
        case 'icon-lapiz':
            window.location.href = `editar.php?tabela=${tabela}&id=${id}`;
            break;
        case 'icon-mais':
            break;
        case 'icon-reload':
            location.reload();
            break;
    }
}

function executarAcao(acao, tabela, id, name) {
  // console.log(`Executando ${acao} na tabela ${tabela} para o ID ${id}`);
  const dados = new FormData();
  dados.append("tabela", tabela);
  dados.append("id", id);
  dados.append("name", name);
  switch (acao) {
    case "delete":
      loadPagePost("/delete", dados, true);
      break;
    case "edite":
      loadPagePost("/editar", dados, true);
      break;
    case "confirma":
      loadPagePost("/confirma", dados, true);
      break;
    case "add":
    case "agenda":
      loadPagePost("/insert", dados, true);
      break;
    case "view":
      loadPagePost("/pesquisa", dados, true);
      break;
    case "revindicar":
      loadPagePost("/revindicar", dados, true);
      break;
    case "back":
      //              overscroll-behavior: contain;
      // touch-action: pan-x pan-y;
      // -webkit-overflow-scrolling: touch;
      break;
    case "reload":
      location.reload();
      break;
  }
}

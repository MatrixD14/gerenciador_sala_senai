function executarAcao(acao, tabela, id, name) {
  console.log(`Executando ${acao} na tabela ${tabela} para o ID ${id}`);
  const dados = new FormData();
  dados.append("tabela", tabela);
  dados.append("id", id);
  dados.append("name", name);
  switch (acao) {
    case "delete":
      loadPagePost("/delete", dados);
      break;
    case "edite":
      loadPagePost("/editar", dados);
      break;
    case "add":
    case "agenda":
      loadPagePost("/insert", dados);
      break;
    case "view":
      loadPagePost("/pesquisa", dados);
      break;
    case "revindicar":
      confirm("avisar");
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

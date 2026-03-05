function executarAcao(acao, tabela, id, name) {
  console.log(`Executando ${acao} na tabela ${tabela} para o ID ${id}`);
  const dados = new FormData();
  dados.append("tabela", tabela);
  dados.append("id", id);
  dados.append("name", name);
  switch (acao) {
    case "icon-lixeira":
      loadPagePost("/delete", dados);
      break;
    case "icon-lapiz":
      loadPagePost("/editar", dados);
      break;
    case "icon-mais":
      break;
    case "icon-reload":
      location.reload();
      break;
  }
}

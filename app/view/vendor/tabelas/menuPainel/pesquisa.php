<?php
$table = $_POST["tabela"];
?>
<form id="form-pesquisa" class="Painel-editor">
    <div class="top-editor">
        <h2>Pesquisar em <?= ucfirst($table) ?></h2>
        <hr>
    </div>
    <div class="editar-dados">
        <input type="hidden" name="table" id="table-search" value="<?= $table ?>">
        <input type="text" name="search" id="search-input" placeholder="Digite sua busca..." autofocus>
    </div>
    <div class="button_editor">
        <button type="button" onclick="buttonVoltar()" id="cancel-editor">Cancelar</button>
        <button id="confirm-editor">Confirmar</button>
    </div>
</form>
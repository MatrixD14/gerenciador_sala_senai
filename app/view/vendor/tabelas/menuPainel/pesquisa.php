<?php
$table = $_POST["tabela"];
?>
<form id="form-pesquisa" class="Painel">
    <div class="top-Painel">
        <h3>Pesquisar em <?= ucfirst($table) ?></h3>
        <hr>
    </div>
    <div class="editar-dados">
        <input type="hidden" name="table" id="table-search" value="<?= $table ?>">
        <input type="text" name="search" class="input-dados" id="search-input" placeholder="Digite sua busca..." autofocus>
    </div>
    <div class="buttons-cal-conf">
        <button type="button" onclick="buttonVoltar()" id="cancel">Cancelar</button>
        <button id="confirm">Confirmar</button>
    </div>
</form>
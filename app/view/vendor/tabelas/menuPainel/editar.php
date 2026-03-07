<?php
$table = $_POST["tabela"];
$id = $_POST["id"];
?>
<form action="/edito" method="post" class="Painel-editor">
    <div class="top-editor">
        <h2>editar</h2>
        <hr>
    </div>
    <div class="editar-dados">
        <input type="hidden" name="id" id="id" value="<?= $id ?>">
        <input type="hidden" name="table" id="table" value="<?= $table ?>">
        <?= gerarFromDinamico::geraFrom($table, $id) ?>
    </div>
    <div class="button_editor">
        <button type="button" onclick="buttonVoltar()" id="cancel-editor">Cancelar</button>
        <button id="confirm-editor">Confirmar</button>
    </div>
</form>
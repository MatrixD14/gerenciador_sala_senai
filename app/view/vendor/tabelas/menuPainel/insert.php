<?php
$table = $_POST["tabela"];
?>
<form action="/inserted" method="post" class="Painel-editor">
    <div class="top-editor">
        <h2>adicionar informação na tabela</h2>
        <hr>
    </div>
    <div class="editar-dados">
        <input type="hidden" name="table" id="table" value="<?= $table ?>">
        <?= gerarFromDinamico::geraFrom($table, null) ?>
    </div>
    <div class="button_editor">
        <button type="button" onclick="buttonVoltar()" id="cancel-editor">Cancelar</button>
        <button id="confirm-editor">Confirmar</button>
    </div>
</form>
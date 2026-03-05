<?php
$name = $_POST["name"];
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
        <?= editor::geraeditor($table) ?>
    </div>
    <div class="button_delete">
        <button type="button" onclick="window.history.back();" id="cancel-editor">Cancelar</button>
        <button id="confirm-editor">Confirmar</button>
    </div>
</form>
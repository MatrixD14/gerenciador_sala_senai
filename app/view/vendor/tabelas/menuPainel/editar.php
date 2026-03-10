<?php
$table = $_POST["tabela"];
$id = $_POST["id"];
?>
<form action="/edito" method="post" class="Painel">
    <div class="top-Painel">
        <h2>editar item da tabela <?= ucfirst($table) ?></h2>
        <hr>
    </div>
    <div class="editar-dados">
        <input type="hidden" name="id" id="id" value="<?= $id ?>">
        <input type="hidden" name="table" id="table" value="<?= $table ?>">
        <?= gerarFromDinamico::geraFrom($table, $id, null) ?>
    </div>
    <div class="buttons-cal-conf">
        <button type="button" onclick="buttonVoltar()" id="cancel">Cancelar</button>
        <button id="confirm">Confirmar</button>
    </div>
</form>
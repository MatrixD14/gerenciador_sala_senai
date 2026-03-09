<?php
$table = $_POST["tabela"];
?>
<form action="/inserted" method="post" class="Painel">
    <div class="top-Painel">
        <h3>adicionar dados na tabela <?= ucfirst($table) ?></h3>
        <hr>
    </div>
    <div class="editar-dados">
        <input type="hidden" name="table" id="table" value="<?= $table ?>">
        <?= gerarFromDinamico::geraFrom($table, null) ?>
    </div>
    <div class="buttons-cal-conf">
        <button type="button" onclick="buttonVoltar()" id="cancel">Cancelar</button>
        <button id="confirm">Confirmar</button>
    </div>
</form>
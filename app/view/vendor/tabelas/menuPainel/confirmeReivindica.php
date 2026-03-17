<?php
$table = $_POST["tabela"];
$id = $_POST["id"];
?>
<form action="/confirmaReivindica" method="post" class="Painel" onsubmit="statusReivindica(event)">
    <div class="top-Painel">
        <h2>aceita reivindicar</h2>
        <hr>
        <div id="menssage-log"></div>
    </div>
    <div class="editar-dados">
        <input type="hidden" name="id" id="id" value="<?= $id ?>">
        <input type="hidden" name="table" id="table" value="<?= $table ?>">
        <?= gerarFromDinamico::geraFrom($table, $id, null, null, true) ?>
    </div>
    <div class="buttons-cal-conf">
        <button type="button" onclick="buttonVoltar()" id="fecha">Fechar</button>
        <button type="button" id="cancel">Cancelar</button>
        <button id="confirm">Confirmar</button>
    </div>
</form>
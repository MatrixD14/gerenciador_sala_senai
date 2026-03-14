<?php
$id = $_POST["id"] ?? "";
$table = "agendamentos";
?>
<form action="/revindicado" method="post" class="Painel">
    <input type="hidden" name="id" id="id" value="<?= $id ?>">
    <div class="top-Painel">
        <h2>revindicar </h2>
        <hr>
    </div>
    <div class="editar-dados">
        <input type="hidden" name="id" id="id" value="<?= $id ?>">
        <?= gerarFromDinamico::geraFrom($table, $id, null, null, true); ?>
        <label for="menssage">Mensagem (Opcional):</label>
        <textarea name="menssage" class="input-dados textarea" id="menssage" placeholder="Gostaria de usar essa sala."></textarea>
    </div>
    <div class="buttons-cal-conf">
        <button type="button" onclick="buttonVoltar()" id="cancel">Cancelar</button>
        <button id="confirm">Confirmar</button>
    </div>
</form>
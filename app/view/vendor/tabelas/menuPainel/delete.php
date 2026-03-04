<?php
$name = $_POST["name"];
$table = $_POST["tabela"];
$id = $_POST["id"];
?>
<form action="/deleted" method="post" class="Painel-delete">
    <div class="top-delete">
        <h2>Deleta</h2>
        <hr>
    </div>
    <p id="delete-info">tem certesa, que vai deleta <?= $id . " - " . $name ?> da tabela <?= $table ?> </p>
    <input type="hidden" name="id" id="id" value="<?= $id ?>">
    <input type="hidden" name="table" id="table" value="<?= $table ?>">
    <input type="hidden" name="name" id="name" value="<?= htmlspecialchars($name) ?>">
    <div class="button_delete">
        <button type="button" window. id="cancel-delete">Cancelar</button>
        <button id="confirm-delete">Confirmar</button>
    </div>
</form>
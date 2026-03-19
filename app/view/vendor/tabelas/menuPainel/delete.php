<?php
$name = $_POST["name"] ?? "";
$table = $_POST["tabela"] ?? "";
$id = $_POST["id"] ?? "";
if (!$table || !$id) {
    header('location: /gerenciado_de_Sala');
    exit;
}
?>
<form action="/deleted" method="post" class="Painel">
    <div class="top-Painel">
        <h2>Deleta</h2>
        <hr>
    </div>
    <p class="delete-info">"<?= $name ?>" serar deletado da tabela <?= $table ?></p>
    <input type="hidden" name="id" id="id" value="<?= $id ?>">
    <input type="hidden" name="table" id="table" value="<?= $table ?>">
    <input type="hidden" name="name" id="name" value="<?= htmlspecialchars($name) ?>">
    <div class="buttons-cal-conf">
        <button type="button" onclick="buttonVoltar()" id="cancel">Cancelar</button>
        <button id="confirm">Confirmar</button>
    </div>
</form>
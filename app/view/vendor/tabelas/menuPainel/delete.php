<?php
$name = $_POST["name"] ?? "";
$table = $_POST["tabela"] ?? "";
$id = $_POST["id"] ?? "";
if (!$table || !$id) {
    header('location: /gerenciado_de_Sala');
    exit;
}

$userAtivo = [
    'id' => $_SESSION['id'] ?? null,
    'privilegio' => $_SESSION['privilegio'] ?? 'normal'
];
try {
    $engine = new FormEngine($table, $id, $userAtivo, true);
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
    exit;
}
?>
<div class="painel-wrapper">
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
            <?php if (!$engine->canSubmit()) echo "<p></p>"; ?>
            <button type="button" onclick="buttonVoltar()" id="cancel">Cancelar</button>
            <?php if (!$engine->canSubmit()) echo "<p></p>";
            else { ?>
                <button id="confirm">Confirmar</button>
            <?php } ?>
        </div>
    </form>
</div>
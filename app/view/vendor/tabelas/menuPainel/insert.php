<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$userData = [
    'id' => $_SESSION['id'] ?? '',
    'privilegio' => $_SESSION['privilegio']
];
$table = $_POST["tabela"];
$presetData = [];
if (isset($_POST['dia'], $_POST['mes'], $_POST['ano'])) {
    $presetData['dia'] = $_POST['ano'] . '-' .
        str_pad($_POST['mes'], 2, "0", STR_PAD_LEFT) . '-' .
        str_pad($_POST['dia'], 2, "0", STR_PAD_LEFT);
}
try {
    $engine = new FormEngine($table, null, $userData, false, $presetData);
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
    exit;
}
?>
<form action="/inserted" method="post" class="Painel">
    <div class="top-Painel">
        <h3>adicionar dados na tabela <?= ucfirst($table) ?></h3>
        <hr>
    </div>
    <div class="editar-dados">
        <input type="hidden" name="table" id="table" value="<?= $table ?>">
        <?= $engine->render() ?>
    </div>
    <div class="buttons-cal-conf">
        <button type="button" onclick="buttonVoltar()" id="cancel">Cancelar</button>
        <button id="confirm">Confirmar</button>
    </div>
</form>
<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$table = $_POST["tabela"] ?? '';
$id = $_POST["id"] ?? '';
if (!$table || !$id) {
    header('location: /gerenciado_de_Sala');
    exit;
}
$userAtivo = [
    'id' => $_SESSION['id'] ?? null,
    'privilegio' => $_SESSION['privilegio'] ?? 'normal'
];
try {
    $engine = new FormEngine($table, $id, $userAtivo, false);
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
    exit;
}
?>
<form action="/edito" method="post" class="Painel">
    <div class="top-Painel">
        <h2>editar item da tabela <?= ucfirst($table) ?></h2>
        <hr>
    </div>
    <div class="editar-dados">
        <input type="hidden" name="id" id="id" value="<?= $id ?>">
        <input type="hidden" name="table" id="table" value="<?= $table ?>">
        <?= $engine->render() ?>
    </div>
    <div class="buttons-cal-conf">
        <button type="button" onclick="buttonVoltar()" id="cancel">Cancelar</button>
        <button id="confirm">Confirmar</button>
    </div>
</form>
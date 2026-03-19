<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$table = $_POST["tabela"] ?? '';
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
<form action="/confirmaReivindica" method="post" class="Painel" onsubmit="statusReivindica(event)">
    <div class="top-Painel">
        <h2>aceita reivindicar</h2>
        <hr>
        <div id="menssage-log"></div>
    </div>
    <div class="editar-dados">
        <input type="hidden" name="id" id="id" value="<?= $id ?>">
        <input type="hidden" name="table" id="table" value="<?= $table ?>">
        <?= $engine->render() ?>
    </div>
    <div class="buttons-cal-conf">
        <button type="button" onclick="buttonVoltar()" id="fecha">Fechar</button>
        <button id="cancel" data-status="cancelo">Cancelar</button>
        <button id="confirm" data-status="confirmado">Confirmar</button>
    </div>
</form>
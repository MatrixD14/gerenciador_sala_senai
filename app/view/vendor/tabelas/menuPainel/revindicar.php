<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$id = $_POST["id"] ?? "";
$table = "agendamentos";
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
<form action="/reivindicado" method="post" class="Painel" onsubmit="bloqueiarevindicar(event)">
    <input type="hidden" name="id" id="id" value="<?= $id ?>">
    <div class="top-Painel">
        <h2>revindicar </h2>
        <hr>
        <div id="menssage-log"></div>
    </div>
    <div class="editar-dados">
        <?= $engine->render() ?>
        <label for="menssage">Mensagem (Opcional):</label>
        <textarea name="menssage" class="input-dados textarea" id="menssage" placeholder="Gostaria de usar essa sala."></textarea>
    </div>
    <div class="buttons-cal-conf">
        <button type="button" onclick="buttonVoltar()" id="cancel">Cancelar</button>
        <button id="confirm">Confirmar</button>
    </div>
</form>
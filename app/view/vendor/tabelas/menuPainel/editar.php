<?php
if (session_status() === PHP_SESSION_NONE) session_start();
date_default_timezone_set('America/Sao_Paulo');
$table = $_POST["tabela"] ?? '';
$id = $_POST["id"] ?? '';
if (!$table || !$id) {
    header('location: /gerenciado_de_Sala');
    exit;
}
$userAtivo = [
    'id' => $_SESSION['id'] ?? null,
    'privilegio' => $_SESSION['privilegio'] ?? 'aluno'
];
try {
    $engine = new FormEngine($table, $id, $userAtivo, false);
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
    exit;
}
$dono = BuscaInfoUser::buscaDonoAgendamento($id);

$bloquearEdicao =
    !$engine->canSubmit() ||
    ($dono['usuario_id'] !== $userAtivo['id'] && $userAtivo['privilegio'] !== "admin");
?>
<div class="painel-wrapper">
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
            <?php if ($bloquearEdicao) echo "<p></p>"; ?>
            <button type="button" onclick="buttonVoltar()" id="cancel">Cancelar</button>
            <?php if ($bloquearEdicao) echo "<p></p>";
            else { ?>
                <button id="confirm">Confirmar</button>
            <?php } ?>
        </div>
    </form>
</div>
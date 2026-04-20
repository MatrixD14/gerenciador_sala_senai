<?php
if (session_status() === PHP_SESSION_NONE) session_start();
date_default_timezone_set('America/Sao_Paulo');
unset($_SESSION['show_cols']);
$table = $_POST["tabela"] ?? '';
$id = $_POST["id"] ?? "";
if (!$table || !$id) {
    header('location: /gerenciado_de_Sala');
    exit;
}
$userAtivo = [
    'id' => $_SESSION['id'] ?? null,
    'privilegio' => $_SESSION['privilegio'] ?? 'aluno'
];
try {
    $engine = new FormEngine($table, $id, $userAtivo, true);
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
    exit;
}
$dadosForm = $engine->getDados();
$statusAtual = $dadosForm['status'] ?? 'pendente';
$dataAgendamento = $dadosForm['dia'] ?? '';
$hoje = date('Y-m-d');
$expirou = ($dataAgendamento < $hoje && $statusAtual === 'pendente');
$jaProcessado = ($statusAtual !== 'pendente');

$donoId = BuscaInfoUser::buscaDonoPorTabela($table, $id);
$isDono = ($donoId !== null && $donoId === (int)$userAtivo['id']);
$isAdmin = ($userAtivo['privilegio'] === "admin");

$bloquearEdicao = $jaProcessado || $expirou || !$engine->canSubmit() || ($isDono && !$isAdmin);
?>
<div class="painel-wrapper">
    <form action="/confirmaReivindica" method="post" class="Painel" onsubmit="statusReivindica(event)" data-status-atual="<?= $statusAtual ?>"
        data-agendamento="<?= $dataAgendamento ?>">
        <div class="top-Painel">
            <h2>aceita reivindicar</h2>
            <hr>
            <div id="menssage-log">
                <?php
                if ($statusAtual === 'aprovado') echo "<b style='color:green'>Esta solicitação já foi APROVADA.</b>";
                elseif ($statusAtual === 'recusado') echo "<b style='color:red'>Esta solicitação já foi RECUSADA.</b>";
                elseif ($statusAtual === 'expirou' || $expirou) echo "<b style='color:orange'>Esta solicitação EXPIROU.</b>"; ?>
            </div>
        </div>
        <div class="editar-dados">
            <input type="hidden" name="id" id="id" value="<?= $id ?>">
            <input type="hidden" name="table" id="table" value="<?= $table ?>">
            <?= $engine->render() ?>
        </div>
        <div class="buttons-cal-conf">
            <p></p>
            <button type="button" onclick="buttonVoltar()" id="cancel">Voltar</button>
            <p></p>
            <?php if (!$bloquearEdicao): ?>
                <button type="submit" id="deny" data-status="recusado" style="background:#ff4d4d">Recusar</button>
                <button type="submit" id="confirm" data-status="aprovado">Aprovar</button>
            <?php endif; ?>
    </form>
</div>
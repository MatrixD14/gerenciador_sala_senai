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
    'privilegio' => $_SESSION['privilegio'] ?? 'aluno'
];
try {
    $engine = new FormEngine($table, $id, $userAtivo, true);
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
    <form action="/deleted" method="post" class="Painel">
        <div class="top-Painel">
            <h2>Deleta</h2>
            <hr>
        </div>
        <?php if (!$engine->canSubmit()) { ?>
            <p class="delete-info">esse agendamento não pode ser deletado já posso do dia</p>
        <?php } elseif ($dono['usuario_id'] !== $userAtivo['id'] && $userAtivo['privilegio'] !== "admin") { ?>
            <p class="delete-info">você não pode deleta esse agendameneto não e seu</p>
        <?php } else { ?>
            <p class="delete-info">"<?= $name ?>" serar deletado da tabela <?= $table ?></p>
            <input type="hidden" name="id" id="id" value="<?= $id ?>">
            <input type="hidden" name="table" id="table" value="<?= $table ?>">
            <input type="hidden" name="name" id="name" value="<?= htmlspecialchars($name) ?>"><?php } ?>
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
<?php
$regrasPeriodo = [
    'manhã' => ['min' => 5, 'max' => 7],
    'tarde' => ['min' => 7, 'max' => 11],
    'noite' => ['min' => 7, 'max' => 18]
];

// Sanitização e Preparação de Dados
$dia = str_pad($_POST['dia'] ?? '', 2, "0", STR_PAD_LEFT);
$mes = str_pad($_POST['mes'] ?? '', 2, "0", STR_PAD_LEFT);
$ano = $_POST['ano'] ?? date('Y');

$dataAgendamento = new DateTime("$ano-$mes-$dia");
$dataHoje = new DateTime('today');
$horaAtual = (int)date('H');

$isPassado = $dataAgendamento < $dataHoje;
$isHoje = $dataAgendamento == $dataHoje;
$dataExibicao = $dataAgendamento->format('d/m/Y');

// Usuário e Permissões
$privilegioUsuario = $_SESSION['privilegio'] ?? '';
$podeReivindicar = in_array($privilegioUsuario, ['aluno', 'professor']);
if (empty($privilegioUsuario)) header("location: /calendario");

// Dados do Formulário
$agendamentos = $_POST['nomes'] ?? [];
$agendamentosLiberados = 0;

// Função auxiliar para validar bloqueio
function verificarBloqueio($periodo, $isHoje, $isPassado, $regras)
{
    if ($isPassado) return "Data retroativa";

    if ($isHoje) {
        $turno = mb_strtolower(explode(' ', $periodo)[0]);
        if (isset($regras[$turno])) {
            $h = (int)date('H');
            if ($h < $regras[$turno]['min'] || $h >= $regras[$turno]['max']) {
                return "Fora do horário ({$regras[$turno]['min']}h às {$regras[$turno]['max']}h)";
            }
        }
    }
    return null;
}
?>
<div class="painel-wrapper">
    <form action="/reivindicar" method="post" class="Painel" id="formReivindicar"
        data-dia="<?= $dia ?>" data-mes="<?= $mes ?>" data-ano="<?= $ano ?>"
        onsubmit="enviaDadosRevindicar(event)" novalidate>

        <div class="top-Painel">
            <h3>Agendamentos para: <strong><?= htmlspecialchars($dataExibicao) ?></strong></h3>
            <hr>
            <span class="menssagen"></span>
        </div>

        <div class="lista-checkbox">
            <?php if (!empty($agendamentos)) { ?>
                <?php foreach ($agendamentos as $i => $nome) {
                    $id = $_POST['id'][$i] ?? $i;
                    $sala = $_POST['salas'][$i] ?? 'N/A';
                    $periodo = $_POST['periodos'][$i] ?? '';

                    $motivoBloqueio = verificarBloqueio($periodo, $isHoje, $isPassado, $regrasPeriodo);
                    $bloqueado = $motivoBloqueio !== null;

                    if (!$bloqueado) $agendamentosLiberados++;

                    $label = htmlspecialchars("$sala - $nome - $periodo");
                ?>
                    <div class="item-selecionavel <?= $bloqueado ? 'opacity: 0.9; cursor: not-allowed;padding:5px' : '' ?>">
                        <?php if ($bloqueado && $podeReivindicar) { ?>
                            <span class="txt-bloqueado" style="color: #fff; font-weight: bold;">
                                <?= $label ?> <br>
                                <small>🚫 <?= $motivoBloqueio ?></small>
                            </span>
                        <?php } else { ?>
                            <input type="radio" name="id" value="<?= htmlspecialchars($id) ?>" id="<?= $id ?>" required>
                            <label for="<?= $id ?>"><?= $label ?></label>
                        <?php } ?>
                    </div>
            <?php
                }
            } else {
                echo "<p>Nenhum agendamento encontrado.</p>";
            } ?>

            <?php if (!$isPassado && $podeReivindicar) { ?>
                <div class="item-adicionar" onclick="novoAgendamentoDesteDia()">
                    <div class="btn-add-inline">
                        <svg class='icon-adicionar'>
                            <use href='#icon-mais'></use>
                        </svg> <span>Adicionar agendamento</span>
                    </div>
                </div>
            <?php } ?>
        </div>

        <div class="buttons-cal-conf">
            <?php if (!$agendamentosLiberados > 0 && $podeReivindicar) echo "<p></p>"; ?>
            <button type="button" onclick="buttonVoltar()" id="cancel">Fechar</button>
            <?php if (!$agendamentosLiberados > 0 && $podeReivindicar) echo "<p></p>";
            else { ?>
                <button type="submit" id="confirm">
                    <?= $podeReivindicar ? 'Solicitar Troca' : 'Visualizar' ?>
                </button>
            <?php } ?>
        </div>
    </form>
</div>
<?php

$regrasPeriodo = [
    'manhã' => ["min" => 5, "max" => 7],
    'tarde' => ["min" => 7, "max" => 11],
    'noite' => ["min" => 7, "max" => 18]
];
$dia = (str_pad($_POST['dia'] ?? "", 2, "0", STR_PAD_LEFT) ?? '');
$mes =  (str_pad($_POST['mes'] ?? "", 2, "0", STR_PAD_LEFT) ?? '');
$ano = ($_POST['ano'] ?? '');
$dataAgendamento = "$ano-$mes-$dia";
$dataHoje = date('Y-m-d');
$dataExibicao = $dia . "/" . $mes . "/" . $ano;
$horaAtual = (int)date('H');
$isPassado = ($dataAgendamento < $dataHoje);
$realmenteHoje = ($dataAgendamento === $dataHoje);
$id_user = $_POST["id"] ?? [];
$nomes = $_POST["nomes"] ?? [];
$periodos = $_POST["periodos"] ?? [];
$salas = $_POST["salas"] ?? [];

$existePeriodoDisponivel = false;
if ($realmenteHoje) {
    foreach ($regrasPeriodo as $p => $regra) {
        if ($horaAtual <= $regra['max']) {
            $existePeriodoDisponivel = true;
            break;
        }
    }
} else
    $existePeriodoDisponivel = !$isPassado;
$agendamentosLiberados = 0;
?>
<div class="painel-wrapper">
    <form action="/reivindicar" method="post" class="Painel" id="formReivindicar" data-dia="<?= $dia ?>" data-mes="<?= $mes ?>" data-ano="<?= $ano ?>" onsubmit="enviaDadosRevindicar(event)" novalidate>
        <div class="top-Painel">
            <h3>pessoas que Agendo no <?= htmlspecialchars($dataExibicao) ?></span>:</h3>
            <hr>

            <span class="menssagen"></span>
        </div>
        <div class="lista-checkbox">
            <?php
            if (!empty($nomes)) {
                foreach ($nomes as $i => $nome) {
                    $periodo = $periodos[$i] ?? 'null';
                    $id = $id_user[$i] ?? $i;
                    $sala = $salas[$i] ?? 'null';
                    $itemBloqueado = $isPassado;
                    $motivo = "";

                    if ($realmenteHoje && isset($regrasPeriodo[$periodo])) {
                        $min = $regrasPeriodo[$periodo]['min'];
                        $max = $regrasPeriodo[$periodo]['max'];
                        if ($horaAtual < $min || $horaAtual >= $max) {
                            $itemBloqueado = true;
                            $motivo = " (Fora do horário: $min:00h às $max:00h)";
                        }
                    }
                    if (!$itemBloqueado)
                        $agendamentosLiberados++;

                    $id_html = "agendamento_" . $id;
                    $label = htmlspecialchars(($salas[$i] ?? '') . " - $nome - " . ($periodos[$i] ?? ''));
            ?>

                    <div class="item-selecionavel" style="<?= $itemBloqueado ? 'opacity: 0.9; cursor: not-allowed;padding:5px' : '' ?>">
                        <?php if ($itemBloqueado) { ?>
                            <span style="color: #fff; font-weight: bold;"><?= $label ?> <br><small><?= $motivo ?></small> 🚫</span>
                        <?php } else { ?>
                            <input type="radio" name="id" value="<?= htmlspecialchars($id) ?>" id="<?= $id_html ?>" required>
                            <label for="<?= $id_html ?>"><?= $label ?></label>
                        <?php } ?>
                    </div>
            <?php
                }
            } else {
                echo "<p>Nenhum agendamento encontrado.</p>";
            } ?>
            <?php if ($existePeriodoDisponivel) { ?>
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
            <?php if (!$agendamentosLiberados > 0) echo "<p></p>"; ?> <button type="button" onclick="buttonVoltar()" id="cancel">Fechar</button>
            <?php if (!$agendamentosLiberados > 0) echo "<p></p>";
            else { ?>
                <button type="submit" id="confirm">Revindicar</button>
            <?php  } ?>
        </div>
    </form>
</div>
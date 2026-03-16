<?php
date_default_timezone_set('America/Sao_Paulo');
$dia = (str_pad($_POST['dia'] ?? "", 2, "0", STR_PAD_LEFT) ?? '');
$mes =  (str_pad($_POST['mes'] ?? "", 2, "0", STR_PAD_LEFT) ?? '');
$ano = ($_POST['ano'] ?? '');
$data = $dia . "/" . $mes . "/" . $ano;
$id_user = $_POST["id"] ?? [];
$nomes = $_POST["nomes"] ?? [];
$periodos = $_POST["periodos"] ?? [];
$salas = $_POST["salas"] ?? [];

$dataAgendamento = "$ano-$mes-$dia";
$dataHoje = date('Y-m-d');
$horaAtual = (int)date('H');
$realmenteHoje = ($dataAgendamento === $dataHoje);
$isPassado = ($dataAgendamento < $dataHoje);
$motivoBloqueio = "";
$bloqueioHorario = false;
if ($realmenteHoje) {
    if ($horaAtual < 11 || $horaAtual >= 18) {
        $bloqueioHorario = true;
        $motivoBloqueio = "Reivindicações hoje só são permitidas entre 11:00 e 17:00.";
    }
}
$bloqueado = $isPassado || $bloqueioHorario;
if ($isPassado) {
    $amanha = date('d/m/Y', strtotime('+1 day'));
    $motivoBloqueio = "Não é possível reivindicar, so na data $amanha";
}
?>

<form action="/revindicar" method="post" class="Painel" onsubmit="enviaDadosRevindicar(event)" novalidate>
    <div class="top-Painel">
        <h3>pessoas que Agendo no <?= htmlspecialchars($data) ?></span>:</h3>
        <hr>
        <?php if ($bloqueado) { ?>
            <p style="color: #F50; font-weight: bold;"><?= $motivoBloqueio ?></p>
        <?php } else { ?>
            <h4>Clique em um usuário para reivindicar:</h4>
        <?php } ?>
        <span class="menssagen"></span>
    </div>
    <div class="lista-checkbox">
        <?php
        if (!empty($nomes)) {
            foreach ($nomes as $i => $nome) {
                $periodo = $periodos[$i] ?? 'Não informado';
                $id = $id_user[$i] ?? $i;
                $sala = $salas[$i] ?? 'Não informado';
                $id_html = "user_id_" . $id;
                $label = htmlspecialchars(($salas[$i] ?? '') . " - $nome - " . ($periodos[$i] ?? ''));
        ?>

                <div class="item-selecionavel">
                    <?php if ($bloqueado) {
                        echo $label;
                    } else { ?><input type="radio" name="id" value="<?= htmlspecialchars($id) ?>" id="<?= $id_html ?>" required>
                        <label for="<?= $id_html ?>"><?= $label ?>
                        </label><?php } ?>
                </div>
            <?php
            }
        } else { ?>
            <!-- <script>
                window.addEventListener('popstate', function(event) {
                    if (event.state && event.state.url) {
                        loadPagePost(event.state.url, event.state.formData, false);
                    } else {
                        location.reload();
                    }
                });
            </script> -->
        <?php } ?>
    </div>
    <div class="buttons-cal-conf">
        <button type="button" onclick="buttonVoltar()" id="cancel">Fechar</button>
        <button type="submit" id="confirm" <?= $bloqueado ? ' style="opacity:0.5; cursor:not-allowed" disabled' : '' ?>>Revindicar</button>
    </div>
</form>
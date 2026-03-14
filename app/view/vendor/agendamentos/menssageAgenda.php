<?php
$data = ((str_pad($_POST['dia'] ?? "", 2, "0", STR_PAD_LEFT) ?? '') . "/" . (str_pad($_POST['mes'] ?? "", 2, "0", STR_PAD_LEFT) ?? '') . "/" . ($_POST['ano'] ?? '')) ?? [];
$id_user = $_POST["id"] ?? [];
$nomes = $_POST["nomes"] ?? [];
$periodos = $_POST["periodos"] ?? [];
$salas = $_POST["salas"] ?? [];
?>

<form action="/revindicar" method="post" class="Painel">
    <div class="top-Painel">
        <h3>pessoas que Agendo no <?= htmlspecialchars($data) ?>:</h3>
        <hr>
        <h4>clique em um usúrio para revindicar:</h4>
    </div>
    <div class="lista-checkbox">
        <?php
        if (!empty($nomes)) {
            foreach ($nomes as $i => $nome) {
                $periodo = $periodos[$i] ?? 'Não informado';
                $id = $id_user[$i] ?? $i;
                $sala = $salas[$i] ?? 'Não informado';
                $id_html = "user_id_" . $id;
        ?>
                <div class="item-selecionavel">
                    <input type="radio" name="id" value="<?= htmlspecialchars($id) ?>" id="<?= $id_html ?>" required>
                    <label for="<?= $id_html ?>"><?= htmlspecialchars($sala) . " - " . htmlspecialchars($nome) . " - " . htmlspecialchars($periodo) ?>
                    </label>
                </div>
        <?php
            }
        } else echo "<p>Nenhum agendamento encontrado.</p>"
        ?>
    </div>
    <div class="buttons-cal-conf">
        <button type="button" onclick="buttonVoltar()" id="cancel">Fechar</button>
        <button type="submit" id="confirm" onclick="enviaDadosRevindicar(event)">Revindicar</button>
    </div>
</form>
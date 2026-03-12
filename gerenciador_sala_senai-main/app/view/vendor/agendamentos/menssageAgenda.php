<?php
$dia = $_POST['dia'] ?? '';
$nomes = $_POST["nomes"] ?? [];
$periodos = $_POST["periodos"] ?? [];
?>

<form class="Painel">
    <div class="top-Painel">
        <h3>pessoas que Agendo no dia <?= htmlspecialchars($dia) ?>:</h3>
        <hr>
    </div>
    <ul class="lista-user">
        <?php
        if (!empty($nomes)) {
            foreach ($nomes as $i => $nome) {
                $periodo = $periodos[$i] ?? 'Não informado';
                echo "<li><a href='/revindicar' style='color:white;text-decoration: none;'><strong>" . htmlspecialchars($nome) . " - " . htmlspecialchars($periodo) . "</strong></a></li><li></li>";
            }
        } else echo "<li>Nenhum agendamento encontrado.</li>"
        ?>
    </ul>
    <div class="buttons-cal-conf">
        <button type="button" onclick="buttonVoltar()" id="cancel">Fecha</button>
        <button id="confirm">revindicar</button>
    </div>
</form>
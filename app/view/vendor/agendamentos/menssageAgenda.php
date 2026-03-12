<?php
$dia = $_POST['dia'] ?? '';
$names = $_POST["nomes"] ?? [];
?>

<form class="Painel">
    <div class="top-Painel">
        <h3>pessoas que Agendo no dia <?= htmlspecialchars($dia) ?>:</h3>
        <hr>
    </div>
    <ul>
        <?php
        if (!empty($names)) {
            foreach ($names as $nome)
                echo "<li><strong>" . htmlspecialchars($nome) . "</strong></li>";
        } else echo "<li>Nenhum agendamento encontrado.</li>"
        ?>
    </ul>
    <div class="buttons-cal-conf">
        <button type="button" onclick="buttonVoltar()" id="cancel">Fecha</button>
        <button id="confirm">Confirmar</button>
    </div>
</form>
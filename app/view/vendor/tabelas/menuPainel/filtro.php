<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$table = $_POST["tabela"] ?? '';
if (!$table) {
    header('location: /gerenciado_de_Sala');
    exit;
}
?>
<div class="painel-wrapper">
    <form id="formFiltro" class="Painel">
        <div class="top-Painel">
            <h2>Filtros:<?= ucfirst($table) ?></h2>
            <hr>
        </div>
        <div class="editar-dados">
            <?= FiltroRenderer::render($table) ?>
        </div>
        <div class="buttons-cal-conf">
            <button type="button" onclick="buttonVoltar()" id="cancel">Fechar</button>
            <button id="confirm">Confirmar</button>
        </div>
    </form>
</div>
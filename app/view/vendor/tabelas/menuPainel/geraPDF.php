<?php
if (session_status() === PHP_SESSION_NONE) session_start();
unset($_SESSION['show_cols']);
$table = $_POST["tabela"] ?? '';
if (!$table) {
    header('location: /gerenciado_de_Sala');
    exit;
}

try {
    $relEngine = new RelatorioEngine($table);
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
    exit;
}
?>
<div class="painel-wrapper">
    <form id="formGerarPDF" action="/ViewInPDF" method="GET" target="_blank" class="Painel">
        <input type='hidden' name='tabela' value="<?= $table ?>">
        <div class="top-Painel">
            <h2>Personalizar Relatório PDF: <?= ucfirst($table) ?></h2>
            <hr>
        </div>

        <?= $relEngine->renderForm(); ?>
        <div class="buttons-cal-conf">
            <button type="button" onclick="buttonVoltar()" id="cancel">Fechar</button>
            <button type="submit" id="confirm" class="btn-gerar">Gerar Relatório PDF</button>
        </div>
    </form>
</div>
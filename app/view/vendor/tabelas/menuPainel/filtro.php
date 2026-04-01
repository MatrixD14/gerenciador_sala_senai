<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$table = $_POST["tabela"] ?? '';
if (!$table) {
    header('location: /gerenciado_de_Sala');
    exit;
}

try {
    $filtro = new FiltroEngine($table);
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
    exit;
}
?>
<div class="painel-wrapper">
    <form action="/<?= $table ?>" method="post" id="formFiltro" class="Painel" onsubmit="filtraTabele(e)">
        <div class="top-Painel">
            <h2>Filtros:<?= ucfirst($table) ?></h2>
            <hr>
        </div>
        <div class="editar-dados">
            <input type='hidden' name='tabela' value="<?= $table ?>">
            <?= $filtro->render() ?>
            <div id="menssage-log" style="margin-top: 10px; text-align: center;"></div>
        </div>
        <div class="buttons-cal-conf">
            <button type="button" onclick="buttonVoltar()" id="cancel">Fechar</button>
            <button id="confirm">Confirmar</button>
        </div>
    </form>
</div>
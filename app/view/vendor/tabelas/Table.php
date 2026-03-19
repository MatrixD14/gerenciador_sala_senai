<?php
$tipoTabela = $Tabelas ?? '';
if ($isAjax && isset($_POST['last_id']) || isset($_POST['is_search_ajax'])) {
    echo Tabelas::geraBodyTabela2($tipoTabela);
    exit;
}
$Toptabela = Tabelas::geraTopTabela($tipoTabela);
$Bodytabela = Tabelas::geraBodyTabela2($tipoTabela);
?>
<div class="body-Table">
    <?php
    if (isset($_SESSION["erro_table"])) { ?>
        <div class="menssage">
            <?= $_SESSION["erro_table"] ?>
        </div>
    <?php }
    unset($_SESSION["erro_table"]); ?>
    <?php require_once __DIR__ . "/menuTop/topBar.php"; ?>
    <div class="table">
        <?php if ($Bodytabela) { ?>
            <div class="table-center">
                <table class="tabela">
                    <thead>
                        <?= $Toptabela ?>
                    </thead>
                    <tbody class="carregaTable" id="carregaTabela">
                        <?= $Bodytabela ?>
                    </tbody>
                </table>
            </div>
        <?php } else echo "<div style='width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#fff'>
    <h1>Nenhum dado</h1>
</div>" ?>
    </div>
</div>
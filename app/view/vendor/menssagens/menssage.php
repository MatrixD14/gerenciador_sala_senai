<?php
$tipoTabela = "menssagem";
if ($isAjax && isset($_POST['last_id'])) {
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
    <?php require_once __DIR__ . "/../tabelas/menuTop/topBar.php"; ?>
    <div class="table">
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
    </div>
</div>
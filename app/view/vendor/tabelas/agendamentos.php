<?php
$tipoTabela = "agendamentos";
$Toptabela = Tabelas::geraTopTabela($tipoTabela);
$Bodytabela = Tabelas::geraBodyJoinTabela($tipoTabela);
?>
<div>
    <?php
    if (isset($_SESSION["erro_table"])) { ?>
        <div class="menssage">
            <?= $_SESSION["erro_table"] ?>
        </div>
    <?php }
    unset($_SESSION["erro_table"]); ?>
    <?php require_once __DIR__ . "/menuTop/topBar.php"; ?>
    <div class="table">
        <div class="table-center">
            <table class="tabela">
                <thead>
                    <?= $Toptabela ?>
                </thead>
                <tbody>
                    <?= $Bodytabela ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
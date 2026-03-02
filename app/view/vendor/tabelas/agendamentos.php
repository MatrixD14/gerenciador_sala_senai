<?php
$tipoTabela = "agendamentos";
$Toptabela = Tabelas::geraTopTabela($tipoTabela);
$Bodytabela = Tabelas::geraBodyJoinTabela($tipoTabela);
?>
<div>
    <?php require_once __DIR__ . "/menuTop/topBar.php"; ?>
    <div class="table">
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
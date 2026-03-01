<?php $Toptabela = Tabelas::geraTopTabela("salas");
$Bodytabela = Tabelas::geraBodyTabela("salas"); ?>

<table border="1">
    <?= $Toptabela ?>
    <?= $Bodytabela ?>
</table>
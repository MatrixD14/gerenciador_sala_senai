<?php $Toptabela = Tabelas::geraTopTabela("agendamentos");
$Bodytabela = Tabelas::geraBodyTabela("agendamentos"); ?>

<table border="1">
    <?= $Toptabela ?>
    <?= $Bodytabela ?>
</table>
<?php
$Toptabela = Tabelas::geraTopTabela("logins");
$Bodytabela = Tabelas::geraBodyTabela("logins"); ?>

<table border="1">
    <?= $Toptabela ?>
    <?= $Bodytabela ?>
</table>
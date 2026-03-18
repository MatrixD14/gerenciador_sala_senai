<div class="menu-table">
    <?= $tipoTabela ?>
    <div class="local-icon-reload">
        <?= MenuTable::geraMenuTable($tipoTabela, $_SESSION['privilegio']) ?></div>
</div>
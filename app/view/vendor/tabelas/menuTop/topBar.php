<div class="menu-table">
    <?= $tipoTabela ?>
    <div class="local-icon-reload">
        <?= MenuTable::geraMenuTable($tipoTabela, $_SESSION['privilegio']) ?></div>

</div>
<div class="search-conteiner">
    <input type="text" name="search" id="search" class="serach-dados" placeholder="pesquisar...">
</div>
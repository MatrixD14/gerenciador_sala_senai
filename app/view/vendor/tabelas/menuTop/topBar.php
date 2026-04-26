<div class="menu-table">
    <div class="tipoTable"><?= $tipoTabela ?></div>
    <div class="local-icon-reload">
        <?= MenuTable::geraMenuTable($tipoTabela, $_SESSION['privilegio']) ?></div>

</div>
<div class="search-conteiner escondido" id="search-wrapper">
    <input type="text" name="search" id="search" class="serach-dados" placeholder="pesquisar..." autocomplete="off">
</div>
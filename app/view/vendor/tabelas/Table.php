<?php
$tipoTabela = $Tabelas ?? '';
if (isset($_POST['get_fragmento']) && $_POST['get_fragmento'] == '1') {
    Tabelas::geraBodyTabela2($tipoTabela);
    exit;
}
$Toptabela = Tabelas::geraTopTabela($tipoTabela);
// $Bodytabela = Tabelas::geraBodyTabela2($tipoTabela);
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
        <?php if ($tipoTabela) { ?>
            <div class="table-center">
                <table class="tabela">
                    <thead>
                        <?= $Toptabela ?>
                    </thead>
                    <tbody class="carregaTable" id="carregaTabela" data-slug="<?= htmlspecialchars($tipoTabela) ?>">
                        <tr id="spacer-top" style="height: 0px; border: none;">
                            <td colspan="100%" style="padding: 0; border: none; height: 0;"></td>
                        </tr>
                        <?php //echo $Bodytabela;
                        ?>
                    </tbody>
                </table>
                <div id="loading-init" style="text-align:center; padding: 20px; color: #fff;">
                    Carregando dados...
                </div>
            </div>
        <?php } else echo "<div style='width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#fff'>
    <h1>Nenhum dado</h1>
</div>" ?>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const slug = '<?= addslashes($tipoTabela) ?>';
        if (slug && typeof window.initTabela === 'function') {
            window.initTabela(slug);
        } else {
            console.error('initTabela não carregada ou slug vazio');
        }
    });
</script>
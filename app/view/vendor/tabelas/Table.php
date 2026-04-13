<?php
$tipoTabela = $Tabelas ?? '';
$filtrosPost = $_POST;
$filtrosParaOJS = $filtrosPost;

unset($filtrosParaOJS['get_fragmento'], $filtrosParaOJS['tabela'], $filtrosParaOJS['slug']);

$jsonFiltros = json_encode($filtrosParaOJS);

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
                    <tbody class="carregaTable" id="carregaTabela" data-slug="<?= htmlspecialchars($tipoTabela) ?>" data-filtros='<?= htmlspecialchars($jsonFiltros, ENT_QUOTES, 'UTF-8') ?>'>
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
        const container = document.getElementById('carregaTabela');
        const slug = container.getAttribute('data-slug');
        console.log('Iniciando tabela para o slug:', slug);
        let filtrosIniciais = {};
        try {
            filtrosIniciais = JSON.parse(container.getAttribute('data-filtros') || '{}');
            console.log('Filtros carregados:', filtrosIniciais);
        } catch (e) {
            console.error("Erro ao ler filtros iniciais");
        }

        if (slug && typeof window.initTabela === 'function') {
            window.initTabela(slug, '', filtrosIniciais);
        } else {
            console.error('initTabela não carregada ou slug vazio');
        }
    });
</script>